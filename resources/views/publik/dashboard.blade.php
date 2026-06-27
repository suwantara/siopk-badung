@extends('layouts.publik')

@section('title', 'Peta OPK Kabupaten Badung')

@push('styles')
<link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" rel="stylesheet">
<link href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" rel="stylesheet">
<style>
    .main-layout { display: grid; grid-template-columns: 320px 1fr; height: calc(100vh - 56px); position: relative; }

    /* ── SIDEBAR ── */
    .sidebar-publik { background: white; border-right: 1px solid var(--garis); overflow-y: auto; display: flex; flex-direction: column; }
    .sidebar-tabs { display: flex; border-bottom: 1px solid var(--garis); }
    .sidebar-tab { flex: 1; padding: 10px 8px; text-align: center; font-size: 0.72rem; font-weight: 600; cursor: pointer; color: var(--abu); border-bottom: 2px solid transparent; transition: all 0.15s; }
    .sidebar-tab:hover { color: var(--tanah); }
    .sidebar-tab.active { color: var(--emas); border-bottom-color: var(--emas); }
    .sidebar-panel { flex: 1; overflow-y: auto; display: none; }
    .sidebar-panel.active { display: flex; flex-direction: column; }

    /* ── Search ── */
    .search-box { position: relative; margin: 0.75rem; }
    .search-box input { width: 100%; border: 1px solid var(--garis); border-radius: 3px; padding: 8px 12px 8px 32px; font-size: 0.82rem; background: var(--krem); outline: none; }
    .search-box input:focus { border-color: var(--emas); }
    .search-box i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--abu); font-size: 0.85rem; }

    /* ── Filter ── */
    .filter-group { padding: 0 0.75rem 0.75rem; border-bottom: 1px solid var(--garis-terang); }
    .filter-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--tanah); margin: 0.75rem 0 6px; }
    .filter-group:first-child .filter-label { margin-top: 0; }

    .filter-chips { display: flex; flex-wrap: wrap; gap: 4px; }
    .filter-chip { padding: 4px 10px; border: 1px solid var(--garis); border-radius: 10px; font-size: 0.7rem; cursor: pointer; background: white; transition: all 0.15s; white-space: nowrap; }
    .filter-chip:hover { border-color: var(--emas); background: rgba(var(--emas-rgb), 0.06); }
    .filter-chip.active { background: var(--emas); border-color: var(--emas); color: var(--tanah); font-weight: 600; }

    .filter-btn { display: flex; align-items: center; gap: 8px; width: 100%; padding: 6px 8px; border: 1px solid var(--garis); border-radius: 3px; background: var(--krem); cursor: pointer; font-size: 0.78rem; margin-bottom: 4px; transition: all 0.15s; }
    .filter-btn:hover { border-color: var(--emas); background: rgba(var(--emas-rgb), 0.05); }
    .filter-btn.active { border-color: var(--emas); background: rgba(var(--emas-rgb), 0.1); font-weight: 600; }
    .filter-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    .filter-select { width: 100%; border: 1px solid var(--garis); border-radius: 3px; padding: 6px 8px; font-size: 0.78rem; background: var(--krem); outline: none; }
    .filter-select:focus { border-color: var(--emas); }

    /* ── OPK List ── */
    .opk-list { flex: 1; overflow-y: auto; }
    .opk-item { padding: 8px 0.75rem; border-bottom: 1px solid var(--garis-terang); cursor: pointer; transition: background 0.15s; display: flex; gap: 10px; align-items: flex-start; }
    .opk-item:hover { background: rgba(var(--emas-rgb), 0.04); }
    .opk-item.selected { background: rgba(var(--emas-rgb), 0.08); border-left: 3px solid var(--emas); }
    .opk-thumb { width: 40px; height: 40px; border-radius: 3px; background: var(--placeholder); flex-shrink: 0; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .opk-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .opk-nama { font-weight: 600; font-size: 0.8rem; color: var(--tanah); line-height: 1.3; }
    .opk-meta { font-size: 0.66rem; color: var(--abu); margin-top: 2px; }
    .kondisi-pill { display: inline-block; padding: 1px 7px; border-radius: 10px; font-size: 0.6rem; font-weight: 700; }
    .pill-kritis { background: rgba(var(--merah-rgb), 0.1); color: var(--merah); }
    .pill-waspada { background: rgba(var(--kuning-rgb), 0.1); color: var(--kuning); }
    .pill-baik { background: rgba(var(--hijau-rgb), 0.1); color: var(--hijau); }

    /* ── Terbaru grid in sidebar ── */
    .terbaru-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 0.75rem; }
    .terbaru-card { border: 1px solid var(--garis); border-radius: 3px; overflow: hidden; cursor: pointer; transition: box-shadow 0.2s; text-decoration: none; color: var(--tanah); }
    .terbaru-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .terbaru-img { height: 70px; background: var(--placeholder); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; overflow: hidden; }
    .terbaru-img img { width: 100%; height: 100%; object-fit: cover; }
    .terbaru-info { padding: 8px; }
    .terbaru-nama { font-size: 0.72rem; font-weight: 600; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .terbaru-meta { font-size: 0.62rem; color: var(--abu); margin-top: 2px; }

    /* ── PETA AREA ── */
    .map-wrapper { position: relative; }
    #petaPublik { width: 100%; height: 100%; z-index: 1; }

    .leaflet-popup-content a { color: var(--tanah); }

    /* ── Stats Overlay on Map ── */
    .stats-overlay {
        position: absolute; top: 0.75rem; left: 0.75rem; z-index: 400;
        background: rgba(255,255,255,0.95); border: 1px solid var(--garis); border-radius: 4px;
        padding: 10px 14px; display: flex; gap: 14px; align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .stat-item { display: flex; align-items: center; gap: 7px; }
    .stat-num { font-family: 'Cormorant Garamond', serif; font-size: 1.3rem; font-weight: 700; line-height: 1; }
    .stat-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
    .stat-divider { width: 1px; height: 28px; background: var(--garis); }

    /* ── Legend Overlay ── */
    .peta-legend {
        position: absolute; bottom: 1rem; left: 0.75rem; z-index: 400;
        background: rgba(255,255,255,0.95); border: 1px solid var(--garis); border-radius: 4px;
        padding: 8px 12px; font-size: 0.7rem;
    }
    .legend-title { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--tanah); margin-bottom: 5px; }
    .legend-item { display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
    .legend-item:last-child { margin-bottom: 0; }

    /* ── Map Popup ── */
    .peta-popup { min-width: 200px; }
    .peta-popup-nama { font-family: 'Cormorant Garamond', serif; font-size: 0.95rem; font-weight: 700; color: var(--tanah); margin-bottom: 3px; }
    .peta-popup-meta { font-size: 0.7rem; color: var(--abu-gelap); margin-bottom: 6px; }
    .peta-popup-kondisi { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 0.62rem; font-weight: 700; margin-bottom: 6px; }
    .peta-popup-btn { display: block; text-align: center; padding: 5px; background: var(--emas); color: var(--tanah); text-decoration: none; border-radius: 3px; font-size: 0.72rem; font-weight: 600; }
    .peta-popup-btn:hover { background: var(--emas-muda); color: var(--tanah); }

    /* ── Loading ── */
    #petaLoading {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: rgba(255,255,255,0.9); padding: 10px 18px; border-radius: 4px;
        font-size: 0.8rem; z-index: 300; display: none;
    }

    /* ── Mobile ── */
    .mobile-toggle { display: none; position: absolute; top: 0.75rem; right: 3.5rem; z-index: 450; background: white; border: 1px solid var(--garis); border-radius: 4px; padding: 6px 10px; font-size: 0.78rem; cursor: pointer; }
    .mobile-filter-bar { display: none; position: absolute; top: 0.75rem; left: 3.5rem; right: 3.5rem; z-index: 400; overflow-x: auto; white-space: nowrap; gap: 6px; padding: 4px 0; }
    .mobile-filter-bar .filter-chip { display: inline-block; }

    @media (max-width: 768px) {
        .main-layout { grid-template-columns: 1fr; }
        .sidebar-publik {
            display: none; position: fixed; top: 56px; left: 0; bottom: 0; width: 280px;
            z-index: 600; box-shadow: 4px 0 20px rgba(0,0,0,0.15);
        }
        .sidebar-publik.open { display: flex; }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 550; }
        .sidebar-backdrop.show { display: block; }
        .mobile-toggle { display: block; }
        .stats-overlay { top: 0.5rem; left: 0.5rem; padding: 6px 10px; gap: 8px; font-size: 0.7rem; }
        .stat-num { font-size: 1.1rem; }
        .stat-divider { height: 20px; }
        .peta-legend { display: none; }
    }
