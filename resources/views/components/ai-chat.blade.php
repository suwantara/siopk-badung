{{--
    Component: AI Chat Asisten
    Penggunaan: @include('components.ai-chat', ['laporan' => $laporan])
--}}
<div class="card" id="aiChatCard" style="border:1px solid rgba(200,146,42,0.3);">
    <div style="background:linear-gradient(135deg,var(--tanah-gelap),var(--tanah));padding:0.85rem 1.2rem;border-radius:4px 4px 0 0;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="width:8px;height:8px;border-radius:50%;background:var(--emas-muda);display:inline-block;animation:blink 2s infinite;"></span>
            <span style="font-size:0.72rem;font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em;">AI Asisten</span>
        </div>
        <span style="font-size:0.65rem;color:rgba(247,241,232,0.4);">Claude · SIOPK Badung</span>
    </div>

    {{-- Riwayat chat --}}
    <div id="chatHistory"
         style="height:240px;overflow-y:auto;padding:1rem;background:var(--input-bg);display:flex;flex-direction:column;gap:10px;">
        <div class="chat-msg ai" style="background:linear-gradient(135deg,var(--tanah-gelap),var(--tanah));color:var(--krem);padding:10px 12px;border-radius:3px 12px 12px 3px;font-size:0.78rem;line-height:1.6;max-width:90%;align-self:flex-start;">
            🙏 Halo! Saya siap membantu menganalisis laporan <strong style="color:var(--emas-muda);">{{ $laporan->nama_opk }}</strong>. Apa yang ingin Anda tanyakan?
        </div>
    </div>

    {{-- Input --}}
    <div style="padding:0.75rem;border-top:1px solid var(--input-bg);background:white;">
        <div style="display:flex;gap:8px;">
            <input type="text" id="chatInput"
                   placeholder="Tanya tentang laporan ini..."
                   style="flex:1;border:1px solid var(--garis);border-radius:3px;padding:8px 12px;font-size:0.82rem;background:var(--input-bg);color:var(--tanah);outline:none;"
                   onkeydown="if(event.key==='Enter') sendChat()"
                   onfocus="this.style.borderColor='var(--emas)'" onblur="this.style.borderColor='var(--garis)'">
            <button onclick="sendChat()"
                    style="background:var(--emas);color:var(--tanah);border:none;padding:8px 14px;border-radius:3px;font-size:0.8rem;font-weight:600;cursor:pointer;white-space:nowrap;"
                    id="chatBtn">
                Kirim
            </button>
        </div>
        {{-- Pertanyaan cepat --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
            @foreach([
                'Apa rekomendasi tindakan?',
                'Mengapa skor urgensi tinggi?',
                'Apakah ini duplikat?',
                'Siapa yang harus dihubungi?',
            ] as $q)
            <button onclick="quickAsk('{{ $q }}')"
                    style="font-size:0.65rem;background:rgba(200,146,42,0.08);border:1px solid rgba(200,146,42,0.2);color:var(--emas-gelap);padding:3px 8px;border-radius:10px;cursor:pointer;">
                {{ $q }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Re-analisis (admin) --}}
    @if(auth()->user()->isAdmin())
    <div style="padding:0.6rem 0.75rem;border-top:1px solid var(--input-bg);background:var(--input-bg);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:0.68rem;color:var(--abu);">
            Score: <strong style="color:{{ $laporan->kondisi === 'kritis' ? 'var(--merah)' : ($laporan->kondisi === 'waspada' ? 'var(--kuning)' : 'var(--hijau)') }}">
                {{ $laporan->ai_urgency_score ? number_format($laporan->ai_urgency_score, 1).'/10' : 'Belum dianalisis' }}
            </strong>
        </span>
        <form method="POST" action="{{ route('admin.ai.re-analisis', $laporan) }}">
            @csrf
            <button type="submit"
                    style="font-size:0.65rem;background:none;border:1px solid rgba(200,146,42,0.3);color:var(--emas);padding:3px 10px;border-radius:3px;cursor:pointer;"
                    onclick="event.preventDefault(); swalKonfirmasi({title:'Re-analisis AI',text:'Jalankan ulang analisis AI untuk laporan ini?',icon:'info',confirmText:'Jalankan',confirmColor:'var(--emas)',onConfirm:()=>this.closest('form').submit()})">
                🔄 Re-analisis AI
            </button>
        </form>
    </div>
    @endif
</div>

<script>
(function() {
    const laporanId = {{ $laporan->id }};
    const chatUrl   = "{{ route('admin.ai.chat', $laporan) }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    window.sendChat = function() {
        const input = document.getElementById('chatInput');
        const btn   = document.getElementById('chatBtn');
        const q     = input.value.trim();
        if (!q) return;

        // Tampilkan pesan user
        appendMsg(q, 'user');
        input.value = '';
        btn.disabled = true;
        btn.textContent = '...';

        // Tampilkan loading
        const loadId = appendMsg('⏳ AI sedang menganalisis...', 'ai', true);

        fetch(chatUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ pertanyaan: q }),
        })
        .then(r => r.json())
        .then(data => {
            removeMsg(loadId);
            appendMsg(data.success ? data.jawaban : 'Maaf, AI tidak dapat merespons saat ini.', 'ai');
        })
        .catch(() => {
            removeMsg(loadId);
            appendMsg('Koneksi ke AI gagal. Pastikan CLAUDE_API_KEY sudah diset di .env', 'ai');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Kirim';
        });
    };

    window.quickAsk = function(q) {
        document.getElementById('chatInput').value = q;
        sendChat();
    };

    function appendMsg(text, from, isTemp = false) {
        const id  = 'msg-' + Date.now();
        const el  = document.createElement('div');
        el.id     = id;
        el.className = 'chat-msg ' + from;

        const isUser = from === 'user';
        el.style.cssText = [
            'padding:8px 12px',
            'border-radius:' + (isUser ? '12px 3px 3px 12px' : '3px 12px 12px 3px'),
            'font-size:0.78rem',
            'line-height:1.6',
            'max-width:88%',
            'align-self:' + (isUser ? 'flex-end' : 'flex-start'),
            'background:' + (isUser ? 'var(--tanah)' : 'linear-gradient(135deg,var(--tanah-gelap),var(--tanah))'),
            'color:var(--krem)',
            isTemp ? 'opacity:0.6;font-style:italic' : '',
        ].join(';');

        el.textContent = text;
        const hist = document.getElementById('chatHistory');
        hist.appendChild(el);
        hist.scrollTop = hist.scrollHeight;
        return id;
    }

    function removeMsg(id) {
        document.getElementById(id)?.remove();
    }
})();
</script>
