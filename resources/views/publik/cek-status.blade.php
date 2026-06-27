@extends('layouts.publik')

@section('title', 'Cek Status Laporan — SIOPK Badung')

@push('styles')
<style>
    .container-status { max-width: 580px; margin: 0 auto; padding: 1.5rem 1rem; }
    .page-title { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 700; }
    .search-card { background: white; border: 1px solid var(--garis); border-radius: 4px; padding: 1.5rem; margin-bottom: 1.5rem; border-top: 4px solid var(--emas); }
    .form-control { border: 1px solid var(--garis); border-radius: 3px; font-size: 0.9rem; background: var(--input-bg); }
    .form-control:focus { border-color: var(--emas); box-shadow: 0 0 0 3px rgba(200,146,42,0.12); }
    .btn-emas { background: var(--emas); color: var(--tanah); border: none; font-weight: 600; padding: 10px 24px; border-radius: 3px; }
    .btn-emas:hover { background: var(--emas-muda); color: var(--tanah); }
    .result-card { background: white; border: 1px solid var(--garis); border-radius: 4px; overflow: hidden; }
    .result-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--garis); background: var(--input-bg); }
    .result-body { padding: 1.5rem; }
    .info-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 10px 0; border-bottom: 1px solid var(--garis-terang); font-size: 0.84rem; }
    .info-row:last-child { border-bottom: none; }
    .info-key { color: var(--abu); flex-shrink: 0; width: 140px; }
    .info-val { font-weight: 500; text-align: right; }
    .timeline { padding: 0; list-style: none; }
    .timeline-item { display: flex; gap: 12px; padding-bottom: 16px; position: relative; }
    .timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 11px; top: 24px; width: 2px; height: calc(100% - 24px); background: var(--input-bg); }
    .timeline-dot { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; z-index: 1; }
    .timeline-content { padding-top: 2px; }
    .timeline-label { font-size: 0.8rem; font-weight: 600; }
    .timeline-meta { font-size: 0.72rem; color: var(--abu); margin-top: 2px; }
    .timeline-note { font-size: 0.75rem; color: var(--abu-gelap); margin-top: 4px; background: var(--krem); border-radius: 3px; padding: 6px 8px; }
    .status-chip { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-status">
    <div class="mb-4">
        <h1 class="page-title">Cek Status Laporan</h1>
        <p style="color:var(--abu);font-size:0.85rem;">Masukkan kode laporan untuk melihat perkembangan verifikasi</p>
    </div>

    <div class="search-card">
        <form method="GET" action="{{ route('publik.lapor.status') }}">
            <label style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Kode Laporan</label>
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
        <div style="background:rgba(192,57,43,0.08);border-left:3px solid var(--merah);padding:12px 16px;border-radius:0 3px 3px 0;font-size:0.84rem;color:var(--merah);">
            <i class="bi bi-exclamation-circle me-2"></i>Kode laporan <strong>{{ $kode }}</strong> tidak ditemukan. Pastikan kode sudah benar.
        </div>
    @endif

    @if($laporan)
    <div class="result-card">
        <div class="result-header">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <div style="font-size:0.68rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">
                        {{ $laporan->kode_laporan }}
                    </div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:700;">
                        {{ $laporan->nama_opk }}
                    </div>
                    <div style="font-size:0.75rem;color:var(--abu);margin-top:2px;">
                        {{ $laporan->kategori?->ikon }} {{ $laporan->kategori?->nama }} &nbsp;·&nbsp;
                        {{ $laporan->kecamatan?->nama }}
                    </div>
                </div>
                @php
                    $statusConfig = [
                        'menunggu'     => ['label'=>'Menunggu Verifikasi', 'bg'=>'rgba(212,160,23,0.1)', 'color'=>'#8a6010'],
                        'ai_review'    => ['label'=>'AI Sedang Review',    'bg'=>'rgba(41,128,185,0.1)', 'color'=>'#2980b9'],
                        'review_dinas' => ['label'=>'Ditinjau Dinas',      'bg'=>'rgba(200,146,42,0.1)', 'color'=>'var(--emas-gelap)'],
                        'disetujui'    => ['label'=>'Disetujui',           'bg'=>'rgba(45,90,39,0.1)',   'color'=>'var(--hijau)'],
                        'ditolak'      => ['label'=>'Ditolak',             'bg'=>'rgba(192,57,43,0.1)', 'color'=>'var(--merah)'],
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
                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:8px;">Detail Laporan</div>
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

            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--abu);margin-bottom:12px;">Riwayat Status</div>
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
            <div style="background:rgba(45,90,39,0.08);border-left:3px solid var(--hijau);padding:12px 14px;border-radius:0 3px 3px 0;font-size:0.8rem;color:var(--hijau);margin-top:1rem;">
                <strong>Laporan disetujui!</strong> OPK ini kini telah masuk ke dalam peta resmi Kabupaten Badung dan dapat dipantau oleh Dinas Kebudayaan. Terima kasih atas kontribusi Anda!
            </div>
            @elseif($laporan->status_verifikasi === 'ditolak')
            <div style="background:rgba(192,57,43,0.08);border-left:3px solid var(--merah);padding:12px 14px;border-radius:0 3px 3px 0;font-size:0.8rem;color:var(--merah);margin-top:1rem;">
                <strong>Laporan tidak dapat diproses.</strong> Silakan perbaiki data dan kirim laporan baru jika diperlukan.
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
