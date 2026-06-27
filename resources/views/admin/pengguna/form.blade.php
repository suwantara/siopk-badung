@extends('layouts.app')
@section('title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')
@section('page-title', $user ? 'Edit Pengguna' : 'Tambah Pengguna')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.pengguna.index') }}" style="font-size:0.8rem;color:var(--emas);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Pengguna
    </a>
</div>

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
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">
                                Nama Lengkap <span style="color:var(--merah)">*</span>
                            </label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user?->name) }}" placeholder="Nama sesuai identitas">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">
                                Email <span style="color:var(--merah)">*</span>
                            </label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user?->email) }}" placeholder="email@instansi.go.id">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">
                                Password {{ $user ? '(kosongkan jika tidak diubah)' : '*' }}
                            </label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 8 karakter" {{ !$user ? 'required' : '' }}>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">
                                Konfirmasi Password
                            </label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">
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
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">NIP</label>
                            <input type="text" name="nip" class="form-control"
                                   value="{{ old('nip', $user?->nip) }}" placeholder="Nomor Induk Pegawai">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">No. HP / WhatsApp</label>
                            <input type="text" name="no_hp" class="form-control"
                                   value="{{ old('no_hp', $user?->no_hp) }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-6">
                            @if($user)
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Status Akun</label>
                            <div class="mt-1">
                                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.85rem;">
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
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Instansi</label>
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
                    ['Admin','Edit OPK, verifikasi, kelola pengguna (kecuali superadmin)','var(--emas-gelap)','rgba(200,146,42,0.1)'],
                    ['Verifikator','Verifikasi laporan, akses AI chat & ringkasan','var(--hijau)','rgba(45,90,39,0.1)'],
                    ['Petugas','Lihat data & dashboard saja, tidak bisa verifikasi','#4b5563','rgba(107,114,128,0.1)'],
                ] as [$role, $desc, $color, $bg])
                <div style="padding:10px 1rem;border-bottom:1px solid var(--garis-terang);display:flex;gap:10px;align-items:flex-start;">
                    <span style="background:{{ $bg }};color:{{ $color }};padding:2px 10px;border-radius:10px;font-size:0.7rem;font-weight:600;flex-shrink:0;margin-top:2px;">{{ $role }}</span>
                    <span style="font-size:0.78rem;color:var(--abu-gelap);">{{ $desc }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
