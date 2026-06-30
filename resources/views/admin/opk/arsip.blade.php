@extends('layouts.app')
@section('title','Arsip OPK')
@section('page-title','Arsip OPK')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">Arsip OPK</h1>
        <p class="text-muted mb-0" style="font-size:0.82rem;">
            Data yang diarsipkan — tidak tampil di peta publik, namun masih tersimpan di database
        </p>
    </div>
    <a href="{{ route('admin.opk.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Data OPK
    </a>
</div>

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
                        <div style="font-weight:600;font-size:0.88rem;">{{ $opk->nama_opk }}</div>
                        <div style="font-size:0.7rem;color:var(--abu);">{{ $opk->kode_laporan }}</div>
                    </td>
                    <td>
                        <span style="background:rgba(200,146,42,0.1);color:var(--emas-gelap);padding:2px 8px;border-radius:2px;font-size:0.7rem;font-weight:500;">
                            {{ $opk->kategori?->ikon }} {{ $opk->kategori?->nama }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem;">{{ $opk->kecamatan?->nama }}</td>
                    <td>
                        <span class="badge badge-{{ $opk->kondisi }} rounded-pill px-2" style="font-size:0.68rem;">
                            {{ ucfirst($opk->kondisi) }}
                        </span>
                    </td>
                    <td style="font-size:0.78rem;color:var(--abu);">
                        {{ $opk->deleted_at?->isoFormat('D MMM Y, HH:mm') }}
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('admin.opk.restore', $opk->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2"
                                        title="Pulihkan OPK ini"
                                        onclick="event.preventDefault(); swalKonfirmasi({title:'Pulihkan OPK',text:'Pulihkan OPK ini ke daftar aktif?',icon:'question',confirmText:'Pulihkan',confirmColor:'var(--hijau)',onConfirm:()=>this.closest('form').submit()})">
                                    <i class="bi bi-arrow-counterclockwise" style="font-size:0.75rem;"></i> Pulihkan
                                </button>
                            </form>
                            @if(auth()->user()->isSuperAdmin())
                            <form method="POST" action="{{ route('admin.opk.force-delete', $opk->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                        title="Hapus permanen OPK ini"
                                        onclick="event.preventDefault(); swalKonfirmasi({title:'Hapus Permanen',text:'Hapus PERMANEN OPK ini? Tindakan ini tidak dapat dibatalkan dan semua data, foto, serta dokumen terkait akan dihapus dari database.',icon:'error',confirmText:'Hapus Permanen',confirmColor:'#dc3545',onConfirm:()=>this.closest('form').submit()})">
                                    <i class="bi bi-trash" style="font-size:0.75rem;"></i> Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-archive" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        Tidak ada OPK yang diarsipkan.
                    </td>
                </tr>
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
