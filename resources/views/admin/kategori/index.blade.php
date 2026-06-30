@extends('layouts.app')
@section('title','Manajemen Kategori OPK')
@section('page-title','Manajemen Kategori OPK')

@section('content')
<x-ui.page-header title="Kategori OPK" subtitle="10 Objek Pemajuan Kebudayaan sesuai UU No. 5 Tahun 2017">
    <x-slot:action>
        <button class="btn btn-emas btn-sm" data-bs-toggle="collapse" data-bs-target="#addCatForm">
            <i class="bi bi-plus-circle me-1"></i>Tambah Kategori
        </button>
    </x-slot:action>
</x-ui.page-header>

{{-- Form tambah kategori --}}
<div class="collapse mb-4 {{ $errors->any() && old('_action') === 'store' ? 'show' : '' }}" id="addCatForm">
    <div class="card">
        <div class="card-header-custom">
            <span class="title"><i class="bi bi-plus-circle me-2"></i>Tambah Kategori Baru</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.kategori.store') }}">
                @csrf
                <input type="hidden" name="_action" value="store">
                <div class="row g-3 align-items-end">
                    <div class="col-md-1">
                        <label class="form-label mb-1" class="t-caption">No</label>
                        <input type="number" name="nomor" class="form-control form-control-sm @error('nomor') is-invalid @enderror"
                               value="{{ old('nomor') }}" placeholder="11" min="1" max="99" required>
                        @error('nomor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1" class="t-caption">Ikon</label>
                        <input type="text" name="ikon" class="form-control form-control-sm @error('ikon') is-invalid @enderror"
                               value="{{ old('ikon') }}" placeholder="🎭" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1" class="t-caption">Nama Kategori</label>
                        <input type="text" name="nama" class="form-control form-control-sm @error('nama') is-invalid @enderror"
                               value="{{ old('nama') }}" placeholder="Contoh: Seni Pertunjukan" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1" class="t-caption">Deskripsi</label>
                        <input type="text" name="deskripsi" class="form-control form-control-sm @error('deskripsi') is-invalid @enderror"
                               value="{{ old('deskripsi') }}" placeholder="Penjelasan singkat...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-emas w-100">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Daftar Kategori --}}
<div class="card">
    <div class="card-header-custom">
        <span class="title"><i class="bi bi-tags me-2"></i>Daftar Kategori ({{ $kategori->count() }})</span>
    </div>
    <div class="card-body p-2">
        @foreach($kategori as $cat)
        <div class="d-flex align-items-center gap-3 p-3 border-bottom {{ $loop->last ? '' : '' }}"
             style="transition: background 0.15s;">
            {{-- Nomor & Ikon --}}
            <div class="d-flex align-items-center gap-2 flex-shrink-0" style="width:80px;">
                <span style="font-size:1.4rem;">{{ $cat->ikon }}</span>
                <span class="badge bg-secondary">{{ $cat->nomor }}</span>
            </div>

            {{-- Form edit inline --}}
            <form method="POST" action="{{ route('admin.kategori.update', $cat) }}" class="row g-2 flex-grow-1 align-items-center">
                @csrf @method('PUT')
                <div class="col-md-3">
                    <input type="number" name="nomor" value="{{ old("nomor.{$cat->id}", $cat->nomor) }}"
                           class="form-control form-control-sm" min="1" max="99" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="nama" value="{{ old("nama.{$cat->id}", $cat->nama) }}"
                           class="form-control form-control-sm" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="deskripsi" value="{{ old("deskripsi.{$cat->id}", $cat->deskripsi) }}"
                           class="form-control form-control-sm" placeholder="Deskripsi...">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Simpan">
                        <i class="bi bi-check-lg"></i></button>
            </form>

                    {{-- Hapus --}}
                    <form method="POST" action="{{ route('admin.kategori.destroy', $cat) }}"
                          onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Kategori',text:'Hapus kategori {{ $cat->nama }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"
                                @if($cat->laporans_count > 0) disabled @endif>
                            <i class="bi bi-trash"></i></button>
                    </form>
                </div>
        </div>
        @endforeach
    </div>
</div>

<p class="text-muted mt-3" class="t-caption">
    <i class="bi bi-info-circle me-1"></i>
    Kategori yang sudah digunakan oleh laporan OPK tidak dapat dihapus. Ubah nomor untuk mengatur urutan tampilan.
</p>
@endsection
