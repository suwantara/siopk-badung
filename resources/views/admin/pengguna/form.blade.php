@extends('layouts.app')
@section('title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<x-ui.back-link href="{{ route('admin.pengguna.index') }}" label="Kembali ke Daftar Pengguna" />

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header-custom">
                <span class="title">{{ $user ? 'Edit: '.$user->name : 'Tambah Pengguna Baru' }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ $user ? route('admin.pengguna.update', $user) : route('admin.pengguna.store') }}">
                    @csrf
                    @if($user) @method('PUT') @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">
                                Nama Lengkap <span style="color:var(--merah)">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user?->name) }}" placeholder="Nama sesuai identitas">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">
                                Email <span style="color:var(--merah)">*</span>
                            </label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user?->email) }}" placeholder="email@instansi.go.id">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">
                                Password {{ $user ? '(kosongkan jika tidak diubah)' : '*' }}
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter" {{ !$user ? 'required' : '' }}>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">
                                Konfirmasi Password
                            </label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">
                                Role <span style="color:var(--merah)">*</span>
                            </label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                @foreach(['superadmin'=>'Superadmin','admin'=>'Admin','verifikator'=>'Verifikator','petugas'=>'Petugas Lapangan'] as $val => $label)
                                <option value="{{ $val }}" {{ old('role', $user?->role) === $val ? 'selected' : '' }}
                                    {{ $val === 'superadmin' && !auth()->user()->isSuperAdmin() ? 'disabled' : '' }}>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">NIP</label>
                            <input type="text" name="nip" class="form-control"
                                   value="{{ old('nip', $user?->nip) }}" placeholder="Nomor Induk Pegawai">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">No. HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control"
                                   value="{{ old('no_hp', $user?->no_hp) }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            @if($user)
                            <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">Status Akun</label>
                            <div class="mt-1">
                                <label style="display:flex;align-items:center;cursor:pointer" class="gap-sm" class="t-body">
                                    <input type="checkbox" name="is_active" value="1"
                                           {{ old('is_active', $user?->is_active) ? 'checked' : '' }}
                                           style="accent-color:var(--emas);">
                                    Akun aktif
                                </label>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;" class="t-caption">Instansi</label>
                        <input type="text" name="instansi" class="form-control"
                               value="{{ old('instansi', $user?->instansi ?? 'Dinas Kebudayaan Kabupaten Badung') }}"
                               placeholder="Nama instansi/unit kerja">
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-emas px-4">
                            <i class="bi bi-check2 me-2"></i>{{ $user ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
                        </button>
                        <a href="{{ route('admin.pengguna.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header-custom"><span class="title">Keterangan Role</span></div>
            <div class="card-body p-0">
                @foreach([
                    ['Superadmin','Akses penuh termasuk kelola pengguna & arsip','var(--tanah)','var(--emas-muda)'],
                    ['Admin','Edit OPK, verifikasi, kelola pengguna (kecuali superadmin)','var(--emas-gelap)','var(--surface-emas)'],
                    ['Verifikator','Verifikasi laporan, akses AI chat & ringkasan','var(--hijau)','var(--surface-hijau)'],
                    ['Petugas','Lihat data & dashboard saja, tidak bisa verifikasi','#4b5563','rgba(107,114,128,0.1)'],
                ] as [$role, $desc, $color, $bg])
                <div style="padding:10px 1rem;border-bottom:1px solid var(--garis-terang);display:flex;align-items:flex-start" class="gap-sm">
                    <span style="background:{{ $bg }};color:{{ $color }};padding:2px 10px;border-radius:10px;font-weight:600;flex-shrink:0;margin-top:2px" class="t-caption">{{ $role }}</span>
                    <span style="color:var(--abu-gelap)" class="t-body">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
