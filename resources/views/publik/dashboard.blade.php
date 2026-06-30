@extends('layouts.publik')

@section('title', 'Peta OPK Kabupaten Badung')

@section('content')
<div class="main-layout">

    {{-- SIDEBAR --}}
    <div class="sidebar-publik" id="sidebar">
        <div class="sidebar-tabs">
            <div class="sidebar-tab active" data-panel="filter" onclick="window._switchTab('filter')">Semua OPK</div>
            <div class="sidebar-tab" data-panel="terbaru" onclick="window._switchTab('terbaru')">Terbaru</div>
        </div>

        {{-- Panel: Filter + Daftar --}}
        <div class="sidebar-panel active" id="panel-filter">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchOpk" placeholder="Cari nama OPK..." autocomplete="off">
                <div class="suggest-dropdown" id="suggestDropdown"></div>
            </div>

            <div class="filter-group">
                <div class="filter-label">Kategori</div>
                <select id="filterKat" class="filter-select" onchange="window._filterKategori(this.value)">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori->where('total', '>', 0) as $kat)
                    <option value="{{ $kat->id }}">{{ $kat->ikon }} {{ $kat->nama }} ({{ $kat->total }})</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <div class="filter-label">Kondisi</div>
                <button class="filter-btn active" data-kondisi="" onclick="window._filterKondisi(this, '')">
                    <span class="filter-dot" style="background:var(--abu);"></span> Semua
                </button>
                <button class="filter-btn" data-kondisi="kritis" onclick="window._filterKondisi(this, 'kritis')">
                    <span class="filter-dot" style="background:var(--merah);"></span> Kritis
                </button>
                <button class="filter-btn" data-kondisi="waspada" onclick="window._filterKondisi(this, 'waspada')">
                    <span class="filter-dot" style="background:var(--kuning);"></span> Waspada
                </button>
                <button class="filter-btn" data-kondisi="baik" onclick="window._filterKondisi(this, 'baik')">
                    <span class="filter-dot" style="background:var(--hijau);"></span> Baik
                </button>
            </div>

            <div class="filter-group">
                <div class="filter-label">Kecamatan</div>
                <select id="filterKec" class="filter-select" onchange="window._filterKecamatan(this.value)">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans->where('total', '>', 0) as $kec)
                    <option value="{{ $kec->id }}">{{ $kec->nama }} ({{ $kec->total }})</option>
                    @endforeach
                </select>
            </div>

            <div class="opk-list" id="opkList">
                <div style="padding:1.5rem;color:var(--abu);text-align:center" class="t-body">
                    <i class="bi bi-hand-index-thumb" style="display:block;font-size:1.3rem;margin-bottom:6px;"></i>
                    Pilih filter atau klik marker di peta
                </div>
            </div>
        </div>

        {{-- Panel: Terbaru --}}
        <div class="sidebar-panel" id="panel-terbaru">
            <div class="terbaru-grid">
                @foreach($terbaru as $opk)
                <a href="{{ route('publik.opk.show', $opk) }}" class="terbaru-card">
                    <div class="terbaru-img">
                        @if($opk->fotoUtama)
                            <img src="{{ asset('storage/'.$opk->fotoUtama->path) }}" alt="{{ $opk->nama_opk }}">
                        @else
                            {{ $opk->kategori?->ikon ?? '🏛️' }}
                        @endif
                    </div>
                    <div class="terbaru-info">
                        <div class="terbaru-nama">{{ $opk->nama_opk }}</div>
                        <div class="terbaru-meta">{{ $opk->kecamatan?->nama }}</div>
                        <span class="kondisi-pill pill-{{ $opk->kondisi }}">{{ ucfirst($opk->kondisi) }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- MAP AREA --}}
    <div class="map-wrapper">
        <div id="petaPublik"></div>

        {{-- Stats overlay --}}
        <div class="stats-overlay">
            <div class="stat-item">
                <span class="stat-num" style="color:var(--tanah);">{{ $stats['total'] }}</span>
                <span style="color:var(--abu);text-transform:uppercase;letter-spacing:0.05em" class="t-caption">Total</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-dot" style="background:var(--merah);"></span>
                <span class="stat-num" style="color:var(--merah);">{{ $stats['kritis'] }}</span>
                <span style="color:var(--abu);text-transform:uppercase;letter-spacing:0.05em" class="t-caption">Kritis</span>
            </div>
            <div class="stat-item">
                <span class="stat-dot" style="background:var(--kuning);"></span>
                <span class="stat-num" style="color:var(--kuning);">{{ $stats['waspada'] }}</span>
                <span style="color:var(--abu);text-transform:uppercase;letter-spacing:0.05em" class="t-caption">Waspada</span>
            </div>
            <div class="stat-item">
                <span class="stat-dot" style="background:var(--hijau);"></span>
                <span class="stat-num" style="color:var(--hijau);">{{ $stats['baik'] }}</span>
                <span style="color:var(--abu);text-transform:uppercase;letter-spacing:0.05em" class="t-caption">Baik</span>
            </div>
        </div>

        {{-- Legend --}}
        <div class="peta-legend">
            <div class="legend-title">Legenda</div>
            <div class="legend-item"><span class="stat-dot" style="background:var(--merah);border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span> Kritis</div>
            <div class="legend-item"><span class="stat-dot" style="background:var(--kuning);border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span> Waspada</div>
            <div class="legend-item"><span class="stat-dot" style="background:var(--hijau);border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></span> Baik</div>
        </div>

        {{-- Loading --}}
        <div id="petaLoading">
            <i class="bi bi-arrow-clockwise" style="animation:spin 1s linear infinite;margin-right:6px;"></i>Memuat data...
        </div>

        {{-- Mobile toggle sidebar --}}
        <button class="mobile-toggle" id="mobileToggle" onclick="window._toggleSidebar()">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </div>
