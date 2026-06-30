@extends('layouts.app')
@section('title', 'Ringkasan Eksekutif AI')
@section('page-title', 'Ringkasan Eksekutif Mingguan')

@section('content')
<x-ui.page-header title="Ringkasan Eksekutif Mingguan" subtitle="Digenerate oleh AI · Diperbarui setiap 6 jam">
    <x-slot:action>
        @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('admin.ai.clear-cache') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-clockwise me-1"></i>Refresh AI
            </button>
        </form>
        @endif
    </x-slot:action>
</x-ui.page-header>

{{-- KPI cepat --}}
@php
    $totalOpk    = \App\Models\OpkLaporan::where('status_verifikasi','disetujui')->count();
    $kritis      = \App\Models\OpkLaporan::where('status_verifikasi','disetujui')->where('kondisi','kritis')->count();
    $waspada     = \App\Models\OpkLaporan::where('status_verifikasi','disetujui')->where('kondisi','waspada')->count();
    $menunggu    = \App\Models\OpkLaporan::whereIn('status_verifikasi',['menunggu','review_dinas'])->count();
    $lapoMinggu  = \App\Models\OpkLaporan::whereDate('created_at','>=',now()->subDays(7))->count();
    $prioritas   = \App\Models\OpkLaporan::where('status_verifikasi','disetujui')->where('ai_urgency_score','>=',7)->count();
@endphp

<div class="row g-3 mb-4">
    @foreach([
        ['Total OPK',       $totalOpk,   'Terverifikasi resmi',   '',        'bi-collection'],
        ['Kritis',          $kritis,     'Perlu tindakan segera', 'kritis',  'bi-exclamation-triangle'],
        ['Waspada',         $waspada,    'Perlu pemantauan',      'waspada', 'bi-eye'],
        ['Antrian',         $menunggu,   'Menunggu verifikasi',   '',        'bi-clock'],
        ['Laporan Minggu',  $lapoMinggu, 'Masuk 7 hari terakhir', '',        'bi-inbox'],
        ['Prioritas AI≥7',  $prioritas,  'Score urgensi tinggi',  'kritis',  'bi-robot'],
    ] as [$label, $val, $sub, $type, $icon])
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card kpi-card {{ $type }} h-100 text-center">
            <div class="card-body py-3">
                <i class="bi {{ $icon }}" style="font-size:1.2rem;color:{{ $type === 'kritis' ? 'var(--merah)' : ($type === 'waspada' ? 'var(--kuning)' : 'var(--emas)') }};"></i>
                <div class="kpi-value mt-1" style="font-size:1.8rem;">{{ $val }}</div>
                <div class="kpi-label" class="t-caption">{{ $label }}</div>
                <div style="color:var(--abu);margin-top:2px" class="t-caption">{{ $sub }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Ringkasan AI --}}
    <div class="col-12 col-md-7">
        <div class="ai-panel p-0 h-100" style="overflow:hidden;">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border-emas);display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center" class="gap-sm">
                    <span class="ai-blink" style="width:8px;height:8px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>
                    <span style="font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em" class="t-caption">AI · Ringkasan Eksekutif</span>
                </div>
                <button onclick="loadCached()" id="btnCached"
                        style="background:var(--border-emas);border:1px solid rgba(200,146,42,0.3);color:var(--emas-muda);padding:4px 12px;border-radius:3px;cursor:pointer;display:none" class="t-caption">
                    📋 Lihat Tersimpan
                </button>
                <button onclick="loadRingkasan()" id="btnLoad"
                        style="background:var(--border-emas);border:1px solid rgba(200,146,42,0.3);color:var(--emas-muda);padding:4px 12px;border-radius:3px;cursor:pointer" class="t-caption">
                    ⚡ Generate Ringkasan
                </button>
            </div>
            <div id="ringkasanArea" style="padding:1.25rem;min-height:260px;color:var(--krem);">
                <div style="opacity:0.4;text-align:center;margin-top:3rem" class="t-body">
                    Klik "Generate Ringkasan" untuk meminta AI menganalisis kondisi OPK minggu ini.
                </div>
            </div>
            <div id="ringkasanMeta" style="padding:0.75rem 1.25rem;border-top:1px solid var(--surface-emas-hover);color:rgba(247,241,232,0.4);display:none" class="t-caption">
                Dihasilkan oleh Claude AI · <span id="cacheTime"></span>
            </div>
        </div>
    </div>

    {{-- OPK Prioritas Tertinggi --}}
    <div class="col-12 col-md-5 mt-3 mt-md-0">
        <div class="card h-100">
            <div class="card-header-custom">
                <span class="title"><i class="bi bi-bar-chart-line me-2"></i>Top 5 Prioritas AI</span>
                <a href="{{ route('admin.opk.index') }}?kondisi=kritis" style="color:var(--emas)" class="t-caption">Lihat Semua →</a>
            </div>
            <div class="card-body p-0">
                @php
                    $top5 = \App\Models\OpkLaporan::with(['kategori','kecamatan'])
                        ->where('status_verifikasi','disetujui')
                        ->whereIn('kondisi',['kritis','waspada'])
                        ->orderByDesc('ai_urgency_score')
                        ->limit(5)
                        ->get();
                @endphp
                @forelse($top5 as $i => $opk)
                <div style="display:flex;align-items:center;padding:10px 1rem;border-bottom:1px solid var(--garis-terang)" class="gap-sm">
                    <div style="width:24px;height:24px;border-radius:50%;background:{{ $i === 0 ? 'var(--merah)' : ($i < 3 ? 'var(--kuning)' : 'var(--abu)') }};color:white;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0" class="t-caption">
                        {{ $i + 1 }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" class="t-body">
                            <a href="{{ route('admin.opk.show', $opk) }}" style="color:var(--tanah);text-decoration:none;">{{ $opk->nama_opk }}</a>
                        </div>
                        <div style="color:var(--abu)" class="t-caption">{{ $opk->kecamatan?->nama }} · {{ $opk->kategori?->ikon }}</div>
                    </div>
                        <div class="flex-shrink-0"><x-ui.ai-score :score="($opk->ai_urgency_score ?? 0)" :kondisi="$opk->kondisi" size="lg" /></div>
                </div>
                @empty
                <div style="padding:2rem;text-align:center;color:var(--abu)" class="t-body">
                    <i class="bi bi-check-circle" style="font-size:1.5rem;color:var(--hijau);display:block;margin-bottom:8px;"></i>
                    Tidak ada OPK dengan urgensi tinggi.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Tabel semua OPK butuh perhatian --}}
