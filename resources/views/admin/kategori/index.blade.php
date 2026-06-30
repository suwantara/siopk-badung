@extends('layouts.app')
@section('title','Manajemen Kategori OPK')
@section('page-title','Manajemen Kategori OPK')

@push('styles')
<style>
    .kat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1rem; }
    .kat-card {
        background: white; border: 1px solid var(--border-default); border-radius: 6px;
        overflow: hidden; transition: box-shadow 0.15s;
    }
    .kat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .kat-card-header {
        display: flex; align-items: center; gap: 12px;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: var(--bg-input);
    }
    .kat-card-icon {
        width: 48px; height: 48px; border-radius: 8px;
        background: var(--surface-emas); display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; flex-shrink: 0;
    }
    .kat-card-number {
        width: 28px; height: 28px; border-radius: 50%;
        background: var(--emas); color: var(--tanah);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }
    .kat-card-title { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 700; color: var(--tanah); }
    .kat-card-desc { font-size: 0.78rem; color: var(--abu-gelap); margin-top: 2px; }
    .kat-card-body { padding: 1rem 1.25rem; }
    .kat-card-meta { font-size: 0.72rem; color: var(--abu); }
    .kat-card-actions { display: flex; gap: 6px; padding: 0.75rem 1.25rem; border-top: 1px solid var(--border-light); justify-content: flex-end; }
    .kat-card-actions form { display: inline; }

    @media (max-width: 768px) {
        .kat-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<x-ui.page-header title="Kategori OPK" subtitle="10 Objek Pemajuan Kebudayaan sesuai UU No. 5 Tahun 2017">
    <x-slot:action>
        <button class="btn btn-emas btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
        </button>
    </x-slot:action>
</x-ui.page-header>

<div class="kat-grid">
    @foreach($kategori as $cat)
    <div class="kat-card">
        <div class="kat-card-header">
            <span class="kat-card-number">{{ $cat->nomor }}</span>
            <span class="kat-card-icon">{{ $cat->ikon }}</span>
            <div style="flex:1;min-width:0;">
                <div class="kat-card-title">{{ $cat->nama }}</div>
                @if($cat->deskripsi)
                <div class="kat-card-desc">{{ $cat->deskripsi }}</div>
                @endif
            </div>
        </div>
        <div class="kat-card-body">
            <div class="kat-card-meta">
                @if($cat->laporans_count)
                    <strong style="color:var(--tanah);">{{ $cat->laporans_count }}</strong> OPK terdaftar
                @else
                    Belum ada OPK
                @endif
                &nbsp;·&nbsp; <a href="{{ route('admin.opk.index') }}?kategori_id={{ $cat->id }}" style="color:var(--emas);">Lihat Data →</a>
            </div>

            <form method="POST" action="{{ route('admin.kategori.update', $cat) }}" class="mt-3">
                @csrf @method('PUT')
                <div class="row g-2">
                    <div class="col-4">
                        <label class="t-caption d-block mb-1">Nomor</label>
                        <input type="number" name="nomor" value="{{ old("nomor.{$cat->id}", $cat->nomor) }}"
                               class="form-control form-control-sm" min="1" max="99">
                    </div>
                    <div class="col-8">
                        <label class="t-caption d-block mb-1">Nama</label>
                        <input type="text" name="nama" value="{{ old("nama.{$cat->id}", $cat->nama) }}"
                               class="form-control form-control-sm">
                    </div>
                    <div class="col-12">
                        <label class="t-caption d-block mb-1">Deskripsi</label>
                        <input type="text" name="deskripsi" value="{{ old("deskripsi.{$cat->id}", $cat->deskripsi) }}"
                               class="form-control form-control-sm" placeholder="Penjelasan singkat...">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="kat-card-actions">
            <form method="POST" action="{{ route('admin.kategori.destroy', $cat) }}"
                  onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Kategori',text:'Hapus kategori {{ $cat->nama }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                        @if($cat->laporans_count > 0) disabled title="Tidak dapat dihapus — sudah digunakan {{ $cat->laporans_count }} OPK" @endif>
                    <i class="bi bi-trash me-1"></i>
                    @if($cat->laporans_count > 0)
                        Terpakai
                    @else
                        Hapus
                    @endif
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<p class="t-caption text-muted mt-3">
    <i class="bi bi-info-circle me-1"></i>
    Kategori yang sudah digunakan OPK tidak dapat dihapus. Ubah nomor untuk mengatur urutan tampilan di publik.
</p>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-top:3px solid var(--emas);">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:1rem;">
                    <i class="bi bi-plus-circle me-2" style="color:var(--emas);"></i>Tambah Kategori Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.kategori.store') }}">
                @csrf
                <input type="hidden" name="_action" value="store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-3">
                            <label class="t-caption d-block mb-1">Nomor</label>
                            <input type="number" name="nomor" class="form-control @error('nomor') is-invalid @enderror"
                                   value="{{ old('nomor', $kategori->max('nomor') + 1) }}" min="1" max="99" required>
                            @error('nomor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-3">
                            <label class="t-caption d-block mb-1">Ikon</label>
                            <input type="text" name="ikon" class="form-control @error('ikon') is-invalid @enderror"
                                   value="{{ old('ikon') }}" placeholder="🎭" maxlength="10">
                            @error('ikon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="t-caption d-block mb-1">Nama Kategori <span style="color:var(--merah);">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama') }}" placeholder="Contoh: Seni Pertunjukan" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="t-caption d-block mb-1">Deskripsi</label>
                            <input type="text" name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                   value="{{ old('deskripsi') }}" placeholder="Penjelasan singkat kategori ini...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-emas">
                        <i class="bi bi-check-lg me-1"></i>Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
