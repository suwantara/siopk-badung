@extends('layouts.app')
@section('title', 'Verifikasi Laporan')
@section('page-title', 'Verifikasi Laporan Masyarakat')

@push('styles')
<style>
    .queue-card {
        border-left: 4px solid var(--border-default);
        transition: box-shadow 0.15s;
    }
    .queue-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
    .queue-card.kritis  { border-left-color: var(--merah); }
    .queue-card.waspada { border-left-color: var(--kuning); }
    .queue-card.baik    { border-left-color: var(--hijau); }

    .queue-thumb {
        width: 80px; height: 80px; border-radius: 6px;
        overflow: hidden; flex-shrink: 0;
        background: var(--bg-placeholder);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem;
    }
    .queue-thumb img { width: 100%; height: 100%; object-fit: cover; }

    .queue-ai-score {
        text-align: center; min-width: 56px; flex-shrink: 0;
    }
    .queue-ai-val {
        font-family: 'Courier New', monospace;
        font-size: 1.5rem; font-weight: 700;
        line-height: 1;
    }
    .queue-ai-label {
        font-size: 0.6rem; text-transform: uppercase;
        letter-spacing: 0.08em; margin-top: 2px;
    }
</style>
@endpush

@section('content')
<x-ui.page-header title="Antrian Verifikasi" :subtitle="$laporans->total() . ' laporan menunggu · diurutkan AI urgency score'" />

<x-ui.filter-bar reset-url="{{ route('admin.verifikasi.index') }}">
    <div class="col-md-3">
        <label class="t-caption d-block mb-1">Kondisi</label>
        <select name="kondisi" class="form-select form-select-sm">
            <option value="">Semua Kondisi</option>
            <option value="kritis"  {{ request('kondisi') === 'kritis'  ? 'selected' : '' }}>🔴 Kritis</option>
            <option value="waspada" {{ request('kondisi') === 'waspada' ? 'selected' : '' }}>🟡 Waspada</option>
            <option value="baik"    {{ request('kondisi') === 'baik'    ? 'selected' : '' }}>🟢 Baik</option>
        </select>
    </div>
</x-ui.filter-bar>

@forelse($laporans as $laporan)
<div class="card mb-3 queue-card {{ $laporan->kondisi }}">
    <div class="card-body">
        <div class="d-flex gap-3 align-items-start">
            <div class="queue-thumb">
                @if($laporan->fotoUtama)
                    <img src="{{ asset('storage/' . $laporan->fotoUtama->path) }}" alt="Foto {{ $laporan->nama_opk }}">
                @else
                    {{ $laporan->kategori?->ikon ?? '🏛️' }}
                @endif
            </div>

            <div class="flex-grow-1" style="min-width:0;">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <a href="{{ route('admin.verifikasi.show', $laporan) }}" class="text-decoration-none">
                            <h6 class="mb-1 fw-bold t-body-lg" style="color:var(--tanah);">{{ $laporan->nama_opk }}</h6>
                        </a>
                        <div class="d-flex gap-2 flex-wrap mb-1">
                            <x-ui.badge-kategori :ikon="$laporan->kategori?->ikon" :nama="$laporan->kategori?->nama" />
                            <x-ui.badge-kondisi :kondisi="$laporan->kondisi" />
                            <span class="t-label" style="background:var(--bg-input);color:var(--text-muted);padding:2px 8px;border-radius:10px;">{{ strtoupper(str_replace('_',' ',$laporan->status_verifikasi)) }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 t-caption" style="color:var(--text-muted);">
                            <span>📍 {{ $laporan->kecamatan?->nama }}</span>
                            <span>🏘️ {{ $laporan->nama_desa_adat }}</span>
                            <span>👤 {{ $laporan->pelapor_nama }}</span>
                            <span>🕐 {{ $laporan->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if($laporan->ai_urgency_score)
                    <div class="queue-ai-score ms-3">
                        <div class="queue-ai-val" style="color:{{ $laporan->kondisi === 'kritis' ? 'var(--merah)' : ($laporan->kondisi === 'waspada' ? 'var(--kuning)' : 'var(--hijau)') }}">
                            {{ number_format($laporan->ai_urgency_score, 1) }}
                        </div>
                        <div class="queue-ai-label" style="color:var(--abu);">AI Score</div>
                    </div>
                    @endif
                </div>

                @if($laporan->ai_rekomendasi)
                <div style="background:var(--surface-emas-light);border:1px solid var(--border-emas-light);border-radius:3px;padding:6px 10px;margin-top:8px;" class="t-caption">
                    🤖 <strong style="color:var(--emas-gelap);">AI:</strong>
                    {{ Str::limit($laporan->ai_rekomendasi, 200) }}
                    @if($laporan->ai_duplikat_score > 50)
                        <span style="color:var(--emas-muda);">⚠ Duplikat {{ number_format($laporan->ai_duplikat_score, 0) }}%</span>
                    @endif
                </div>
                @endif

                <div class="d-flex gap-2 mt-2">
                    <a href="{{ route('admin.verifikasi.show', $laporan) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-search me-1"></i>Tinjau Detail
                    </a>

                    <form method="POST" action="{{ route('admin.verifikasi.setujui', $laporan) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success"
                                onclick="event.preventDefault(); swalKonfirmasi({title:'Setujui Laporan',text:'Setujui {{ $laporan->kode_laporan }} — {{ $laporan->nama_opk }}?',icon:'question',confirmText:'Setujui',confirmColor:'var(--hijau)',onConfirm:()=>this.closest('form').submit()})">
                            <i class="bi bi-check2 me-1"></i>Setujui
                        </button>
                    </form>

                    <button type="button" class="btn btn-sm btn-outline-danger btn-tolak"
                            data-id="{{ $laporan->id }}"
                            data-kode="{{ $laporan->kode_laporan }}">
                        <i class="bi bi-x-circle me-1"></i>Tolak
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-check-circle" style="font-size:3rem;color:var(--hijau);display:block;margin-bottom:12px;"></i>
        <div class="t-body-lg" style="color:var(--text-muted);">Semua laporan sudah diverifikasi. Tidak ada antrian.</div>
    </div>
</div>
@endforelse

<div class="mt-3">{{ $laporans->links() }}</div>

{{-- Single unified Tolak modal --}}
<div class="modal fade" id="modalTolak" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-top:4px solid var(--merah);">
            <div class="modal-header">
                <h5 class="modal-title t-subheading">Tolak Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="formTolak" action="">
                @csrf
                <div class="modal-body">
                    <p class="t-body" style="color:var(--text-muted);" id="tolakInfo"></p>
                    <div class="mb-3">
                        <label class="t-caption d-block mb-1">Alasan Penolakan</label>
                        <select name="alasan" class="form-select" required>
                            <option value="tidak_valid">Data tidak valid</option>
                            <option value="duplikat">Duplikat laporan lain</option>
                            <option value="kurang_data">Data tidak lengkap</option>
                            <option value="diluar_wilayah">Di luar wilayah Badung</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="t-caption d-block mb-1">Catatan untuk Pelapor</label>
                        <textarea name="catatan" class="form-control" rows="3" required
                                  placeholder="Jelaskan alasan penolakan agar pelapor memahami..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-x me-1"></i>Tolak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-tolak').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const kode = this.dataset.kode;
            document.getElementById('formTolak').action = "{{ url('admin/verifikasi') }}/" + id + "/tolak";
            document.getElementById('tolakInfo').textContent = 'Anda akan menolak laporan: ' + kode;
            new bootstrap.Modal(document.getElementById('modalTolak')).show();
        });
    });
});
</script>
@endpush
