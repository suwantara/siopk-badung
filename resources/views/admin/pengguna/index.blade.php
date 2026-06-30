@extends('layouts.app')
@section('title','Manajemen Pengguna')
@section('page-title','Manajemen Pengguna')

@section('content')
<x-ui.page-header title="Pengguna Sistem" subtitle="Kelola akun petugas Dinas Kebudayaan" action-label="Tambah Pengguna" action-url="{{ route('admin.pengguna.create') }}" action-icon="bi-person-plus" />

<x-ui.filter-bar reset-url="{{ route('admin.pengguna.index') }}">
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
</x-ui.filter-bar>

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
                        <div style="font-weight:600" class="t-body-lg">{{ $user->name }}</div>
                        @if($user->nip)<div style="color:var(--abu)" class="t-caption">NIP: {{ $user->nip }}</div>@endif
                    </td>
                    <td class="t-body">{{ $user->email }}</td>
                    <td>
                        @php
                            $roleColor = match($user->role) {
                                'superadmin' => ['bg'=>'var(--tanah)','color'=>'var(--emas-muda)'],
                                'admin'      => ['bg'=>'var(--surface-emas-hover)','color'=>'var(--emas-gelap)'],
                                'verifikator'=> ['bg'=>'rgba(45,90,39,0.12)','color'=>'var(--hijau)'],
                                default      => ['bg'=>'rgba(107,114,128,0.1)','color'=>'#4b5563'],
                            };
                        @endphp
                        <span style="background:{{ $roleColor['bg'] }};color:{{ $roleColor['color'] }};padding:2px 10px;border-radius:10px;font-weight:600" class="t-caption">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td style="color:var(--abu-gelap)" class="t-body">{{ $user->instansi ?? '—' }}</td>
                    <td>
                        @if($user->is_active)
                            <span style="color:var(--hijau);font-weight:600;" class="t-caption"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Aktif</span>
                        @else
                            <span style="color:var(--abu);" class="t-caption"><i class="bi bi-circle me-1" style="font-size:0.5rem;"></i>Nonaktif</span>
                        @endif
                    </td>
                    <td style="color:var(--abu)" class="t-body">{{ $user->created_at->isoFormat('D MMM Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            @if(auth()->user()->isSuperAdmin() || (auth()->user()->isAdmin() && !$user->isSuperAdmin()))
                            <a href="{{ route('admin.pengguna.edit', $user) }}"
                               class="btn btn-sm py-0 px-2" style="background:var(--surface-emas);color:var(--emas-gelap);border:1px solid var(--border-emas);" title="Edit">
                                <i class="bi bi-pencil" class="t-caption"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.pengguna.toggle', $user) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm py-0 px-2 btn-outline-secondary" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="bi bi-{{ $user->is_active ? 'toggle-on' : 'toggle-off' }}" class="t-caption"></i>
                                </button>
                            </form>
                            @if(!$user->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.pengguna.destroy', $user) }}"
                                  onsubmit="event.preventDefault(); swalKonfirmasi({title:'Hapus Pengguna',text:'Hapus pengguna {{ $user->name }}?',icon:'warning',confirmText:'Hapus',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm py-0 px-2 btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash" class="t-caption"></i>
                                </button>
                            </form>
                            @endif
                            @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-ui.empty-state colspan="7" message="Belum ada pengguna." />
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white" style="border-top:1px solid var(--garis);">{{ $users->links() }}</div>
    @endif
</div>
@endsection