<div class="card mt-3">
    <div class="card-header-custom">
        <span class="title">Semua OPK Perlu Perhatian (Kritis + Waspada)</span>
        <span style="color:var(--abu)" class="t-caption">Diurutkan AI Score tertinggi</span>
    </div>
    <div class="card-body p-0 table-responsive-si">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;">Nama OPK</th>
                    <th>Jenis</th>
                    <th>Kecamatan</th>
                    <th>Desa Adat</th>
                    <th>Kondisi</th>
                    <th>AI Score</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $semua = \App\Models\OpkLaporan::with(['kategori','kecamatan'])
                        ->where('status_verifikasi','disetujui')
                        ->whereIn('kondisi',['kritis','waspada'])
                        ->orderByDesc('ai_urgency_score')
                        ->paginate(15);
                @endphp
                @forelse($semua as $opk)
                <tr>
                    <td style="padding-left:1.25rem;font-weight:600" class="t-body">
                        <a href="{{ route('admin.opk.show', $opk) }}" style="color:var(--tanah);text-decoration:none;">
                            {{ $opk->nama_opk }}
                        </a>
                    </td>
                    <td><x-ui.badge-kategori :ikon="$opk->kategori?->ikon" :nama="$opk->kategori?->nama" /></td>
                    <td class="t-body">{{ $opk->kecamatan?->nama }}</td>
                    <td style="color:var(--abu-gelap)" class="t-body">{{ Str::limit($opk->nama_desa_adat, 20) }}</td>
                    <td><x-ui.badge-kondisi :kondisi="$opk->kondisi" /></td>
                    <td>
                        <div style="display:flex;align-items:center" class="gap-xs">
                            <x-ui.ai-score :score="$opk->ai_urgency_score ?? 0" :kondisi="$opk->kondisi" />
                            <div style="height:4px;width:50px;background:var(--input-bg);border-radius:2px;overflow:hidden;">
                                <div style="height:100%;width:{{ ($opk->ai_urgency_score ?? 0)*10 }}%;background:{{ $opk->kondisi==='kritis'?'var(--merah)':'var(--kuning)' }};border-radius:2px;"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.opk.show', $opk) }}" class="btn-icon">
                            <i class="bi bi-eye" class="t-caption"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada OPK kritis atau waspada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($semua->hasPages())
    <div class="card-footer bg-white" style="border-top:1px solid var(--garis);">{{ $semua->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function cleanMarkdown(text) {
    return text
        .replace(/\*\*(.+?)\*\*/g, '$1')
        .replace(/^[-•]\s+/gm, '→ ')
        .replace(/^\d+\.\s+\*\*(.+?)\*\*/gm, '<h4>$1</h4>')
        .replace(/^\d+\.\s+(.+)$/gm, '<h4>$1</h4>');
}

function renderRingkasan(ringkasan) {
    const escaped = ringkasan.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return escaped
        .split('\n')
        .filter(l => l.trim())
        .map(l => {
            const t = l.trim();
            if (t.startsWith('&lt;h4&gt;')) return t;
            if (/^\d+\./.test(t)) return `<div style="margin-top:10px;font-weight:600;color:var(--emas-muda);">${cleanMarkdown(t)}</div>`;
            if (t.startsWith('→')) return `<div style="padding-left:12px;margin-top:4px;opacity:0.9" class="t-body">${t}</div>`;
            return `<div style="line-height:1.8;opacity:0.9;margin-top:6px" class="t-body">${cleanMarkdown(t)}</div>`;
        }).join('');
}

function showCached(data) {
    const area = document.getElementById('ringkasanArea');
    const meta = document.getElementById('ringkasanMeta');
    area.innerHTML = renderRingkasan(data.ringkasan);
    meta.style.display = 'block';
    meta.innerHTML = `Dihasilkan oleh ${data.provider} · ${data.cached_at}`;
    document.getElementById('btnCached').style.display = 'none';
}

function loadCached() {
    fetch("{{ route('admin.ai.ringkasan') }}", {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.from_cache) {
            showCached(data);
        } else {
            document.getElementById('ringkasanArea').innerHTML =
                '<div style="opacity:0.5;text-align:center;margin-top:3rem" class="t-body">Belum ada ringkasan tersimpan. Klik Generate.</div>';
        }
    });
}

function loadRingkasan() {
    const btn  = document.getElementById('btnLoad');
    const area = document.getElementById('ringkasanArea');
    const meta = document.getElementById('ringkasanMeta');

    btn.textContent = '⏳ Memproses...';
    btn.disabled    = true;
    area.innerHTML  = '<div style="opacity:0.5;text-align:center;margin-top:3rem" class="t-body">⏳ AI sedang menganalisis data OPK minggu ini...<br><small>Mohon tunggu 10–20 detik</small></div>';

    fetch("{{ route('admin.ai.ringkasan') }}", {
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            cleanMarkdown(data.ringkasan);
            area.innerHTML  = renderRingkasan(data.ringkasan);
            meta.style.display = 'block';
            meta.innerHTML = `Dihasilkan oleh ${data.provider} · ${data.cached_at}`;
            document.getElementById('btnCached').style.display = 'inline-block';
        } else {
            area.innerHTML = `<div style="color:var(--emas-muda);text-align:center;margin-top:2rem" class="t-body">
                <i class="bi bi-exclamation-triangle" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                ${data.message.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g,'<br>') || 'Gagal mendapatkan ringkasan.'}
                <br><small style="color:var(--abu);margin-top:8px;display:block;">Provider terkonfigurasi: ${data.provider || 'tidak diketahui'}</small>
            </div>`;
        }
    })
    .catch(() => {
        area.innerHTML = '<div style="color:var(--emas-muda)" class="t-body">Koneksi gagal. Periksa jaringan dan API key.</div>';
    })
    .finally(() => {
        btn.textContent = '⚡ Generate Baru';
        btn.disabled = false;
    });
}

// Auto-load cached ringkasan jika ada
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => loadCached(), 300);
});
</script>
@endpush
