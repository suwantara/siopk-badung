@extends('layouts.app')
@section('title','Manajemen Wilayah')
@section('page-title','Manajemen Wilayah')

@push('styles')
<style>
    /* Override Bootstrap list-group active agar sesuai design system */
    .list-group-kec .list-group-item {
        border-left: 3px solid transparent;
        transition: all 0.15s;
    }
    .list-group-kec .list-group-item:hover {
        border-left-color: rgba(200,146,42,0.3);
    }
    .list-group-kec .list-group-item.active {
        background: var(--tanah);
        border-color: var(--tanah);
        border-left-color: var(--emas);
        color: var(--krem);
    }
    .list-group-kec .list-group-item.active strong {
        color: var(--emas-muda);
    }
    .list-group-kec .list-group-item.active small {
        color: rgba(247,241,232,0.6);
    }
    .list-group-kec .list-group-item.active .text-muted {
        color: rgba(247,241,232,0.5) !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">Manajemen Wilayah</h1>
        <p class="text-muted mb-0" style="font-size:0.82rem;">Kelola kecamatan, desa dinas, dan desa adat di Kabupaten Badung</p>
    </div>
</div>

<div class="row g-4">
    {{-- Sidebar: Daftar Kecamatan --}}
    <div class="col-12 col-lg-4 mb-4 mb-lg-0">
        <div class="card">
            <div class="card-header-custom">
                <span class="title"><i class="bi bi-geo-alt me-2"></i>Kecamatan</span>
                <span class="badge bg-secondary">{{ $kecamatans->count() }}</span>
            </div>
            <div class="card-body p-2">
                <div class="list-group list-group-flush list-group-kec">
                    @foreach($kecamatans as $kec)
                    <a href="?kecamatan_id={{ $kec->id }}"
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3
                              {{ $selectedId == $kec->id ? 'active' : '' }}">
                        <div>
                            <strong style="font-size:0.85rem;">{{ $kec->nama }}</strong>
                            <small class="d-block text-muted" style="font-size:0.7rem;">Kode: {{ $kec->kode }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            <small class="text-muted" style="font-size:0.65rem;">
                                {{ $kec->desa_dinas_count ?? 0 }} <i class="bi bi-building"></i>
                            </small>
                            <small class="text-muted" style="font-size:0.65rem;">
                                {{ $kec->desa_adats_count ?? 0 }} <i class="bi bi-house"></i>
                            </small>
                        </div>
                    </a>
                    @endforeach
                </div>

                {{-- Tambah Kecamatan --}}
                <div class="p-3 border-top mt-2">
                    <button class="btn btn-sm btn-emas w-100" data-bs-toggle="collapse" data-bs-target="#addKecForm">
                        <i class="bi bi-plus-circle me-1"></i>Tambah Kecamatan
                    </button>
                    <form method="POST" action="{{ route('admin.wilayah.kecamatan.store') }}"
                          class="collapse mt-3 {{ $errors->has('kec_nama') || $errors->has('kode') ? 'show' : '' }}"
                          id="addKecForm">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label mb-1" style="font-size:0.72rem;">Nama Kecamatan</label>
                            <input type="text" name="nama" class="form-control form-control-sm @error('nama') is-invalid @enderror"
                                   value="{{ old('nama') }}" placeholder="Contoh: Kuta Utara">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label mb-1" style="font-size:0.72rem;">Kode Wilayah</label>
                            <input type="text" name="kode" class="form-control form-control-sm @error('kode') is-invalid @enderror"
                                   value="{{ old('kode') }}" placeholder="Contoh: 5103050">
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-sm btn-emas w-100">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail Kecamatan --}}
    <div class="col-12 col-lg-8">
        @if($selectedKec)
            {{-- Edit Kecamatan --}}
            <div class="card mb-4">
                <div class="card-header-custom">
                    <span class="title"><i class="bi bi-pencil me-2"></i>Edit Kecamatan: {{ $selectedKec->nama }}</span>
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editKecForm">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                </div>
                <div class="collapse {{ $errors->has('kec_nama') || $errors->has('kec_kode') ? 'show' : '' }}"
                     id="editKecForm">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.wilayah.kecamatan.update', $selectedKec) }}">
                            @csrf @method('PUT')
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label mb-1" style="font-size:0.72rem;">Nama</label>
                                    <input type="text" name="nama" class="form-control form-control-sm @error('kec_nama') is-invalid @enderror"
                                           value="{{ old('nama', $selectedKec->nama) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label mb-1" style="font-size:0.72rem;">Kode</label>
                                    <input type="text" name="kode" class="form-control form-control-sm @error('kec_kode') is-invalid @enderror"
                                           value="{{ old('kode', $selectedKec->kode) }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-emas">
                                        <i class="bi bi-check-lg"></i> Simpan</button>
                                </div>
                                <div class="col-md-2 text-end">
                                    <form method="POST" action="{{ route('admin.wilayah.kecamatan.destroy', $selectedKec) }}"
                                          onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Kecamatan',text:'Hapus kecamatan {{ $selectedKec->nama }} dan semua data terkait?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Desa Dinas --}}
            <div class="card mb-4">
                <div class="card-header-custom">
                    <span class="title"><i class="bi bi-building me-2"></i>Desa Dinas</span>
                    <span class="badge bg-secondary">{{ $desaDinas->count() }}</span>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-hover mb-0">
                        @foreach($desaDinas as $d)
                        <tr>
                            <td style="width:40px;text-align:center;">
                                <i class="bi bi-building text-muted"></i>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.wilayah.desa-dinas.update', $d) }}"
                                      class="input-group input-group-sm">
                                    @csrf @method('PUT')
                                    <input type="text" name="nama" value="{{ $d->nama }}" class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Simpan">
                                        <i class="bi bi-check-lg"></i></button>
                                </form>
                            </td>
                            <td style="width:50px;">
                                    <form method="POST" action="{{ route('admin.wilayah.desa-dinas.destroy', $d) }}"
                                          onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Desa Dinas',text:'Hapus {{ $d->nama }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger" title="Hapus">
                                        <i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </table>

                    {{-- Tambah Desa Dinas --}}
                    <div class="p-2 border-top">
                        <form method="POST" action="{{ route('admin.wilayah.desa-dinas.store') }}"
                              class="input-group input-group-sm">
                            @csrf
                            <input type="hidden" name="kecamatan_id" value="{{ $selectedKec->id }}">
                            <input type="text" name="nama" class="form-control form-control-sm"
                                   placeholder="Nama desa dinas baru..." required>
                            <button type="submit" class="btn btn-sm btn-emas">
                                <i class="bi bi-plus-circle"></i> Tambah</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Desa Adat --}}
            <div class="card">
                <div class="card-header-custom">
                    <span class="title"><i class="bi bi-house-door me-2"></i>Desa Adat</span>
                    <span class="badge bg-secondary">{{ $desaAdats->count() }}</span>
                </div>
                <div class="card-body p-2">
                    <table class="table table-sm table-hover mb-0">
                        @foreach($desaAdats as $a)
                        <tr>
                            <td style="width:40px;text-align:center;">
                                <i class="bi bi-house-door text-muted"></i>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.wilayah.desa-adat.update', $a) }}"
                                      class="input-group input-group-sm">
                                    @csrf @method('PUT')
                                    <input type="text" name="nama" value="{{ $a->nama }}" class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Simpan">
                                        <i class="bi bi-check-lg"></i></button>
                                </form>
                            </td>
                            <td style="width:50px;">
                                    <form method="POST" action="{{ route('admin.wilayah.desa-adat.destroy', $a) }}"
                                          onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Desa Adat',text:'Hapus {{ $a->nama }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger" title="Hapus">
                                        <i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </table>

                    {{-- Tambah Desa Adat --}}
                    <div class="p-2 border-top">
                        <form method="POST" action="{{ route('admin.wilayah.desa-adat.store') }}"
                              class="input-group input-group-sm">
                            @csrf
                            <input type="hidden" name="kecamatan_id" value="{{ $selectedKec->id }}">
                            <input type="text" name="nama" class="form-control form-control-sm"
                                   placeholder="Nama desa adat baru..." required>
                            <button type="submit" class="btn btn-sm btn-emas">
                                <i class="bi bi-plus-circle"></i> Tambah</button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-geo-alt" style="font-size:3rem;"></i>
                    <p class="mt-3">Pilih kecamatan dari daftar di samping untuk mengelola wilayah.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
