@extends('layouts.publik')

@section('title', 'Cek Status Laporan — SIOPK Badung')

@section('content')
<div class="container-status">
    <div class="mb-4">
        <h1 class="page-title">Cek Status Laporan</h1>
        <p style="color:var(--abu)" class="t-body">Masukkan kode laporan untuk melihat perkembangan verifikasi</p>
    </div>

    <div class="search-card">
        <form method="GET" action="{{ route('publik.lapor.status') }}">
            <label style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">Kode Laporan</label>
            <div class="d-flex gap-2 mt-2">
                <input type="text" name="kode_laporan" class="form-control"
                       value="{{ $kode }}" placeholder="Contoh: SIOPK-2025-00001"
                       style="font-family:'Courier New',monospace;letter-spacing:0.05em;">
                <button type="submit" class="btn btn-emas">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>

    @if($kode && !$laporan)
        <div style="background:var(--surface-merah);border-left:3px solid var(--merah);padding:12px 16px;border-radius:0 3px 3px 0;color:var(--merah)" class="t-body">
            <i class="bi bi-exclamation-circle me-2"></i>Kode laporan <strong>{{ $kode }}</strong> tidak ditemukan. Pastikan kode sudah benar.
        </div>
    @endif

    @if($laporan)
    <div class="result-card">
        <div class="result-header">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <div style="color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px" class="t-caption">
                        {{ $laporan->kode_laporan }}
                    </div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:700;">
                        {{ $laporan->nama_opk }}
                    </div>
                    <div style="color:var(--abu);margin-top:2px;" class="t-caption">
                        {{ $laporan->kategori?->ikon }} {{ $laporan->kategori?->nama }} &nbsp;·&nbsp;
                        {{ $laporan->kecamatan?->nama }}
                    </div>
                </div>
                @php
                    $statusConfig = [
                        'menunggu'     => ['label'=>'Menunggu Verifikasi', 'bg'=>'var(--surface-kuning)', 'color'=>'#8a6010'],
                        'ai_review'    => ['label'=>'AI Sedang Review',    'bg'=>'rgba(41,128,185,0.1)', 'color'=>'#2980b9'],
                        'review_dinas' => ['label'=>'Ditinjau Dinas',      'bg'=>'var(--surface-emas)', 'color'=>'var(--emas-gelap)'],
                        'disetujui'    => ['label'=>'Disetujui',           'bg'=>'var(--surface-hijau)',   'color'=>'var(--hijau)'],
                        'ditolak'      => ['label'=>'Ditolak',             'bg'=>'var(--surface-merah)', 'color'=>'var(--merah)'],
                        'duplikat'     => ['label'=>'Duplikat',            'bg'=>'rgba(107,114,128,0.1)','color'=>'var(--abu-gelap)'],
                    ];
                    $sc = $statusConfig[$laporan->status_verifikasi] ?? $statusConfig['menunggu'];
                @endphp
                <span class="status-chip" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                    {{ $sc['label'] }}
                </span>
            </div>
        </div>

        <div class="result-body">
            <div class="mb-4">
                <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:8px" class="t-caption">Detail Laporan</div>
                <div class="info-row">
                    <span class="info-key">Kecamatan</span>
                    <span class="info-val">{{ $laporan->kecamatan?->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Desa Dinas</span>
                    <span class="info-val">{{ $laporan->desaDinas?->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Desa Adat</span>
                    <span class="info-val">{{ $laporan->nama_desa_adat }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Kondisi</span>
                    <span class="info-val">
                        @if($laporan->kondisi === 'kritis') Kritis
                        @elseif($laporan->kondisi === 'waspada') Waspada
                        @else Baik @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-key">Tanggal Lapor</span>
                    <span class="info-val">{{ $laporan->created_at->isoFormat('D MMMM Y, HH:mm') }}</span>
                </div>
                @if($laporan->tanggal_verifikasi)
                <div class="info-row">
                    <span class="info-key">Tgl Verifikasi</span>
                    <span class="info-val">{{ $laporan->tanggal_verifikasi->isoFormat('D MMMM Y, HH:mm') }}</span>
                </div>
                @endif
                @if($laporan->catatan_verifikasi && in_array($laporan->status_verifikasi, ['disetujui','ditolak','duplikat']))
                <div class="info-row">
                    <span class="info-key">Catatan Dinas</span>
                    <span class="info-val">{{ $laporan->catatan_verifikasi }}</span>
                </div>
                @endif
            </div>

            <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:12px" class="t-caption">Riwayat Status</div>
            <ul class="timeline">
                <li class="timeline-item">
                    <div class="timeline-dot" style="background:var(--hijau);color:white;">✓</div>
                    <div class="timeline-content">
                        <div class="timeline-label">Laporan Dikirim</div>
                        <div class="timeline-meta">{{ $laporan->created_at->isoFormat('D MMM Y, HH:mm') }} · {{ $laporan->tipe_pelapor === 'masyarakat' ? 'Masyarakat Umum' : ($laporan->tipe_pelapor === 'tokoh_adat' ? 'Tokoh Adat' : 'Petugas Dinas') }}</div>
                    </div>
                </li>

                @foreach($laporan->riwayat as $rw)
                <li class="timeline-item">
                    <div class="timeline-dot" style="background:{{ in_array($rw->status_baru, ['disetujui']) ? 'var(--hijau)' : (in_array($rw->status_baru, ['ditolak','duplikat']) ? 'var(--merah)' : 'var(--emas)') }};color:white;">
                        {{ in_array($rw->status_baru, ['disetujui']) ? '✓' : (in_array($rw->status_baru, ['ditolak','duplikat']) ? '✕' : '→') }}
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-label">
                            {{ $statusConfig[$rw->status_baru]['label'] ?? ucfirst($rw->status_baru) }}
                        </div>
                        <div class="timeline-meta">{{ $rw->created_at->isoFormat('D MMM Y, HH:mm') }}
                            @if($rw->user) · {{ $rw->user->name }} @endif
                        </div>
                        @if($rw->catatan)
                        <div class="timeline-note">{{ $rw->catatan }}</div>
                        @endif
                    </div>
                </li>
                @endforeach

                @if($laporan->riwayat->isEmpty() && $laporan->status_verifikasi === 'menunggu')
                <li class="timeline-item">
                    <div class="timeline-dot" style="background:var(--input-bg);color:var(--abu);">⋯</div>
                    <div class="timeline-content">
                        <div class="timeline-label" style="color:var(--abu);">Menunggu Verifikasi Dinas</div>
                        <div class="timeline-meta">Laporan dalam antrian tim verifikator</div>
                    </div>
                </li>
                @endif
            </ul>

            @if($laporan->status_verifikasi === 'disetujui')
            <div style="background:var(--surface-hijau);border-left:3px solid var(--hijau);padding:12px 14px;border-radius:0 3px 3px 0;color:var(--hijau);margin-top:1rem" class="t-body">
                <strong>Laporan disetujui!</strong> OPK ini kini telah masuk ke dalam peta resmi Kabupaten Badung dan dapat dipantau oleh Dinas Kebudayaan. Terima kasih atas kontribusi Anda!
            </div>
            @elseif($laporan->status_verifikasi === 'ditolak')
            <div style="background:var(--surface-merah);border-left:3px solid var(--merah);padding:12px 14px;border-radius:0 3px 3px 0;color:var(--merah);margin-top:1rem" class="t-body">
                <strong>Laporan tidak dapat diproses.</strong> Silakan perbaiki data dan kirim laporan baru jika diperlukan.
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
