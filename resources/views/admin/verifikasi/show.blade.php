@extends('layouts.app')
@section('title', 'Detail Laporan')
@section('page-title', 'Review Laporan')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.verifikasi.index') }}" style="font-size:0.8rem;color:var(--emas);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Antrian
    </a>
</div>

<div class="row g-3">
    <div class="col-md-8">

        {{-- Header --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-start">
                    <div style="width:80px;height:80px;border-radius:4px;overflow:hidden;flex-shrink:0;background:var(--placeholder);display:flex;align-items:center;justify-content:center;font-size:2rem;">
                        @if($laporan->fotoUtama)
                            <img src="{{ asset('storage/'.$laporan->fotoUtama->path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else {{ $laporan->kategori?->ikon ?? '🏛️' }} @endif
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:0.68rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">{{ $laporan->kode_laporan }}</div>
                        <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:700;margin-bottom:6px;">{{ $laporan->nama_opk }}</h2>
                        <div class="d-flex gap-2 flex-wrap">
                            <span style="background:rgba(200,146,42,0.1);color:var(--emas-gelap);padding:2px 8px;border-radius:2px;font-size:0.72rem;font-weight:500;">
                                {{ $laporan->kategori?->ikon }} {{ $laporan->kategori?->nama }}
                            </span>
                            <span class="badge badge-{{ $laporan->kondisi }} rounded-pill px-2" style="font-size:0.68rem;">{{ ucfirst($laporan->kondisi) }}</span>
                            <span style="font-size:0.72rem;background:var(--krem);color:var(--abu-gelap);padding:2px 8px;border-radius:2px;">
                                {{ $laporan->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- AI Pre-screening --}}
        <div class="ai-panel p-3 mb-3">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid rgba(200,146,42,0.2);">
                <span class="ai-blink" style="width:7px;height:7px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>
                <span style="font-size:0.68rem;font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em;">AI Pre-Screening</span>
                @if($laporan->ai_urgency_score)
                <span class="ms-auto" style="font-family:'Courier New',monospace;font-size:1rem;font-weight:700;color:{{ $laporan->kondisi === 'kritis' ? '#f08080' : ($laporan->kondisi === 'waspada' ? '#e8c55a' : '#7ec87e') }}">
                    Urgency: {{ number_format($laporan->ai_urgency_score, 1) }}/10
                </span>
                @endif
            </div>

            @if($laporan->ai_rekomendasi)
            <div style="font-size:0.82rem;line-height:1.7;opacity:0.9;margin-bottom:0.75rem;">
                🤖 <strong style="color:var(--emas-muda);">Rekomendasi:</strong> {{ $laporan->ai_rekomendasi }}
            </div>
            @else
            <div style="font-size:0.82rem;opacity:0.6;margin-bottom:0.75rem;">AI belum memproses laporan ini.</div>
            @endif

            @if($laporan->ai_duplikat_score > 30)
            <div style="background:rgba(192,57,43,0.2);border-left:3px solid #f08080;padding:8px 12px;border-radius:0 3px 3px 0;font-size:0.78rem;color:#f08080;">
                ⚠️ Potensi duplikat terdeteksi: <strong>{{ number_format($laporan->ai_duplikat_score, 0) }}%</strong>
                @if($laporan->duplikatDari) — mirip dengan <a href="{{ route('admin.opk.show', $laporan->duplikatDari) }}" style="color:#f08080;">{{ $laporan->duplikatDari->kode_laporan }}</a>@endif
            </div>
            @endif
        </div>

        {{-- Foto --}}
        @if($laporan->fotos->count() > 0)
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-images me-2"></i>Foto ({{ $laporan->fotos->count() }})</span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">
                    @foreach($laporan->fotos as $foto)
                    <div style="aspect-ratio:1;border-radius:3px;overflow:hidden;background:var(--placeholder);cursor:pointer;"
                         onclick="openFoto('{{ asset('storage/'.$foto->path) }}')">
                        <img src="{{ asset('storage/'.$foto->path) }}" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Deskripsi --}}
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title">Deskripsi & Detail</span></div>
            <div class="card-body">
                <div class="mb-3">
                    <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:6px;">Deskripsi Umum</div>
                    <p style="font-size:0.88rem;line-height:1.8;color:var(--teks);">{{ $laporan->deskripsi_umum }}</p>
                </div>
                @if($laporan->sejarah_asal_usul)
                <div class="mb-3">
                    <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:6px;">Sejarah & Asal-Usul</div>
                    <p style="font-size:0.88rem;line-height:1.8;color:var(--teks);">{{ $laporan->sejarah_asal_usul }}</p>
                </div>
                @endif
                @if($laporan->nilai_makna_budaya)
                <div>
                    <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:6px;">Nilai & Makna Budaya</div>
                    <p style="font-size:0.88rem;line-height:1.8;color:var(--teks);">{{ $laporan->nilai_makna_budaya }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Aksi Verifikasi --}}
        <div class="card" style="border-top:3px solid var(--emas);">
            <div class="card-header-custom"><span class="title">Keputusan Verifikasi</span></div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Setujui --}}
                    <div class="col-md-6">
                        <div style="background:rgba(45,90,39,0.05);border:1px solid rgba(45,90,39,0.2);border-radius:4px;padding:1.25rem;">
                            <div style="font-weight:600;color:var(--hijau);margin-bottom:8px;font-size:0.88rem;">
                                <i class="bi bi-check-circle me-2"></i>Setujui Laporan
                            </div>
                            <p style="font-size:0.78rem;color:var(--abu-gelap);margin-bottom:12px;">OPK akan masuk peta resmi & dashboard Pemkab Badung.</p>
                            <form method="POST" action="{{ route('admin.verifikasi.setujui', $laporan) }}">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan verifikator (opsional)..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm w-100"
                                        style="background:var(--hijau);color:white;border:none;"
                                        onclick="event.preventDefault(); swalKonfirmasi({title:'Setujui Laporan',text:'Setujui laporan ini dan masukkan ke peta OPK?',icon:'question',confirmText:'Setujui',confirmColor:'var(--hijau)',onConfirm:()=>this.closest('form').submit()})">
                                    <i class="bi bi-check2 me-1"></i>Setujui & Masukkan Peta
                                </button>
                            </form>
                        </div>
                    </div>
                    {{-- Tolak --}}
                    <div class="col-md-6">
                        <div style="background:rgba(192,57,43,0.05);border:1px solid rgba(192,57,43,0.2);border-radius:4px;padding:1.25rem;">
                            <div style="font-weight:600;color:var(--merah);margin-bottom:8px;font-size:0.88rem;">
                                <i class="bi bi-x-circle me-2"></i>Tolak Laporan
                            </div>
                            <p style="font-size:0.78rem;color:var(--abu-gelap);margin-bottom:12px;">Laporan ditolak dan pelapor akan diberitahu.</p>
                            <form method="POST" action="{{ route('admin.verifikasi.tolak', $laporan) }}">
                                @csrf
                                <div class="mb-2">
                                    <select name="alasan" class="form-select form-select-sm" required>
                                        <option value="tidak_valid">Data tidak valid</option>
                                        <option value="duplikat">Duplikat</option>
                                        <option value="kurang_data">Data tidak lengkap</option>
                                        <option value="diluar_wilayah">Luar wilayah Badung</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <textarea name="catatan" class="form-control form-control-sm" rows="2" placeholder="Catatan alasan penolakan..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-danger w-100">
                                    <i class="bi bi-x me-1"></i>Tolak Laporan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="col-md-4">
        {{-- Lokasi --}}
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-geo me-2"></i>Lokasi</span></div>
            <div class="card-body p-0">
                @if($laporan->latitude && $laporan->longitude)
                <div id="petaVerif" style="height:160px;"></div>
                @endif
                <div style="padding:1rem;">
                    @foreach([['Kecamatan',$laporan->kecamatan?->nama],['Desa Dinas',$laporan->desaDinas?->nama],['Desa Adat',$laporan->nama_desa_adat],['Banjar Adat',$laporan->banjar_adat],['Lokasi Spesifik',$laporan->lokasi_spesifik],['GPS',$laporan->latitude ? number_format($laporan->latitude,5).', '.number_format($laporan->longitude,5) : '—']] as [$k,$v])
                    @if($v)<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--garis-terang);font-size:0.78rem;"><span style="color:var(--abu);width:100px;flex-shrink:0;">{{ $k }}</span><span style="font-weight:500;text-align:right;word-break:break-all;">{{ $v }}</span></div>@endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Atribut --}}
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-tags me-2"></i>Atribut OPK</span></div>
            <div class="card-body p-0"><div style="padding:0 1rem;">
                @foreach([['Tahun',$laporan->tahun_keterangan ?? ($laporan->tahun_diketahui ? (string)$laporan->tahun_diketahui : null)],['Bahasa',$laporan->bahasa_digunakan],['Aksara',$laporan->aksara_digunakan],['Frekuensi',$laporan->frekuensi_pelaksanaan ? ucwords(str_replace('_',' ',$laporan->frekuensi_pelaksanaan)) : null],['Kepemilikan',$laporan->status_kepemilikan ? ucwords(str_replace('_',' ',$laporan->status_kepemilikan)) : null]] as [$k,$v])
                @if($v)<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--garis-terang);font-size:0.78rem;"><span style="color:var(--abu);width:100px;flex-shrink:0;">{{ $k }}</span><span style="font-weight:500;text-align:right;">{{ $v }}</span></div>@endif
                @endforeach
            </div></div>
        </div>

        {{-- Pelapor --}}
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-person me-2"></i>Pelapor</span></div>
            <div class="card-body p-0"><div style="padding:0 1rem;">
                @foreach([['Tipe',ucwords(str_replace('_',' ',$laporan->tipe_pelapor))],['Nama',$laporan->pelapor_nama],['WA',$laporan->pelapor_whatsapp],['Email',$laporan->pelapor_email],['Dikirim',$laporan->created_at->isoFormat('D MMM Y, HH:mm')]] as [$k,$v])
                @if($v)<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--garis-terang);font-size:0.78rem;"><span style="color:var(--abu);width:80px;flex-shrink:0;">{{ $k }}</span><span style="font-weight:500;text-align:right;word-break:break-all;">{{ $v }}</span></div>@endif
                @endforeach
            </div></div>
        </div>

        {{-- Dokumen --}}
        @if($laporan->dokumens->count() > 0)
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title"><i class="bi bi-file-earmark me-2"></i>Dokumen</span></div>
            <div class="card-body">
                @foreach($laporan->dokumens as $dok)
                <a href="{{ asset('storage/'.$dok->path) }}" target="_blank"
                   class="d-flex align-items-center gap-2 p-2 rounded mb-1 text-decoration-none"
                   style="background:var(--input-bg);border:1px solid var(--input-bg);color:var(--tanah);">
                    <i class="bi bi-file-earmark-pdf" style="color:#c0392b;"></i>
                    <span style="font-size:0.78rem;flex:1;">{{ $dok->nama_file }}</span>
                    <i class="bi bi-download" style="font-size:0.75rem;color:var(--abu);"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- AI Chat --}}
        @include('components.ai-chat', ['laporan' => $laporan])

    </div>
</div>

@include('components.foto-modal')
@endsection

@push('scripts')
<script>
@if($laporan->latitude && $laporan->longitude)
const m = L.map('petaVerif').setView([{{ $laporan->latitude }},{{ $laporan->longitude }}],15);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(m);
L.marker([{{ $laporan->latitude }},{{ $laporan->longitude }}]).addTo(m);
@endif
</script>
@endpush
