@extends('layouts.app')
@section('title', 'Verifikasi Laporan')
@section('page-title', 'Verifikasi Laporan Masyarakat')

@section('content')
<x-ui.page-header title="Antrian Verifikasi" :subtitle="$laporans->total() . ' laporan menunggu · diurutkan berdasarkan AI urgency score'" />

<x-ui.filter-bar reset-url="{{ route('admin.verifikasi.index') }}">
    <div class="col-md-3">
        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Kondisi</label>
        <select name="kondisi" class="form-select form-select-sm">
            <option value="">Semua Kondisi</option>
            <option value="kritis"  {{ request('kondisi') === 'kritis'  ? 'selected' : '' }}>Kritis</option>
            <option value="waspada" {{ request('kondisi') === 'waspada' ? 'selected' : '' }}>Waspada</option>
            <option value="baik"    {{ request('kondisi') === 'baik'    ? 'selected' : '' }}>Baik</option>
        </select>
    </div>
</x-ui.filter-bar>

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
                        <h6 class="mb-1 fw-bold" class="t-body-lg">{{ $laporan->nama_opk }}</h6>
                        <div class="d-flex gap-2 flex-wrap mb-1">
                            <x-ui.badge-kategori :ikon="$laporan->kategori?->ikon" :nama="$laporan->kategori?->nama" />
                            <x-ui.badge-kondisi :kondisi="$laporan->kondisi" />
                        </div>
                        <div style="color:var(--abu-gelap);" class="t-caption">
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
                        <div style="color:var(--abu);text-transform:uppercase;letter-spacing:0.06em" class="t-caption">AI Score</div>
                    </div>
                    @endif
                </div>

                {{-- AI Saran --}}
                @if($laporan->ai_rekomendasi)
                <div style="background:rgba(200,146,42,0.07);border:1px solid var(--border-emas);border-radius:3px;padding:6px 10px;margin-top:8px;color:var(--emas-gelap)" class="t-caption">
                    🤖 <strong>AI:</strong> {{ $laporan->ai_rekomendasi }}
                    @if($laporan->ai_duplikat_score > 50)
                        — <span style="color:var(--emas-muda);">⚠ Potensi duplikat {{ number_format($laporan->ai_duplikat_score, 0) }}%</span>
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
                <h5 class="modal-title" class="t-subheading">Tolak Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.verifikasi.tolak', $laporan) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-body">Alasan Penolakan</label>
                        <select name="alasan" class="form-select" required>
                            <option value="tidak_valid">Data tidak valid</option>
                            <option value="duplikat">Duplikat laporan lain</option>
                            <option value="kurang_data">Data tidak lengkap</option>
                            <option value="diluar_wilayah">Di luar wilayah Badung</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-body">Catatan untuk Pelapor</label>
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
        <div class="mt-3" style="color:var(--abu-gelap)" class="t-body-lg">Tidak ada laporan yang menunggu verifikasi.</div>
    </div>
</div>
@endforelse

{{-- Pagination --}}
<div class="mt-3">{{ $laporans->links() }}</div>
@endsection