</style>
@endpush

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
                <input type="text" id="searchOpk" placeholder="Cari nama OPK...">
            </div>

            <div class="filter-group">
                <div class="filter-label">Kategori</div>
                <div class="filter-chips" id="katChips">
                    <span class="filter-chip active" data-kat="">Semua</span>
                    @foreach($kategori->where('total', '>', 0) as $kat)
                    <span class="filter-chip" data-kat="{{ $kat->id }}">{{ $kat->ikon }} {{ $kat->nama }}</span>
                    @endforeach
                </div>
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
                <div style="padding:1.5rem;font-size:0.78rem;color:var(--abu);text-align:center;">
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
                <span style="font-size:0.62rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.05em;">Total</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-dot" style="background:var(--merah);"></span>
                <span class="stat-num" style="color:var(--merah);">{{ $stats['kritis'] }}</span>
                <span style="font-size:0.62rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.05em;">Kritis</span>
            </div>
            <div class="stat-item">
                <span class="stat-dot" style="background:var(--kuning);"></span>
                <span class="stat-num" style="color:var(--kuning);">{{ $stats['waspada'] }}</span>
                <span style="font-size:0.62rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.05em;">Waspada</span>
            </div>
            <div class="stat-item">
                <span class="stat-dot" style="background:var(--hijau);"></span>
                <span class="stat-num" style="color:var(--hijau);">{{ $stats['baik'] }}</span>
                <span style="font-size:0.62rem;color:var(--abu);text-transform:uppercase;letter-spacing:0.05em;">Baik</span>
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
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
        list.innerHTML = '<div style="padding:2rem;text-align:center;color:var(--abu);font-size:0.8rem;"><i class="bi bi-inbox" style="font-size:1.4rem;display:block;margin-bottom:8px;"></i>Tidak ada OPK ditemukan.</div>';
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

document.getElementById('katChips').addEventListener('click', function(e) {
    const chip = e.target.closest('.filter-chip');
    if (!chip) return;
    document.querySelectorAll('#katChips .filter-chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');
    activeKat = chip.dataset.kat;
    loadData();
});

let searchTimeout;
document.getElementById('searchOpk').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => renderList(allData), 300);
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
