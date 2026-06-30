@extends('layouts.app')
@section('title','Arsip OPK')
@section('page-title','Arsip OPK')

@section('content')
<x-ui.page-header title="Arsip OPK" subtitle="Data yang diarsipkan — tidak tampil di peta publik, namun masih tersimpan di database">
    <x-slot:action>
        <a href="{{ route('admin.opk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Data OPK
        </a>
    </x-slot:action>
</x-ui.page-header>

<div class="card">
    <div class="card-body p-0 table-responsive-si">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;">Nama OPK</th>
                    <th>Jenis</th>
                    <th>Kecamatan</th>
                    <th>Kondisi</th>
                    <th>Diarsipkan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporans as $opk)
                <tr style="opacity:0.7;">
                    <td style="padding-left:1.25rem;">
                        <div style="font-weight:600" class="t-body-lg">{{ $opk->nama_opk }}</div>
                        <div style="color:var(--abu)" class="t-caption">{{ $opk->kode_laporan }}</div>
                    </td>
                    <td>
                        <x-ui.badge-kategori :ikon="$opk->kategori?->ikon" :nama="$opk->kategori?->nama" />
                    </td>
                    <td class="t-body">{{ $opk->kecamatan?->nama }}</td>
                    <td>
                        <x-ui.badge-kondisi :kondisi="$opk->kondisi" />
                    </td>
                    <td style="color:var(--abu)" class="t-body">
                        {{ $opk->deleted_at?->isoFormat('D MMM Y, HH:mm') }}
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.opk.restore', $opk->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                        title="Pulihkan OPK ini"
                                        onclick="event.preventDefault(); swalKonfirmasi({title:'Pulihkan OPK',text:'Pulihkan OPK ini ke daftar aktif?',icon:'question',confirmText:'Pulihkan',confirmColor:'var(--hijau)',onConfirm:()=>this.closest('form').submit()})">
                                    <i class="bi bi-arrow-counterclockwise" class="t-caption"></i> Pulihkan
                                </button>
                            </form>
                            @if(auth()->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.opk.force-delete', $opk->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        title="Hapus permanen OPK ini"
                                        onclick="event.preventDefault(); swalKonfirmasi({title:'Hapus Permanen',text:'Hapus PERMANEN OPK ini? Tindakan ini tidak dapat dibatalkan dan semua data, foto, serta dokumen terkait akan dihapus dari database.',icon:'error',confirmText:'Hapus Permanen',confirmColor:'#dc3545',onConfirm:()=>this.closest('form').submit()})">
                                    <i class="bi bi-trash" class="t-caption"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-ui.empty-state colspan="6" icon="bi-archive" message="Tidak ada OPK yang diarsipkan." />
                @endforelse
            </tbody>
        </table>
    </div>
    @if($laporans->hasPages())
    <div class="card-footer bg-white" style="border-top:1px solid var(--garis);">
        {{ $laporans->links() }}
    </div>
    @endif
</div>
@endsection
