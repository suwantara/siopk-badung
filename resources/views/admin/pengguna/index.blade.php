@extends('layouts.app')
@section('title','Manajemen Pengguna')
@section('page-title','Manajemen Pengguna')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">Pengguna Sistem</h1>
        <p class="text-muted mb-0" style="font-size:0.82rem;">Kelola akun petugas Dinas Kebudayaan</p>
    </div>
    <a href="{{ route('admin.pengguna.create') }}" class="btn btn-emas btn-sm">
        <i class="bi bi-person-plus me-1"></i>Tambah Pengguna
    </a>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                       value="{{ request('search') }}" placeholder="Cari nama atau email...">
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">Semua Role</option>
                    @foreach(['superadmin','admin','verifikator','petugas'] as $r)
                    <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-emas me-1">Filter</button>
                <a href="{{ route('admin.pengguna.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0 table-responsive-si">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;">Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Instansi</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr style="{{ !$user->is_active ? 'opacity:0.55;' : '' }}">
                    <td style="padding-left:1.25rem;">
                        <div style="font-weight:600;font-size:0.88rem;">{{ $user->name }}</div>
                        @if($user->nip)<div style="font-size:0.7rem;color:var(--abu);">NIP: {{ $user->nip }}</div>@endif
                    </td>
                    <td style="font-size:0.82rem;">{{ $user->email }}</td>
                    <td>
                        @php
                            $roleColor = match($user->role) {
                                'superadmin' => ['bg'=>'var(--tanah)','color'=>'var(--emas-muda)'],
                                'admin'      => ['bg'=>'rgba(200,146,42,0.15)','color'=>'var(--emas-gelap)'],
                                'verifikator'=> ['bg'=>'rgba(45,90,39,0.12)','color'=>'var(--hijau)'],
                                default      => ['bg'=>'rgba(107,114,128,0.1)','color'=>'#4b5563'],
                            };
                        @endphp
                        <span style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }};padding:2px 10px;border-radius:10px;font-size:0.7rem;font-weight:600;">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--abu-gelap);">{{ $user->instansi ?? '—' }}</td>
                    <td>
                        @if($user->is_active)
                            <span style="color:var(--hijau);font-size:0.75rem;font-weight:600;"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Aktif</span>
                        @else
                            <span style="color:var(--abu);font-size:0.75rem;"><i class="bi bi-circle me-1" style="font-size:0.5rem;"></i>Nonaktif</span>
                        @endif
                    </td>
                    <td style="font-size:0.78rem;color:var(--abu);">{{ $user->created_at->isoFormat('D MMM Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @if(auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && !$user->isSuperAdmin()))
                            <a href="{{ route('admin.pengguna.edit', $user) }}"
                               class="btn btn-sm py-0 px-2" style="background:rgba(200,146,42,0.1);color:var(--emas-gelap);border:1px solid rgba(200,146,42,0.2);" title="Edit">
                                <i class="bi bi-pencil" style="font-size:0.75rem;"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.pengguna.toggle', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm py-0 px-2 btn-outline-secondary" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="bi bi-{{ $user->is_active ? 'toggle-on' : 'toggle-off' }}" style="font-size:0.75rem;"></i>
                                </button>
                            </form>
                            @if(!$user->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.pengguna.destroy', $user) }}"
                                  onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Pengguna',text:'Hapus pengguna {{ $user->name }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm py-0 px-2 btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash" style="font-size:0.75rem;"></i>
                                </button>
                            </form>
                            @endif
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white" style="border-top:1px solid var(--garis);">{{ $users->links() }}</div>
    @endif
</div>
@endsection