</div>

{{-- Mobile sidebar backdrop --}}
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="window._toggleSidebar()"></div>
@endsection

@push('scripts')
<script type="module">
(function() {

let allData = [];
let markers = [];
let markerCluster;
let activeKondisi = '';
let activeKat = '';
let activeKec = '';
let selectedId = null;

const peta = L.map('petaPublik', { zoomControl: true }).setView([-8.65, 115.18], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(peta);

const ds = {
    merah:  getComputedStyle(document.documentElement).getPropertyValue('--merah').trim(),
    kuning: getComputedStyle(document.documentElement).getPropertyValue('--kuning').trim(),
    hijau:  getComputedStyle(document.documentElement).getPropertyValue('--hijau').trim(),
    emas:   getComputedStyle(document.documentElement).getPropertyValue('--emas').trim(),
};

function getColor(kondisi) {
    return kondisi === 'kritis' ? ds.merah : kondisi === 'waspada' ? ds.kuning : ds.hijau;
}

function makeIcon(kondisi, selected) {
    const color = getColor(kondisi);
    const size  = selected ? 18 : 13;
    const pulse = kondisi === 'kritis'
        ? `<div style="position:absolute;inset:-4px;border-radius:50%;border:2px solid ${color};animation:pulseRing 1.5s infinite;"></div>`
        : '';
    return L.divIcon({
        className: '',
        html: `<div style="position:relative;width:${size}px;height:${size}px;">${pulse}<div style="width:${size}px;height:${size}px;border-radius:50%;background:${color};border:${selected ? 3 : 2}px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div></div>`,
        iconSize: [size, size],
        iconAnchor: [size/2, size/2],
    });
}

function loadData() {
    document.getElementById('petaLoading').style.display = 'block';
    let url = "{{ route('publik.peta.json') }}";
    const params = [];
    if (activeKondisi) params.push('kondisi=' + activeKondisi);
    if (activeKat)     params.push('kategori_id=' + activeKat);
    if (activeKec)     params.push('kecamatan_id=' + activeKec);
    if (params.length) url += '?' + params.join('&');

    fetch(url)
        .then(r => r.json())
        .then(data => {
            allData = data;
            renderMarkers(data);
            renderList(data);
        })
        .finally(() => document.getElementById('petaLoading').style.display = 'none');
}

function renderMarkers(data) {
    markers.forEach(m => peta.removeLayer(m));
    markers = [];
    if (markerCluster) peta.removeLayer(markerCluster);
    markerCluster = L.markerClusterGroup({ maxClusterRadius: 50 });

    data.forEach(opk => {
        if (!opk.lat || !opk.lng) return;
        const m = L.marker([opk.lat, opk.lng], { icon: makeIcon(opk.kondisi) });
        const kondisiColor = getColor(opk.kondisi);
        m.bindPopup(`
            <div class="peta-popup" style="border-top:3px solid ${kondisiColor};padding-top:6px;">
                <div class="peta-popup-nama">${opk.nama}</div>
                <div class="peta-popup-meta">${opk.ikon || ''} ${opk.kategori || ''} &nbsp;·&nbsp; ${opk.kec || ''}</div>
                <span class="peta-popup-kondisi" style="background:${kondisiColor}22;color:${kondisiColor};">${opk.kondisi.charAt(0).toUpperCase()+opk.kondisi.slice(1)}</span>
                <a href="${opk.url}" class="peta-popup-btn">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        `, { maxWidth: 230 });
        m.on('click', () => selectOpk(opk.id));
        m._opkId = opk.id;
        markers.push(m);
        markerCluster.addLayer(m);
    });

    peta.addLayer(markerCluster);
}

function renderList(data) {
    const list = document.getElementById('opkList');
    const q    = document.getElementById('searchOpk').value.toLowerCase();
    const filtered = q ? data.filter(o => o.nama.toLowerCase().includes(q)) : data;

    if (!filtered.length) {
        list.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--abu)" class="t-body"><i class="bi bi-inbox" style="font-size:1.4rem;display:block;margin-bottom:8px;"></i>Tidak ada OPK ditemukan.</div>';
        return;
    }

    list.innerHTML = filtered.map(opk => `
        <div class="opk-item ${selectedId === opk.id ? 'selected' : ''}" id="li-${opk.id}" onclick="window._selectOpk(${opk.id})">
            <div class="opk-thumb">
                ${opk.foto ? `<img src="${opk.foto}" alt="${opk.nama}">` : (opk.ikon || '🏛️')}
            </div>
            <div style="flex:1;min-width:0;">
                <div class="opk-nama">${opk.nama}</div>
                <div class="opk-meta">
                    ${opk.kec || ''} · ${opk.desa || ''}
                    <span class="kondisi-pill pill-${opk.kondisi}">${opk.kondisi.charAt(0).toUpperCase()+opk.kondisi.slice(1)}</span>
                </div>
                <div class="opk-meta">${opk.ikon || ''} ${opk.kategori || ''}</div>
            </div>
        </div>
    `).join('');
}

function selectOpk(id) {
    selectedId = id;
    const opk = allData.find(o => o.id === id);
    if (!opk) return;

    document.querySelectorAll('.opk-item').forEach(el => el.classList.remove('selected'));
    const li = document.getElementById('li-' + id);
    if (li) { li.classList.add('selected'); li.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }

    markers.forEach(m => m.setIcon(makeIcon(allData.find(o => o.id === m._opkId)?.kondisi || 'baik', m._opkId === id)));

    if (opk.lat && opk.lng) {
        peta.flyTo([opk.lat, opk.lng], 15, { duration: 0.8 });
        const marker = markers.find(m => m._opkId === id);
        if (marker) setTimeout(() => marker.openPopup(), 900);
    }
}
window._selectOpk = selectOpk;

function filterKondisi(btn, val) {
    document.querySelectorAll('#panel-filter .filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeKondisi = val;
    loadData();
}
window._filterKondisi = filterKondisi;

function filterKecamatan(val) {
    activeKec = val;
    loadData();
}
window._filterKecamatan = filterKecamatan;

function filterKategori(val) {
    activeKat = val;
    loadData();
}
window._filterKategori = filterKategori;

let searchTimeout;
let suggestIndex = -1;
const searchInput = document.getElementById('searchOpk');
const suggestDropdown = document.getElementById('suggestDropdown');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const q = this.value.trim();
    if (q.length >= 2) {
        searchTimeout = setTimeout(() => fetchSuggest(q), 250);
    } else {
        suggestDropdown.classList.remove('show');
        suggestIndex = -1;
        renderList(allData);
    }
});

searchInput.addEventListener('keydown', function(e) {
    const items = suggestDropdown.querySelectorAll('.suggest-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') { e.preventDefault(); suggestIndex = Math.min(suggestIndex + 1, items.length - 1); updateSuggestActive(items); }
    else if (e.key === 'ArrowUp')   { e.preventDefault(); suggestIndex = Math.max(suggestIndex - 1, 0); updateSuggestActive(items); }
    else if (e.key === 'Enter')     { if (suggestIndex >= 0) { e.preventDefault(); items[suggestIndex].click(); } }
    else if (e.key === 'Escape')    { suggestDropdown.classList.remove('show'); suggestIndex = -1; }
});

function updateSuggestActive(items) {
    items.forEach((el, i) => el.classList.toggle('active', i === suggestIndex));
    if (suggestIndex >= 0) items[suggestIndex].scrollIntoView({ block: 'nearest' });
}

function fetchSuggest(q) {
    fetch(`/api/suggest-opk?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(results => {
            if (!results.length) {
                suggestDropdown.innerHTML = '<div class="suggest-empty">Tidak ditemukan</div>';
            } else {
                suggestIndex = -1;
                suggestDropdown.innerHTML = results.map(opk => `
                    <div class="suggest-item" onclick="window._goToOpk(${opk.id})">
                        <div class="suggest-thumb">
                            ${opk.foto ? `<img src="${opk.foto}" alt="${opk.nama}">` : (opk.ikon || '🏛️')}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div class="suggest-nama">${opk.nama}</div>
                            <div class="suggest-meta">
                                ${opk.ikon || ''} ${opk.kategori || ''} · ${opk.kec || ''}
                                <span class="kondisi-pill pill-${opk.kondisi}">${opk.kondisi.charAt(0).toUpperCase()+opk.kondisi.slice(1)}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            suggestDropdown.classList.add('show');
        })
        .catch(() => {
            suggestDropdown.classList.remove('show');
        });
}

function goToOpk(id) { window.location.href = `/opk/${id}`; }
window._goToOpk = goToOpk;

document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box')) {
        suggestDropdown.classList.remove('show');
        suggestIndex = -1;
    }
});

searchInput.addEventListener('focus', function() {
    const q = this.value.trim();
    if (q.length >= 2) fetchSuggest(q);
});

function switchTab(tab) {
    document.querySelectorAll('.sidebar-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sidebar-panel').forEach(p => p.classList.remove('active'));
    document.querySelector(`.sidebar-tab[data-panel="${tab}"]`).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
}
window._switchTab = switchTab;

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    sidebar.classList.toggle('open');
    backdrop.classList.toggle('show');
}
window._toggleSidebar = toggleSidebar;

const style = document.createElement('style');
style.textContent = `
    @keyframes pulseRing { 0%{transform:scale(1);opacity:1} 100%{transform:scale(2.5);opacity:0} }
    @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
`;
document.head.appendChild(style);

loadData();

})();
</script>
@endpush
