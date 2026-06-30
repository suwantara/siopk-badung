@extends('layouts.app')
@section('title', 'Data OPK')
@section('page-title', 'Data OPK Resmi')

@section('content')
<x-ui.page-header title="Data OPK Kabupaten Badung" :subtitle="$laporans->total() . ' objek terdaftar · status: disetujui &amp; terverifikasi'" action-label="Tambah OPK" action-url="{{ route('publik.lapor.index') }}" action-target="_blank" />

<x-ui.filter-bar reset-url="{{ route('admin.opk.index') }}">
    <div class="col-6 col-md-3">
        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Cari Nama OPK</label>
        <input type="text" name="search" class="form-control form-control-sm"
               value="{{ request('search') }}" placeholder="Nama objek budaya...">
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Jenis OPK</label>
        <select name="kategori_id" class="form-select form-select-sm">
            <option value="">Semua Jenis</option>
            @foreach($kategori as $kat)
                <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                    {{ $kat->ikon }} {{ $kat->nama }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Kecamatan</label>
        <select name="kecamatan_id" class="form-select form-select-sm">
            <option value="">Semua Kecamatan</option>
            @foreach($kecamatans as $kec)
                <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>
                    {{ $kec->nama }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em" class="t-caption">Kondisi</label>
        <select name="kondisi" class="form-select form-select-sm">
            <option value="">Semua Kondisi</option>
            <option value="kritis"  {{ request('kondisi') === 'kritis'  ? 'selected' : '' }}>Kritis</option>
            <option value="waspada" {{ request('kondisi') === 'waspada' ? 'selected' : '' }}>Waspada</option>
            <option value="baik"    {{ request('kondisi') === 'baik'    ? 'selected' : '' }}>Baik</option>
        </select>
    </div>
</x-ui.filter-bar>

{{-- Tabel --}}
<div class="card">
    <div class="card-body p-0 table-responsive-si">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;width:40px;">#</th>
                    <th>Nama OPK</th>
                    <th>Jenis</th>
                    <th>Kecamatan</th>
                    <th>Desa Adat</th>
                    <th>Kondisi</th>
                    <th>AI Score</th>
                    <th>GPS</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporans as $i => $opk)
                <tr>
                    <td style="padding-left:1.25rem;color:var(--abu);" class="t-caption">
                        {{ $laporans->firstItem() + $i }}
                    </td>
                    <td>
                        <div style="font-weight:600" class="t-body-lg">
                            <a href="{{ route('admin.opk.show', $opk) }}" style="color:var(--tanah);text-decoration:none;">
                                {{ $opk->nama_opk }}
                            </a>
                        </div>
                        <div style="color:var(--abu)" class="t-caption">{{ $opk->kode_laporan }}</div>
                    </td>
                    <td><x-ui.badge-kategori :ikon="$opk->kategori?->ikon" :nama="$opk->kategori?->nama" /></td>
                    <td class="t-body">{{ $opk->kecamatan?->nama }}</td>
                    <td style="color:var(--abu-gelap)" class="t-body">{{ Str::limit($opk->nama_desa_adat, 25) }}</td>
                    <td><x-ui.badge-kondisi :kondisi="$opk->kondisi" /></td>
                    <td>
                        <x-ui.ai-score :score="$opk->ai_urgency_score" :kondisi="$opk->kondisi" />
                    </td>
                    <td>
                        @if($opk->latitude && $opk->longitude)
                            <i class="bi bi-geo-alt-fill" style="color:var(--hijau)" class="t-body" title="{{ $opk->latitude }}, {{ $opk->longitude }}"></i>
                        @else
                            <i class="bi bi-geo-alt" style="color:var(--abu)" class="t-body"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.opk.show', $opk) }}" class="btn-icon" title="Detail">
                                <i class="bi bi-eye" class="t-caption"></i>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.opk.edit', $opk) }}" class="btn-icon" title="Edit" style="color:var(--emas);border-color:var(--border-emas);">
                                <i class="bi bi-pencil" class="t-caption"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <x-ui.empty-state colspan="9" message="Belum ada data OPK yang sesuai filter." />
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
