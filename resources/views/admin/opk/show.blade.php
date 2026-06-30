@extends('layouts.app')
@section('title', $laporan->nama_opk)
@section('page-title', 'Detail OPK')

@section('content')
<x-ui.back-link href="{{ route('admin.opk.index') }}" label="Kembali ke Daftar OPK" />

<div class="row g-3">
    <div class="col-12 col-md-8">

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-start">
                    <div style="width:100px;height:100px;border-radius:4px;overflow:hidden;flex-shrink:0;background:var(--placeholder);display:flex;align-items:center;justify-content:center;font-size:2.5rem;">
                        @if($laporan->fotoUtama)
                            <img src="{{ asset('storage/'.$laporan->fotoUtama->path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ $laporan->kategori?->ikon ?? '🏛️' }}
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div style="color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px" class="t-caption">{{ $laporan->kode_laporan }}</div>
                        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;font-weight:700;margin-bottom:6px;line-height:1.2;">{{ $laporan->nama_opk }}</h1>
                        <div class="d-flex gap-2 flex-wrap">
                            <x-ui.badge-kategori :ikon="$laporan->kategori?->ikon" :nama="$laporan->kategori?->nama" />
                            <x-ui.badge-kondisi :kondisi="$laporan->kondisi" size="md" />
                            <span style="background:var(--surface-hijau);color:var(--hijau);padding:3px 10px;border-radius:2px;font-weight:500" class="t-caption">✓ Terverifikasi</span>
                        </div>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="d-flex gap-1">
                    <a href="{{ route('admin.opk.edit', $laporan) }}" class="btn btn-sm" style="background:var(--surface-emas);color:var(--emas-gelap);border:1px solid var(--border-emas);white-space:nowrap;">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <form method="POST" action="{{ route('admin.opk.destroy', $laporan) }}"
                          onsubmit="event.preventDefault(); swalKonfirmasi({title:'Arsipkan OPK',text:'Arsipkan {{ $laporan->nama_opk }}? OPK tidak akan tampil di peta publik namun masih dapat dipulihkan.',icon:'warning',confirmText:'Arsipkan',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm" style="background:rgba(220,53,69,0.1);color:#dc3545;border:1px solid rgba(220,53,69,0.2);white-space:nowrap;" title="Arsipkan OPK">
                            <i class="bi bi-archive me-1"></i>Arsip
                        </button>
                    </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($laporan->fotos->count() > 0)
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-images me-2"></i>Foto ({{ $laporan->fotos->count() }})</span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr))" class="gap-sm">
                    @foreach($laporan->fotos as $foto)
                    <div style="aspect-ratio:1;border-radius:3px;overflow:hidden;background:var(--placeholder);cursor:pointer;position:relative;"
                         onclick="openFoto('{{ asset('storage/'.$foto->path) }}','{{ $foto->keterangan }}')">
                        <img src="{{ asset('storage/'.$foto->path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @if($foto->is_utama)<div style="position:absolute;top:3px;left:3px;background:var(--emas);color:var(--tanah);font-weight:700;padding:1px 5px;border-radius:2px" class="t-caption">UTAMA</div>@endif
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
                    <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:8px" class="t-caption">Deskripsi Umum</div>
                    <div style="line-height:1.8;color:var(--teks)" class="t-body-lg">{{ $laporan->deskripsi_umum }}</div>
                </div>
                @if($laporan->sejarah_asal_usul)
                <div class="mb-3">
                    <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:8px" class="t-caption">Sejarah & Asal-Usul</div>
                    <div style="line-height:1.8;color:var(--teks)" class="t-body-lg">{{ $laporan->sejarah_asal_usul }}</div>
                </div>
                @endif
                @if($laporan->nilai_makna_budaya)
                <div>
                    <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:8px" class="t-caption">Nilai & Makna Budaya</div>
                    <div style="line-height:1.8;color:var(--teks)" class="t-body-lg">{{ $laporan->nilai_makna_budaya }}</div>
                </div>
                @endif
            </div>
        </div>

        @if($laporan->ai_rekomendasi)
        <div class="ai-panel p-3 mb-3">
            <div class="d-flex align-items-center gap-2 mb-2 pb-2" style="border-bottom:1px solid var(--border-emas);">
                <span class="ai-blink" style="width:7px;height:7px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>
                <span style="font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em" class="t-caption">AI · Analisis & Rekomendasi</span>
                @if($laporan->ai_urgency_score)
                <span class="ms-auto">
                    <x-ui.ai-score :score="$laporan->ai_urgency_score" :kondisi="$laporan->kondisi" size="lg" />
                    <span style="color:rgba(247,241,232,0.4)" class="t-caption">/10</span>
                </span>
                @endif
            </div>
            <p style="line-height:1.7;opacity:0.9;margin:0" class="t-body">{{ $laporan->ai_rekomendasi }}</p>
        </div>
        @endif

        <div class="card">
            <div class="card-header-custom"><span class="title"><i class="bi bi-clock-history me-2"></i>Riwayat Status</span></div>
            <div class="card-body">
                <ul style="list-style:none;padding:0;margin:0;">
                    <li style="display:flex;padding-bottom:14px;position:relative" class="gap-md">
                        <div style="position:absolute;left:11px;top:24px;width:2px;height:calc(100% - 12px);background:var(--input-bg);"></div>
                        <div style="width:24px;height:24px;border-radius:50%;background:var(--hijau);color:white;display:flex;align-items:center;justify-content:center;flex-shrink:0;z-index:1" class="t-caption">✓</div>
                        <div>
                            <div style="font-weight:600" class="t-body">Laporan Dikirim</div>
                            <div style="color:var(--abu)" class="t-caption">{{ $laporan->created_at->isoFormat('D MMM Y, HH:mm') }}</div>
                        </div>
                    </li>
                    @foreach($laporan->riwayat as $rw)
                    <li style="display:flex;padding-bottom:14px;position:relative" class="gap-md">
                        @if(!$loop->last)<div style="position:absolute;left:11px;top:24px;width:2px;height:calc(100% - 12px);background:var(--input-bg);"></div>@endif
                        <div style="width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;z-index:1;
                            background:{{ $rw->status_baru === 'disetujui' ? 'var(--hijau)' : ($rw->status_baru === 'ditolak' ? 'var(--merah)' : 'var(--emas)') }};
                            color:{{ $rw->status_baru === 'disetujui' ? 'white' : ($rw->status_baru === 'ditolak' ? 'white' : 'var(--tanah)') }}" class="t-caption">
                            {{ $rw->status_baru === 'disetujui' ? '✓' : ($rw->status_baru === 'ditolak' ? '✕' : '→') }}
                        </div>
                        <div>
                            <div style="font-weight:600" class="t-body">{{ ucwords(str_replace('_',' ',$rw->status_baru)) }}</div>
                            <div style="color:var(--abu)" class="t-caption">{{ $rw->created_at->isoFormat('D MMM Y, HH:mm') }}@if($rw->user) · {{ $rw->user->name }}@endif</div>
                            @if($rw->catatan)<div style="color:var(--abu-gelap);margin-top:4px;background:var(--krem);border-radius:3px;padding:5px 8px;" class="t-caption">{{ $rw->catatan }}</div>@endif
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

    </div>

    <div class="col-12 col-md-4 mt-3 mt-md-0">

        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-geo me-2"></i>Lokasi & Wilayah</span></div>
            <div class="card-body p-0">
                @if($laporan->latitude && $laporan->longitude)
                <div id="petaDetail" style="height:160px;"></div>
                @endif
                <div style="padding:1rem;">
                    <x-ui.info-rows :rows="[
                        ['Kecamatan', $laporan->kecamatan?->nama],
                        ['Desa Dinas', $laporan->desaDinas?->nama],
                        ['Desa Adat', $laporan->nama_desa_adat],
                        ['Banjar Adat', $laporan->banjar_adat],
                        ['Lokasi', $laporan->lokasi_spesifik],
                        ['GPS', $laporan->latitude ? number_format($laporan->latitude,6).', '.number_format($laporan->longitude,6) : null],
                    ]" key-width="100px" />
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-tags me-2"></i>Atribut OPK</span></div>
            <div class="card-body p-0">
                <div style="padding:0 1rem;">
                    <x-ui.info-rows :rows="[
                        ['Tahun', $laporan->tahun_keterangan ?? ($laporan->tahun_diketahui ? (string)$laporan->tahun_diketahui : null)],
                        ['Pelindungan', ucwords(str_replace('_',' ',$laporan->status_pelindungan))],
                        ['Bahasa', $laporan->bahasa_digunakan],
                        ['Aksara', $laporan->aksara_digunakan],
                        ['Frekuensi', $laporan->frekuensi_pelaksanaan ? ucwords(str_replace('_',' ',$laporan->frekuensi_pelaksanaan)) : null],
                        ['Kepemilikan', $laporan->status_kepemilikan ? ucwords(str_replace('_',' ',$laporan->status_kepemilikan)) : null],
                    ]" key-width="110px" />
                </div>
            </div>
        </div>

        @if($laporan->praktisi_nama && auth()->user()->isVerifikator())
        <div class="card mb-3" style="border-left:3px solid var(--emas);">
            <div class="card-header-custom"><span class="title"><i class="bi bi-person me-2"></i>Praktisi</span><span style="background:var(--surface-emas-hover);color:var(--emas);padding:2px 8px;border-radius:10px;font-weight:600" class="t-caption">RAHASIA</span></div>
            <div class="card-body p-0"><div style="padding:0 1rem;">
                <x-ui.info-rows :rows="[
                    ['Nama', $laporan->praktisi_nama],
                    ['Usia', $laporan->praktisi_usia ? $laporan->praktisi_usia.' thn' : null],
                    ['Kontak', $laporan->praktisi_kontak],
                ]" key-width="80px" />
            </div></div>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-person-check me-2"></i>Pelapor</span></div>
            <div class="card-body p-0"><div style="padding:0 1rem;">
                <x-ui.info-rows :rows="[
                    ['Tipe', ucwords(str_replace('_',' ',$laporan->tipe_pelapor))],
                    ['Nama', $laporan->pelapor_nama],
                    ['WA', $laporan->pelapor_whatsapp],
                    ['Dilaporkan', $laporan->created_at->isoFormat('D MMM Y')],
                ]" key-width="80px" />
            </div></div>
        </div>

        @if(auth()->user()->isAdmin())
        <div class="card">
            <div class="card-header-custom">
                <span class="title" style="display:flex;align-items:center" class="gap-xs">
                    <span class="ai-blink" style="width:7px;height:7px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>AI Score
                </span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.verifikasi.ai-score', $laporan) }}">
                    @csrf
                    <div class="mb-2">
                        <label style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Urgency Score (0–10)</label>
                        <input type="number" name="ai_urgency_score" step="0.1" min="0" max="10" value="{{ $laporan->ai_urgency_score }}" class="form-control form-control-sm mt-1">
                    </div>
                    <div class="mb-3">
                        <label style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Rekomendasi</label>
                        <textarea name="ai_rekomendasi" class="form-control form-control-sm mt-1" rows="3">{{ $laporan->ai_rekomendasi }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-emas w-100">Simpan AI Score</button>
                </form>
            </div>
        </div>
        @endif

    </div>
</div>

@include('components.foto-modal')
@endsection

@push('scripts')
<script type="module">
@if($laporan->latitude && $laporan->longitude)
const m = L.map('petaDetail').setView([{{ $laporan->latitude }},{{ $laporan->longitude }}], 15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(m);
L.marker([{{ $laporan->latitude }},{{ $laporan->longitude }}],{icon:L.divIcon({className:'',html:'<div style="width:14px;height:14px;border-radius:50%;background:{{ $laporan->kondisi==="kritis"?'var(--merah)':($laporan->kondisi==="waspada"?'var(--kuning)':'var(--hijau)') }};border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.4);"></div>',iconSize:[14,14],iconAnchor:[7,7]})}).addTo(m);
@endif
</script>
@endpush
