@extends('layouts.publik')

@section('title', 'Laporan Terkirim — SIOPK Badung')

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
                <div style="color:var(--abu);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px" class="t-caption">Kode Laporan Anda</div>
                <div class="kode-val">{{ $laporan->kode_laporan }}</div>
                <div class="kode-hint">Simpan kode ini untuk memantau status laporan</div>
            </div>

            <x-ui.info-rows :rows="[
                ['Objek Budaya', $laporan->nama_opk],
                ['Jenis OPK', $laporan->kategori?->ikon.' '.$laporan->kategori?->nama],
                ['Kecamatan', $laporan->kecamatan?->nama],
                ['Desa Adat', $laporan->nama_desa_adat],
                ['Notifikasi', $laporan->pelapor_whatsapp],
            ]" key-width="140px" />

            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border-light);" class="t-body">
                <span style="color:var(--abu);width:140px;flex-shrink:0;">Status</span>
                <span style="background:var(--surface-kuning);color:#8a6010;padding:2px 10px;border-radius:10px;font-size:0.72rem;font-weight:600;">Menunggu Verifikasi</span>
            </div>

            <div style="background:rgba(45,90,39,0.06);border-left:3px solid var(--hijau);padding:10px 12px;border-radius:0 3px 3px 0;color:var(--hijau);margin-bottom:1.5rem;line-height:1.6" class="t-body">
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
