@extends('layouts.publik')

@php
    $kondisiIkon = ['baik'=>'<i class="bi bi-check-circle-fill" style="color:var(--hijau);"></i>','waspada'=>'<i class="bi bi-exclamation-triangle-fill" style="color:var(--kuning);"></i>','kritis'=>'<i class="bi bi-exclamation-circle-fill" style="color:var(--merah);"></i>'];
@endphp

@section('title', 'Daftar OPK — SIOPK Badung')

@section('content')
<div class="container-daftar">

    <div class="daftar-header mb-4">
        <h1>Daftar Objek Pemajuan Kebudayaan</h1>
        <p>Seluruh OPK yang telah terverifikasi di Kabupaten Badung</p>
    </div>

    <div class="suggest-wrap">
        <i class="bi bi-search suggest-icon"></i>
        <input type="text" id="suggestDaftar" placeholder="Ketik nama OPK untuk cari cepat..." autocomplete="off">
        <div class="suggest-dropdown" id="suggestDaftarDropdown"></div>
    </div>

    <form method="GET" action="{{ route('publik.daftar-opk') }}" class="daftar-filter-bar">
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
        <p style="color:var(--abu);margin:0" class="t-body-lg">Tidak ada OPK ditemukan dengan filter tersebut.</p>
    </div>
    @endforelse

    <div class="mt-3">
        {{ $opks->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    const input = document.getElementById('suggestDaftar');
    const dropdown = document.getElementById('suggestDaftarDropdown');
    let suggestIndex = -1;
    let suggestTimeout;

    input.addEventListener('input', function() {
        clearTimeout(suggestTimeout);
        const q = this.value.trim();
        if (q.length >= 2) {
            suggestTimeout = setTimeout(() => fetchSuggest(q), 250);
        } else {
            dropdown.classList.remove('show');
            suggestIndex = -1;
        }
    });

    input.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.suggest-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); suggestIndex = Math.min(suggestIndex + 1, items.length - 1); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); suggestIndex = Math.max(suggestIndex - 1, 0); }
        else if (e.key === 'Enter')     { if (suggestIndex >= 0) { e.preventDefault(); items[suggestIndex].click(); } }
        else if (e.key === 'Escape')    { dropdown.classList.remove('show'); suggestIndex = -1; return; }
        items.forEach((el, i) => el.classList.toggle('active', i === suggestIndex));
        if (suggestIndex >= 0) items[suggestIndex].scrollIntoView({ block: 'nearest' });
    });

    input.addEventListener('focus', function() {
        const q = this.value.trim();
        if (q.length >= 2) fetchSuggest(q);
    });

    function fetchSuggest(q) {
        fetch(`/api/suggest-opk?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(results => {
                suggestIndex = -1;
                if (!results.length) {
                    dropdown.innerHTML = '<div class="suggest-empty">Tidak ditemukan</div>';
                } else {
                    dropdown.innerHTML = results.map(opk => `
                        <div class="suggest-item" onclick="location.href='${opk.url}'">
                            <div class="suggest-thumb">
                                ${opk.foto ? `<img src="${opk.foto}" alt="${opk.nama}">` : (opk.ikon || '🏛️')}
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="suggest-nama">${opk.nama}</div>
                                <div class="suggest-meta">
                                    ${opk.ikon || ''} ${opk.kategori || ''} · ${opk.kec || ''}
                                    <span class="opk-list-badge badge-${opk.kondisi}">${opk.kondisi.charAt(0).toUpperCase()+opk.kondisi.slice(1)}</span>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }
                dropdown.classList.add('show');
            })
            .catch(() => dropdown.classList.remove('show'));
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.suggest-wrap')) {
            dropdown.classList.remove('show');
            suggestIndex = -1;
        }
    });
})();
</script>
@endpush
