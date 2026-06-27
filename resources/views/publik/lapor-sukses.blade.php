@extends('layouts.publik')

@section('title', 'Laporan Terkirim — SIOPK Badung')

@push('styles')
<style>
    .sukses-container { display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 56px); padding: 2rem 1rem; }
    .sukses-card { background: white; border-radius: 6px; max-width: 520px; width: 100%; border-top: 4px solid var(--hijau); box-shadow: 0 8px 30px rgba(0,0,0,0.1); overflow: hidden; }
    .sukses-header { background: linear-gradient(135deg, var(--hijau), var(--hijau)); padding: 2rem; text-align: center; color: white; }
    .sukses-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
    .sukses-title { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 700; margin-bottom: 4px; }
    .sukses-sub { font-size: 0.82rem; opacity: 0.85; }
    .sukses-body { padding: 1.75rem; }
    .kode-box { background: var(--krem); border: 1px solid var(--garis); border-radius: 3px; padding: 1rem; text-align: center; margin-bottom: 1.5rem; }
    .kode-val { font-family: 'Courier New', monospace; font-size: 1.3rem; font-weight: 700; color: var(--tanah); letter-spacing: 0.1em; }
    .kode-hint { font-size: 0.72rem; color: var(--abu); margin-top: 4px; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--garis-terang); font-size: 0.82rem; }
    .info-row:last-child { border-bottom: none; }
    .info-key { color: var(--abu); }
    .info-val { font-weight: 500; }
    .btn-emas { background: var(--emas); color: var(--tanah); border: none; font-weight: 600; padding: 10px 24px; border-radius: 3px; text-decoration: none; display: inline-block; }
    .btn-emas:hover { background: var(--emas-muda); color: var(--tanah); }
    .btn-outline { border: 1px solid var(--garis); padding: 10px 24px; border-radius: 3px; text-decoration: none; color: var(--tanah); font-size: 0.88rem; }
    .btn-outline:hover { background: var(--krem); }
</style>
@endpush

@section('content')
<div class="sukses-container">
    <div class="sukses-card">
        <div class="sukses-header">
            <div class="sukses-icon"><i class="bi bi-check-circle"></i></div>
            <div class="sukses-title">Laporan Terkirim!</div>
            <div class="sukses-sub">Terima kasih telah berkontribusi melestarikan budaya Bali</div>
        </div>
        <div class="sukses-body">
            <div class="kode-box">
                <div style="font-size:0.68rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;">Kode Laporan Anda</div>
                <div class="kode-val">{{ $laporan->kode_laporan }}</div>
                <div class="kode-hint">Simpan kode ini untuk memantau status laporan</div>
            </div>

            <div class="mb-4">
                <div class="info-row">
                    <span class="info-key">Objek Budaya</span>
                    <span class="info-val">{{ $laporan->nama_opk }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Jenis OPK</span>
                    <span class="info-val">{{ $laporan->kategori?->ikon }} {{ $laporan->kategori?->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Kecamatan</span>
                    <span class="info-val">{{ $laporan->kecamatan?->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Desa Adat</span>
                    <span class="info-val">{{ $laporan->nama_desa_adat }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Status</span>
                    <span style="background:rgba(212,160,23,0.1);color:#8a6010;padding:2px 10px;border-radius:10px;font-size:0.72rem;font-weight:600;">Menunggu Verifikasi</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Notifikasi</span>
                    <span class="info-val">{{ $laporan->pelapor_whatsapp }}</span>
                </div>
            </div>

            <div style="background:rgba(45,90,39,0.06);border-left:3px solid var(--hijau);padding:10px 12px;border-radius:0 3px 3px 0;font-size:0.78rem;color:var(--hijau);margin-bottom:1.5rem;line-height:1.6;">
                <strong>Langkah selanjutnya:</strong> Tim Dinas Kebudayaan Kabupaten Badung akan memverifikasi laporan Anda. Notifikasi akan dikirim ke WhatsApp Anda setelah proses verifikasi selesai.
            </div>

            <div class="d-flex gap-3">
                <a href="{{ route('publik.lapor.index') }}" class="btn-emas" style="flex:1;text-align:center;">
                    <i class="bi bi-plus-circle me-1"></i>Lapor OPK Lain
                </a>
                <a href="{{ route('publik.lapor.status') }}?kode_laporan={{ $laporan->kode_laporan }}"
                   class="btn-outline" style="flex:1;text-align:center;">
                    <i class="bi bi-search me-1"></i>Cek Status
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
