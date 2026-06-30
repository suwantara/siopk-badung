@extends('layouts.app')
@section('title', 'Review: ' . $laporan->nama_opk)
@section('page-title', 'Review Laporan')

@section('content')
<x-ui.back-link href="{{ route('admin.verifikasi.index') }}" label="Kembali ke Antrian" />

{{-- Workflow Progress --}}
<div class="d-flex align-items-center gap-2 mb-4" style="font-size:0.78rem;">
    @php
        $steps = [
            ['menunggu',   'Laporan Masuk'],
            ['ai_review',  'AI Review'],
            ['review_dinas','Review Dinas'],
        ];
        $currentIdx = array_search($laporan->status_verifikasi, array_column($steps, 0));
        if ($currentIdx === false) $currentIdx = 0;
    @endphp
    @foreach($steps as $i => [$key, $label])
        <span style="padding:4px 12px;border-radius:20px;font-weight:600;
            background:{{ $i < $currentIdx ? 'var(--surface-hijau)' : ($i === $currentIdx ? 'var(--emas)' : 'var(--bg-input)') }};
            color:{{ $i < $currentIdx ? 'var(--hijau)' : ($i === $currentIdx ? 'var(--tanah)' : 'var(--abu)') }};">
            {{ $i < $currentIdx ? '✓' : $i + 1 }}. {{ $label }}
        </span>
        @if(!$loop->last)
            <span style="color:var(--abu);">→</span>
        @endif
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-start">
                    <div style="width:90px;height:90px;border-radius:6px;overflow:hidden;flex-shrink:0;background:var(--bg-placeholder);display:flex;align-items:center;justify-content:center;font-size:2rem;">
                        @if($laporan->fotoUtama)
                            <img src="{{ asset('storage/'.$laporan->fotoUtama->path) }}" style="width:100%;height:100%;object-fit:cover;" alt="Foto {{ $laporan->nama_opk }}">
                        @else {{ $laporan->kategori?->ikon ?? '🏛️' }} @endif
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <span class="t-caption" style="color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;">{{ $laporan->kode_laporan }}</span>
                        <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.6rem;font-weight:700;margin:4px 0 6px;">{{ $laporan->nama_opk }}</h2>
                        <div class="d-flex gap-2 flex-wrap">
                            <x-ui.badge-kategori :ikon="$laporan->kategori?->ikon" :nama="$laporan->kategori?->nama" />
                            <x-ui.badge-kondisi :kondisi="$laporan->kondisi" />
                            <span style="background:var(--krem);color:var(--abu-gelap);padding:2px 8px;border-radius:2px;" class="t-caption">{{ $laporan->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Pre-Screening --}}
        <div class="ai-panel p-3 mb-3">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid var(--border-emas);">
                <span class="ai-blink" style="width:8px;height:8px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>
                <span style="font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em" class="t-caption">AI Pre-Screening</span>
                @if($laporan->ai_urgency_score)
                <span class="ms-auto">
                    <span style="color:rgba(247,241,232,0.5);">Urgency</span>
                    <x-ui.ai-score :score="$laporan->ai_urgency_score" :kondisi="$laporan->kondisi" size="lg" />
                    <span style="color:rgba(247,241,232,0.4);">/10</span>
                </span>
                @endif
            </div>

            @if($laporan->ai_rekomendasi)
            <div style="line-height:1.7;opacity:0.9;margin-bottom:0.75rem" class="t-body">
                🤖 <strong style="color:var(--emas-muda);">Rekomendasi:</strong> {{ $laporan->ai_rekomendasi }}
            </div>
            @else
            <div style="opacity:0.5;margin-bottom:0.75rem" class="t-body">AI belum memproses laporan ini. <em>Jalankan re-analisis dari AI Chat di sidebar.</em></div>
            @endif

            @if($laporan->ai_duplikat_score > 30)
            <div style="background:rgba(192,57,43,0.2);border-left:3px solid var(--emas-muda);padding:8px 12px;border-radius:0 3px 3px 0;color:var(--emas-muda)" class="t-body">
                ⚠️ Potensi duplikat terdeteksi: <strong>{{ number_format($laporan->ai_duplikat_score, 0) }}%</strong>
                @if($laporan->duplikatDari)
                    — mirip dengan <a href="{{ route('admin.opk.show', $laporan->duplikatDari) }}" style="color:var(--emas-muda);text-decoration:underline;">{{ $laporan->duplikatDari->kode_laporan }}</a>
                @endif
            </div>
            @endif
        </div>

        @if($laporan->fotos->count() > 0)
        <div class="card mb-3">
            <div class="card-header-custom">
                <span class="title"><i class="bi bi-images me-2"></i>Foto Dokumentasi ({{ $laporan->fotos->count() }})</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:8px;">
                    @foreach($laporan->fotos as $foto)
                    <div style="aspect-ratio:1;border-radius:4px;overflow:hidden;background:var(--bg-placeholder);cursor:pointer;"
                         onclick="openFoto('{{ asset('storage/'.$foto->path) }}', '{{ $foto->keterangan }}')">
                        <img src="{{ asset('storage/'.$foto->path) }}" style="width:100%;height:100%;object-fit:cover;" alt="Foto OPK">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-file-text me-2"></i>Deskripsi & Detail</span></div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="t-caption" style="font-weight:700;color:var(--abu);margin-bottom:6px;">Deskripsi Umum</div>
                    <p class="t-body" style="line-height:1.8;color:var(--teks);">{{ $laporan->deskripsi_umum }}</p>
                </div>
                @if($laporan->sejarah_asal_usul)
                <div class="mb-3">
                    <div class="t-caption" style="font-weight:700;color:var(--abu);margin-bottom:6px;">Sejarah & Asal-Usul</div>
                    <p class="t-body" style="line-height:1.8;color:var(--teks);">{{ $laporan->sejarah_asal_usul }}</p>
                </div>
                @endif
                @if($laporan->nilai_makna_budaya)
                <div>
                    <div class="t-caption" style="font-weight:700;color:var(--abu);margin-bottom:6px;">Nilai & Makna Budaya</div>
                    <p class="t-body" style="line-height:1.8;color:var(--teks);">{{ $laporan->nilai_makna_budaya }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Keputusan --}}
        <div class="card card-accent-emas">
            <div class="card-header-custom"><span class="title"><i class="bi bi-gavel me-2"></i>Keputusan Verifikasi</span></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div style="background:var(--surface-hijau);border:1px solid rgba(45,90,39,0.25);border-radius:6px;padding:1.5rem;text-align:center;">
                            <i class="bi bi-check-circle" style="font-size:2rem;color:var(--hijau);display:block;margin-bottom:8px;"></i>
                            <div class="t-body-lg fw-semibold" style="color:var(--hijau);margin-bottom:4px;">Setujui Laporan</div>
                            <p class="t-caption" style="color:var(--abu-gelap);margin-bottom:12px;">OPK akan masuk peta resmi dan dashboard Pemkab Badung.</p>
                            <form method="POST" action="{{ route('admin.verifikasi.setujui', $laporan) }}">
                                @csrf
                                <textarea name="catatan" class="form-control form-control-sm mb-2" rows="2" placeholder="Catatan verifikator (opsional)..."></textarea>
                                <button type="submit" class="btn w-100"
                                        style="background:var(--hijau);color:white;border:none;font-weight:600;"
                                        onclick="event.preventDefault(); swalKonfirmasi({title:'Setujui Laporan',text:'Setujui {{ $laporan->kode_laporan }} — {{ $laporan->nama_opk }} dan masukkan ke peta OPK?',icon:'question',confirmText:'Setujui',confirmColor:'var(--hijau)',onConfirm:()=>this.closest('form').submit()})">
                                    <i class="bi bi-check2 me-1"></i>Setujui & Publikasikan
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div style="background:var(--surface-merah);border:1px solid rgba(192,57,43,0.25);border-radius:6px;padding:1.5rem;text-align:center;">
                            <i class="bi bi-x-circle" style="font-size:2rem;color:var(--merah);display:block;margin-bottom:8px;"></i>
                            <div class="t-body-lg fw-semibold" style="color:var(--merah);margin-bottom:4px;">Tolak Laporan</div>
                            <p class="t-caption" style="color:var(--abu-gelap);margin-bottom:12px;">Laporan tidak memenuhi kriteria. Pelapor akan diberitahu via WhatsApp.</p>
                            <form method="POST" action="{{ route('admin.verifikasi.tolak', $laporan) }}">
                                @csrf
                                <select name="alasan" class="form-select form-select-sm mb-2" required>
                                    <option value="">— Pilih alasan —</option>
                                    <option value="tidak_valid">Data tidak valid</option>
                                    <option value="duplikat">Duplikat laporan lain</option>
                                    <option value="kurang_data">Data tidak lengkap</option>
                                    <option value="diluar_wilayah">Luar wilayah Badung</option>
                                    <option value="lainnya">Lainnya</option>
                                </select>
                                <textarea name="catatan" class="form-control form-control-sm mb-2" rows="2" placeholder="Jelaskan alasan penolakan..." required></textarea>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-x-circle me-1"></i>Tolak Laporan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4 mt-3 mt-lg-0">
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-geo me-2"></i>Lokasi</span></div>
            <div class="card-body p-0">
                @if($laporan->latitude && $laporan->longitude)
                <div id="petaVerif" style="height:160px;"></div>
                @endif
                <div style="padding:1rem;">
                    <x-ui.info-rows :rows="[
                        ['Kecamatan', $laporan->kecamatan?->nama],
                        ['Desa Dinas', $laporan->desaDinas?->nama],
                        ['Desa Adat', $laporan->nama_desa_adat],
                        ['Banjar Adat', $laporan->banjar_adat],
                        ['Lokasi Spesifik', $laporan->lokasi_spesifik],
                        ['GPS', $laporan->latitude ? number_format($laporan->latitude,5).', '.number_format($laporan->longitude,5) : '—'],
                    ]" key-width="100px" />
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-tags me-2"></i>Atribut</span></div>
            <div class="card-body p-0"><div style="padding:0 1rem;">
                <x-ui.info-rows :rows="[
                    ['Tahun', $laporan->tahun_keterangan ?? ($laporan->tahun_diketahui ? (string)$laporan->tahun_diketahui : null)],
                    ['Bahasa', $laporan->bahasa_digunakan],
                    ['Aksara', $laporan->aksara_digunakan],
                    ['Frekuensi', $laporan->frekuensi_pelaksanaan ? ucwords(str_replace('_',' ',$laporan->frekuensi_pelaksanaan)) : null],
                    ['Kepemilikan', $laporan->status_kepemilikan ? ucwords(str_replace('_',' ',$laporan->status_kepemilikan)) : null],
                ]" key-width="100px" />
            </div></div>
        </div>

        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-person me-2"></i>Pelapor</span></div>
            <div class="card-body p-0"><div style="padding:0 1rem;">
                <x-ui.info-rows :rows="[
                    ['Tipe', ucwords(str_replace('_',' ',$laporan->tipe_pelapor))],
                    ['Nama', $laporan->pelapor_nama],
                    ['WA', $laporan->pelapor_whatsapp],
                    ['Email', $laporan->pelapor_email],
                    ['Dikirim', $laporan->created_at->isoFormat('D MMM Y, HH:mm')],
                ]" key-width="80px" />
            </div></div>
        </div>

        @if($laporan->dokumens->count() > 0)
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-file-earmark me-2"></i>Dokumen ({{ $laporan->dokumens->count() }})</span></div>
            <div class="card-body">
                @foreach($laporan->dokumens as $dok)
                <a href="{{ asset('storage/'.$dok->path) }}" target="_blank"
                   class="d-flex align-items-center gap-2 p-2 rounded mb-1 text-decoration-none"
                   style="background:var(--bg-input);border:1px solid var(--border-light);color:var(--tanah);">
                    <i class="bi bi-file-earmark-pdf" style="color:var(--merah);"></i>
                    <span class="t-body" style="flex:1;">{{ $dok->nama_file }}</span>
                    <i class="bi bi-download t-caption" style="color:var(--abu);"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @include('components.ai-chat', ['laporan' => $laporan])
    </div>
</div>

@include('components.foto-modal')
@endsection

@push('scripts')
<script type="module">
@if($laporan->latitude && $laporan->longitude)
const m = L.map('petaVerif').setView([{{ $laporan->latitude }},{{ $laporan->longitude }}],15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(m);
L.marker([{{ $laporan->latitude }},{{ $laporan->longitude }}]).addTo(m);
@endif
</script>
@endpush
