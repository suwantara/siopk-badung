@extends('layouts.publik')

@php
    $kondisiIkon = ['baik'=>'<i class="bi bi-check-circle-fill" style="color:var(--hijau);"></i>','waspada'=>'<i class="bi bi-exclamation-triangle-fill" style="color:var(--kuning);"></i>','kritis'=>'<i class="bi bi-exclamation-circle-fill" style="color:var(--merah);"></i>'];
@endphp

@section('title', 'Daftar OPK — SIOPK Badung')

@push('styles')
<style>
    .container-daftar { max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem; }
    .daftar-header h1 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 700; margin-bottom: 4px; }
    .daftar-header p  { color: var(--abu-gelap); font-size: 0.85rem; }

    .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 1.2rem; }
    .filter-bar select, .filter-bar input { border: 1px solid var(--garis); border-radius: 3px; font-size: 0.8rem; padding: 6px 10px; background: white; color: var(--tanah); }
    .filter-bar select:focus, .filter-bar input:focus { border-color: var(--emas); outline: none; box-shadow: 0 0 0 2px rgba(200,146,42,0.15); }

    .opk-list-item { display: flex; gap: 16px; align-items: center; padding: 14px 18px; background: white; border: 1px solid var(--garis); border-radius: 4px; margin-bottom: 10px; text-decoration: none; transition: all 0.15s; }
    .opk-list-item:hover { border-color: var(--emas); box-shadow: 0 2px 8px rgba(0,0,0,0.04); text-decoration: none; }
    .opk-list-thumb { width: 72px; height: 56px; border-radius: 3px; overflow: hidden; flex-shrink: 0; background: var(--placeholder); display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .opk-list-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .opk-list-info { flex: 1; min-width: 0; }
    .opk-list-nama { font-family: 'Cormorant Garamond', serif; font-size: 1.05rem; font-weight: 700; color: var(--tanah); line-height: 1.3; }
    .opk-list-meta { font-size: 0.75rem; color: var(--abu-gelap); margin-top: 3px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .opk-list-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 8px; font-weight: 600; white-space: nowrap; }
    .badge-kritis  { background: rgba(192,57,43,0.1); color: var(--merah); }
    .badge-waspada { background: rgba(212,160,23,0.1); color: var(--kuning); }
    .badge-baik    { background: rgba(45,90,39,0.1); color: var(--hijau); }
    .opk-list-arrow { color: var(--abu); font-size: 1.1rem; flex-shrink: 0; }

    .result-count { font-size: 0.78rem; color: var(--abu); margin-bottom: 0.75rem; }

    @media (max-width: 576px) {
        .opk-list-item { padding: 10px 12px; }
        .opk-list-thumb { width: 56px; height: 44px; font-size: 1.2rem; }
        .filter-bar { flex-direction: column; align-items: stretch; }
    }

    .pagination { gap: 4px; }
    .page-link {
        color: var(--tanah);
        background: white;
        border: 1px solid var(--garis);
        border-radius: 3px !important;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 500;
        transition: all 0.15s;
    }
    .page-link:hover {
        color: var(--tanah);
        background: rgba(var(--emas-rgb), 0.08);
        border-color: var(--emas);
    }
    .page-item.active .page-link {
        background: var(--emas);
        border-color: var(--emas);
        color: var(--tanah);
        font-weight: 600;
    }
    .page-item.disabled .page-link {
        color: var(--abu);
        background: var(--input-bg);
        border-color: var(--garis);
        pointer-events: none;
    }
    .page-item:first-child .page-link,
    .page-item:last-child .page-link {
        padding: 6px 14px;
    }
</style>
@endpush

@section('content')
<div class="container-daftar">

    <div class="daftar-header mb-4">
        <h1>Daftar Objek Pemajuan Kebudayaan</h1>
        <p>Seluruh OPK yang telah terverifikasi di Kabupaten Badung</p>
    </div>

    <form method="GET" action="{{ route('publik.daftar-opk') }}" class="filter-bar">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama, deskripsi, atau desa adat..." style="flex:1;min-width:200px;">
        <select name="kategori">
            <option value="">Semua Jenis OPK</option>
            @foreach($kategori as $kat)
            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
                {{ $kat->ikon }} {{ $kat->nama }}
            </option>
            @endforeach
        </select>
        <select name="kecamatan">
            <option value="">Semua Kecamatan</option>
            @foreach($kecamatans as $kec)
            <option value="{{ $kec->id }}" {{ request('kecamatan') == $kec->id ? 'selected' : '' }}>
                {{ $kec->nama }}
            </option>
            @endforeach
        </select>
        <select name="kondisi">
            <option value="">Semua Kondisi</option>
            <option value="baik" {{ request('kondisi') === 'baik' ? 'selected' : '' }}>Baik</option>
            <option value="waspada" {{ request('kondisi') === 'waspada' ? 'selected' : '' }}>Waspada</option>
            <option value="kritis" {{ request('kondisi') === 'kritis' ? 'selected' : '' }}>Kritis</option>
        </select>
        <select name="urut">
            <option value="terbaru" {{ request('urut','terbaru') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
            <option value="terlama" {{ request('urut') === 'terlama' ? 'selected' : '' }}>Terlama</option>
            <option value="nama" {{ request('urut') === 'nama' ? 'selected' : '' }}>Nama A-Z</option>
            <option value="kritis" {{ request('urut') === 'kritis' ? 'selected' : '' }}>Prioritas Kritis</option>
        </select>
        <button type="submit" class="btn btn-sm" style="background:var(--emas);color:var(--tanah);border:none;font-weight:600;padding:6px 14px;">
            <i class="bi bi-search"></i>
        </button>
        @if(request()->anyFilled(['cari','kategori','kecamatan','kondisi','urut']) && request('urut') !== 'terbaru')
        <a href="{{ route('publik.daftar-opk') }}" class="btn btn-sm" style="border:1px solid var(--garis);color:var(--abu-gelap);padding:6px 14px;text-decoration:none;">
            <i class="bi bi-x"></i> Reset
        </a>
        @endif
    </form>

    <div class="result-count">
        {{ $opks->total() }} OPK ditemukan
        @if(request()->anyFilled(['cari','kategori','kecamatan','kondisi']))
        @endif
    </div>

    @forelse($opks as $opk)
    <a href="{{ route('publik.opk.show', $opk) }}" class="opk-list-item">
        <div class="opk-list-thumb">
            @if($opk->fotoUtama)
                <img src="{{ asset('storage/'.$opk->fotoUtama->path) }}" alt="{{ $opk->nama_opk }}">
            @else
                {{ $opk->kategori?->ikon ?? '🏛️' }}
            @endif
        </div>
        <div class="opk-list-info">
            <div class="opk-list-nama">{{ $opk->nama_opk }}</div>
            <div class="opk-list-meta">
                <span>{{ $opk->kategori?->ikon }} {{ $opk->kategori?->nama }}</span>
                <span>· {{ $opk->kecamatan?->nama }}</span>
                <span>· {{ $opk->nama_desa_adat }}</span>
                <span class="opk-list-badge badge-{{ $opk->kondisi }}">{{ ucfirst($opk->kondisi) }}</span>
            </div>
        </div>
        <div class="opk-list-arrow"><i class="bi bi-chevron-right"></i></div>
    </a>
    @empty
    <div style="text-align:center;padding:3rem 1rem;background:white;border:1px solid var(--garis);border-radius:4px;">
        <i class="bi bi-inbox" style="font-size:2.5rem;color:var(--abu);display:block;margin-bottom:12px;"></i>
        <p style="font-size:0.9rem;color:var(--abu);margin:0;">Tidak ada OPK ditemukan dengan filter tersebut.</p>
    </div>
    @endforelse

    <div class="mt-3">
        {{ $opks->links() }}
    </div>
</div>
@endsection
