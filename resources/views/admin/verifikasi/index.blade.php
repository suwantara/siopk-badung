@extends('layouts.app')
@section('title', 'Verifikasi Laporan')
@section('page-title', 'Verifikasi Laporan Masyarakat')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">
            Antrian Verifikasi
        </h1>
        <p class="text-muted mb-0" style="font-size:0.82rem;">
            {{ $laporans->total() }} laporan menunggu · diurutkan berdasarkan AI urgency score
        </p>
    </div>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Kondisi</label>
                <select name="kondisi" class="form-select form-select-sm">
                    <option value="">Semua Kondisi</option>
                    <option value="kritis"  {{ request('kondisi') === 'kritis'  ? 'selected' : '' }}>Kritis</option>
                    <option value="waspada" {{ request('kondisi') === 'waspada' ? 'selected' : '' }}>Waspada</option>
                    <option value="baik"    {{ request('kondisi') === 'baik'    ? 'selected' : '' }}>Baik</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-emas">Filter</button>
                <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Daftar Laporan --}}
@forelse($laporans as $laporan)
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex gap-3 align-items-start">
            {{-- Foto --}}
            <div style="width:72px;height:72px;border-radius:4px;background:var(--placeholder);flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">
                @if($laporan->fotoUtama)
                    <img src="{{ asset('storage/' . $laporan->fotoUtama->path) }}" style="width:100%;height:100%;object-fit:cover;">
                @else
                    {{ $laporan->kategori?->ikon ?? '🏛️' }}
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <h6 class="mb-1 fw-bold" style="font-size:0.92rem;">{{ $laporan->nama_opk }}</h6>
                        <div class="d-flex gap-2 flex-wrap mb-1">
                            <span style="font-size:0.7rem;background:rgba(200,146,42,0.1);color:var(--emas-gelap);padding:2px 8px;border-radius:2px;font-weight:500;">
                                {{ $laporan->kategori?->ikon }} {{ $laporan->kategori?->nama }}
                            </span>
                            <span class="badge badge-{{ $laporan->kondisi }} rounded-pill" style="font-size:0.68rem;">
                                {{ ucfirst($laporan->kondisi) }}
                            </span>
                        </div>
                        <div style="font-size:0.75rem;color:var(--abu-gelap);">
                            📍 Kec. {{ $laporan->kecamatan?->nama }} &nbsp;·&nbsp;
                            🏘️ {{ $laporan->nama_desa_adat }} &nbsp;·&nbsp;
                            {{ $laporan->tipe_pelapor === 'masyarakat' ? '👤' : ($laporan->tipe_pelapor === 'tokoh_adat' ? '👘' : '🏛️') }}
                            {{ $laporan->pelapor_nama }} &nbsp;·&nbsp;
                            🕐 {{ $laporan->created_at->diffForHumans() }}
                        </div>
                    </div>
                    {{-- AI Score --}}
                    @if($laporan->ai_urgency_score)
                    <div class="text-center ms-3" style="flex-shrink:0;">
                        <div style="font-family:'Courier New',monospace;font-size:1.3rem;font-weight:700;color:{{ $laporan->kondisi === 'kritis' ? 'var(--merah)' : 'var(--kuning)' }}">
                            {{ number_format($laporan->ai_urgency_score, 1) }}
                        </div>
                        <div style="font-size:0.6rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.06em;">AI Score</div>
                    </div>
                    @endif
                </div>

                {{-- AI Saran --}}
                @if($laporan->ai_rekomendasi)
                <div style="background:rgba(200,146,42,0.07);border:1px solid rgba(200,146,42,0.2);border-radius:3px;padding:6px 10px;margin-top:8px;font-size:0.72rem;color:var(--emas-gelap);">
                    🤖 <strong>AI:</strong> {{ $laporan->ai_rekomendasi }}
                    @if($laporan->ai_duplikat_score > 50)
                        — <span style="color:var(--merah);">⚠ Potensi duplikat {{ number_format($laporan->ai_duplikat_score, 0) }}%</span>
                    @endif
                </div>
                @endif

                {{-- Aksi --}}
                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('admin.verifikasi.show', $laporan) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>

                    <form method="POST" action="{{ route('admin.verifikasi.setujui', $laporan) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm"
                                style="background:var(--hijau);color:white;border:none;"
                                onclick="event.preventDefault(); swalKonfirmasi({title:'Setujui Laporan',text:'Setujui laporan {{ $laporan->kode_laporan }}?',icon:'question',confirmText:'Setujui',confirmColor:'var(--hijau)',onConfirm:()=>this.closest('form').submit()})">
                            <i class="bi bi-check2 me-1"></i>Setujui
                        </button>
                    </form>

                    <button type="button" class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#modalTolak{{ $laporan->id }}">
                        <i class="bi bi-x me-1"></i>Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tolak --}}
<div class="modal fade" id="modalTolak{{ $laporan->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-top:4px solid var(--merah);">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:1rem;">Tolak Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.verifikasi.tolak', $laporan) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Alasan Penolakan</label>
                        <select name="alasan" class="form-select" required>
                            <option value="tidak_valid">Data tidak valid</option>
                            <option value="duplikat">Duplikat laporan lain</option>
                            <option value="kurang_data">Data tidak lengkap</option>
                            <option value="diluar_wilayah">Di luar wilayah Badung</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.78rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Catatan untuk Pelapor</label>
                        <textarea name="catatan" class="form-control" rows="3" required
                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">Tolak Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-check-circle" style="font-size:2.5rem;color:var(--hijau);"></i>
        <div class="mt-3" style="font-size:0.9rem;color:var(--abu-gelap);">Tidak ada laporan yang menunggu verifikasi.</div>
    </div>
</div>
@endforelse

{{-- Pagination --}}
<div class="mt-3">{{ $laporans->links() }}</div>
@endsection
