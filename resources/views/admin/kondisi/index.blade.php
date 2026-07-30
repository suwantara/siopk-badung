@extends('layouts.app')
@section('title','Manajemen Kondisi OPK')
@section('page-title','Manajemen Kondisi OPK')

@push('styles')
<style>
    .knd-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1rem; }
    .knd-card {
        background: white; border: 1px solid var(--border-default); border-radius: 6px;
        overflow: hidden; transition: box-shadow 0.15s;
    }
    .knd-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
    .knd-card-header {
        display: flex; align-items: center; gap: 12px;
        padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light);
        background: var(--bg-input);
    }
    .knd-color-swatch {
        width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0;
        border: 2px solid rgba(0,0,0,0.1);
    }
    .knd-card-title { font-family: 'Cormorant Garamond', serif; font-size: 1.1rem; font-weight: 700; color: var(--tanah); }
    .knd-card-kode { font-size: 0.7rem; color: var(--abu); text-transform: uppercase; letter-spacing: 0.5px; }
    .knd-card-body { padding: 1rem 1.25rem; }
    .knd-card-meta { font-size: 0.72rem; color: var(--abu); }
    .knd-card-actions { display: flex; gap: 6px; padding: 0.75rem 1.25rem; border-top: 1px solid var(--border-light); justify-content: flex-end; }
    .knd-card-actions form { display: inline; }
    @media (max-width: 768px) { .knd-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<x-ui.page-header title="Kondisi OPK" subtitle="Kelola kategori kondisi Objek Pemajuan Kebudayaan">
    <x-slot:action>
        <button class="btn btn-emas btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle me-1"></i>Tambah Kondisi
        </button>
    </x-slot:action>
</x-ui.page-header>

<div class="knd-grid">
    @foreach($kondisiItems as $k)
    <div class="knd-card">
        <div class="knd-card-header">
            <div class="knd-color-swatch" style="background-color:{{ $k->warna }};"></div>
            <div style="flex:1;min-width:0;">
                <div class="knd-card-title">{{ $k->nama }}</div>
                <div class="knd-card-kode">Kode: {{ $k->kode }}</div>
            </div>
        </div>
        <div class="knd-card-body">
            @if($k->deskripsi)
                <div class="knd-card-meta mb-2">{{ $k->deskripsi }}</div>
            @endif
            <div class="knd-card-meta">
                @if($k->laporans_count)
                    <strong style="color:var(--tanah);">{{ $k->laporans_count }}</strong> OPK terdaftar
                @else
                    Belum ada OPK
                @endif
            </div>

            <form method="POST" action="{{ route('admin.kondisi.update', $k) }}" class="mt-3">
                @csrf @method('PUT')
                {{-- SCOPED: edit.{id}.field_name — each card isolated --}}
                <div class="row g-2">
                    <div class="col-6">
                        <label class="t-caption d-block mb-1">Kode</label>
                        <input type="text" name="edit[{{ $k->id }}][kode]"
                               value="{{ old("edit.{$k->id}.kode", $k->kode) }}"
                               class="form-control form-control-sm @error("edit.{$k->id}.kode") is-invalid @enderror"
                               style="text-transform:lowercase;">
                        @error("edit.{$k->id}.kode")<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="t-caption d-block mb-1">Urutan</label>
                        <input type="number" name="edit[{{ $k->id }}][urutan]"
                               value="{{ old("edit.{$k->id}.urutan", $k->urutan) }}"
                               class="form-control form-control-sm @error("edit.{$k->id}.urutan") is-invalid @enderror" min="0">
                        @error("edit.{$k->id}.urutan")<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="t-caption d-block mb-1">Nama</label>
                        <input type="text" name="edit[{{ $k->id }}][nama]"
                               value="{{ old("edit.{$k->id}.nama", $k->nama) }}"
                               class="form-control form-control-sm @error("edit.{$k->id}.nama") is-invalid @enderror" required>
                        @error("edit.{$k->id}.nama")<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="t-caption d-block mb-1">Deskripsi</label>
                        <input type="text" name="edit[{{ $k->id }}][deskripsi]"
                               value="{{ old("edit.{$k->id}.deskripsi", $k->deskripsi) }}"
                               class="form-control form-control-sm @error("edit.{$k->id}.deskripsi") is-invalid @enderror"
                               placeholder="Penjelasan singkat...">
                        @error("edit.{$k->id}.deskripsi")<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-6">
                        <label class="t-caption d-block mb-1">Warna</label>
                        <div class="input-group input-group-sm">
                            <input type="color" name="edit[{{ $k->id }}][warna]"
                                   value="{{ old("edit.{$k->id}.warna", $k->warna) }}"
                                   class="form-control form-control-color @error("edit.{$k->id}.warna") is-invalid @enderror"
                                   style="max-width:44px;padding:2px;">
                            <input type="text" class="form-control form-control-sm font-monospace"
                                   value="{{ old("edit.{$k->id}.warna", $k->warna) }}"
                                   onchange="this.previousElementSibling.value = this.value"
                                   placeholder="#RRGGBB" style="max-width:100px;">
                            @error("edit.{$k->id}.warna")<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12 mt-1">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-check-lg me-1"></i>Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="knd-card-actions">
            <form method="POST" action="{{ route('admin.kondisi.destroy', $k) }}"
                  onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Kondisi',text:'Hapus kondisi {{ $k->nama }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                        @if($k->laporans_count > 0) disabled title="Tidak dapat dihapus — digunakan {{ $k->laporans_count }} OPK" @endif>
                    <i class="bi bi-trash me-1"></i>
                    @if($k->laporans_count > 0) Terpakai @else Hapus @endif
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

<p class="t-caption text-muted mt-3">
    <i class="bi bi-info-circle me-1"></i>
    Kondisi yang sudah digunakan OPK tidak dapat dihapus. Perubahan warna akan langsung terlihat di peta dan dashboard.
</p>

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-top:3px solid var(--emas);">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size:1rem;">
                    <i class="bi bi-plus-circle me-2" style="color:var(--emas);"></i>Tambah Kondisi Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.kondisi.store') }}">
                @csrf
                <input type="hidden" name="_action" value="store">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="t-caption d-block mb-1">Kode <span style="color:var(--merah);">*</span></label>
                            <input type="text" name="store[kode]" class="form-control @error('store.kode') is-invalid @enderror"
                                   value="{{ old('store.kode') }}" required style="text-transform:lowercase;"
                                   placeholder="contoh: punah">
                            @error('store.kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="t-caption d-block mb-1">Urutan</label>
                            <input type="number" name="store[urutan]" class="form-control @error('store.urutan') is-invalid @enderror"
                                   value="{{ old('store.urutan', $kondisiItems->max('urutan') + 1) }}" min="0">
                            @error('store.urutan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="t-caption d-block mb-1">Nama <span style="color:var(--merah);">*</span></label>
                            <input type="text" name="store[nama]" class="form-control @error('store.nama') is-invalid @enderror"
                                   value="{{ old('store.nama') }}" required placeholder="Contoh: Punah">
                            @error('store.nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="t-caption d-block mb-1">Deskripsi</label>
                            <input type="text" name="store[deskripsi]" class="form-control @error('store.deskripsi') is-invalid @enderror"
                                   value="{{ old('store.deskripsi') }}" placeholder="Penjelasan singkat...">
                            @error('store.deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="t-caption d-block mb-1">Warna <span style="color:var(--merah);">*</span></label>
                            <div class="input-group">
                                <input type="color" id="storeWarnaColor" class="form-control form-control-color @error('store.warna') is-invalid @enderror"
                                       value="{{ old('store.warna', '#6c757d') }}" style="max-width:50px;padding:2px;">
                                <input type="text" name="store[warna]" id="storeWarnaText"
                                       value="{{ old('store.warna', '#6c757d') }}"
                                       class="form-control font-monospace @error('store.warna') is-invalid @enderror"
                                       oninput="document.getElementById('storeWarnaColor').value = this.value"
                                       placeholder="#RRGGBB">
                                @error('store.warna')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-emas">
                        <i class="bi bi-check-lg me-1"></i>Simpan Kondisi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const storeColor = document.getElementById('storeWarnaColor');
    const storeText = document.getElementById('storeWarnaText');
    if (storeColor && storeText) {
        storeColor.addEventListener('input', function() { storeText.value = this.value; });
    }
    @if($errors->any() && $errors->has(['store.kode','store.nama','store.warna','store.urutan']))
        new bootstrap.Modal(document.getElementById('modalTambah')).show();
    @endif
});
</script>
@endpush
@endsection
