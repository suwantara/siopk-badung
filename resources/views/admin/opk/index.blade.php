@extends('layouts.app')
@section('title', 'Data OPK')
@section('page-title', 'Data OPK Resmi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">
            Data OPK Kabupaten Badung
        </h1>
        <p class="text-muted mb-0" style="font-size:0.82rem;">
            {{ $laporans->total() }} objek terdaftar · status: disetujui & terverifikasi
        </p>
    </div>
    <a href="{{ route('publik.lapor.index') }}" target="_blank" class="btn btn-emas btn-sm">
        <i class="bi bi-plus-circle me-1"></i>Tambah OPK
    </a>
</div>

{{-- Filter --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Cari Nama OPK</label>
                <input type="text" name="search" class="form-control form-control-sm"
                       value="{{ request('search') }}" placeholder="Nama objek budaya...">
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Jenis OPK</label>
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
                <label class="form-label" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Kecamatan</label>
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
                <label class="form-label" style="font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Kondisi</label>
                <select name="kondisi" class="form-select form-select-sm">
                    <option value="">Semua Kondisi</option>
                    <option value="kritis"  {{ request('kondisi') === 'kritis'  ? 'selected' : '' }}>Kritis</option>
                    <option value="waspada" {{ request('kondisi') === 'waspada' ? 'selected' : '' }}>Waspada</option>
                    <option value="baik"    {{ request('kondisi') === 'baik'    ? 'selected' : '' }}>Baik</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-emas me-1">
                    <i class="bi bi-search me-1"></i>Filter
                </button>
                <a href="{{ route('admin.opk.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card">
    <div class="card-body p-0">
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
                    <td style="padding-left:1.25rem;color:var(--abu);font-size:0.75rem;">
                        {{ $laporans->firstItem() + $i }}
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:0.88rem;">
                            <a href="{{ route('admin.opk.show', $opk) }}" style="color:var(--tanah);text-decoration:none;">
                                {{ $opk->nama_opk }}
                            </a>
                        </div>
                        <div style="font-size:0.7rem;color:var(--abu);">{{ $opk->kode_laporan }}</div>
                    </td>
                    <td>
                        <span style="background:rgba(200,146,42,0.1);color:var(--emas-gelap);padding:2px 8px;border-radius:2px;font-size:0.7rem;font-weight:500;white-space:nowrap;">
                            {{ $opk->kategori?->ikon }} {{ $opk->kategori?->nama }}
                        </span>
                    </td>
                    <td style="font-size:0.82rem;">{{ $opk->kecamatan?->nama }}</td>
                    <td style="font-size:0.78rem;color:var(--abu-gelap);">{{ Str::limit($opk->nama_desa_adat, 25) }}</td>
                    <td>
                        <span class="badge badge-{{ $opk->kondisi }} rounded-pill px-2" style="font-size:0.68rem;">
                            {{ ucfirst($opk->kondisi) }}
                        </span>
                    </td>
                    <td>
                        @if($opk->ai_urgency_score)
                            <span style="font-family:'Courier New',monospace;font-size:0.82rem;font-weight:700;
                                color:{{ $opk->kondisi === 'kritis' ? 'var(--merah)' : ($opk->kondisi === 'waspada' ? 'var(--kuning)' : 'var(--hijau)') }}">
                                {{ number_format($opk->ai_urgency_score, 1) }}
                            </span>
                        @else
                            <span style="color:var(--abu);font-size:0.75rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($opk->latitude && $opk->longitude)
                            <i class="bi bi-geo-alt-fill" style="color:var(--hijau);font-size:0.85rem;" title="{{ $opk->latitude }}, {{ $opk->longitude }}"></i>
                        @else
                            <i class="bi bi-geo-alt" style="color:var(--abu);font-size:0.85rem;"></i>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.opk.show', $opk) }}"
                               class="btn btn-sm btn-outline-secondary py-0 px-2" title="Detail">
                                <i class="bi bi-eye" style="font-size:0.75rem;"></i>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.opk.edit', $opk) }}"
                               class="btn btn-sm py-0 px-2"
                               style="background:rgba(200,146,42,0.1);color:var(--emas-gelap);border:1px solid rgba(200,146,42,0.2);" title="Edit">
                                <i class="bi bi-pencil" style="font-size:0.75rem;"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                        Belum ada data OPK yang sesuai filter.
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
