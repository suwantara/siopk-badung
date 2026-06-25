<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta OPK Kabupaten Badung — SIOPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --tanah:#2C1A0E; --emas:#C8922A; --emas-muda:#E8B84B;
            --krem:#F7F1E8; --hijau:#2D5A27; --merah:#C0392B; --kuning:#D4A017;
            --emas-rgb:200,146,42; --merah-rgb:192,57,43; --kuning-rgb:212,160,23; --hijau-rgb:45,90,39;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--krem); color:var(--tanah); }

        /* NAV */
        .publik-nav {
            background:var(--tanah); padding:0 1.5rem; height:58px;
            display:flex; align-items:center; justify-content:space-between;
            border-bottom:2px solid var(--emas); position:sticky; top:0; z-index:500;
        }
        .nav-brand { display:flex; align-items:center; gap:10px; }
        .nav-logo { width:34px; height:34px; border-radius:50%; background:var(--emas); display:flex; align-items:center; justify-content:center; font-family:'Cormorant Garamond',serif; font-weight:700; color:var(--tanah); font-size:1rem; }
        .nav-title { font-family:'Cormorant Garamond',serif; font-size:1.05rem; font-weight:700; color:#f7f1e8; }
        .nav-title span { color:var(--emas-muda); }
        .nav-links { display:flex; align-items:center; gap:1.5rem; }
        .nav-links a { color:rgba(247,241,232,0.65); text-decoration:none; font-size:0.82rem; font-weight:500; transition:color 0.2s; }
        .nav-links a:hover { color:var(--emas-muda); }
        .nav-links a.active { color:var(--emas-muda); }
        .btn-lapor { background:var(--emas); color:var(--tanah); border:none; padding:7px 18px; border-radius:3px; font-size:0.82rem; font-weight:600; text-decoration:none; cursor:pointer; }
        .btn-lapor:hover { background:var(--emas-muda); color:var(--tanah); }

        /* HERO */
        .hero-strip {
            background:linear-gradient(135deg, var(--tanah) 0%, #3d2410 100%);
            padding:2rem 1.5rem; text-align:center;
        }
        .hero-strip h1 { font-family:'Cormorant Garamond',serif; font-size:clamp(1.5rem,4vw,2.4rem); font-weight:700; color:#f7f1e8; margin-bottom:4px; }
        .hero-strip p { color:rgba(247,241,232,0.6); font-size:0.85rem; }

        /* STATS BAR */
        .stats-bar { background:white; border-bottom:1px solid #d4c9b8; padding:0.75rem 1.5rem; }
        .stats-inner { max-width:1200px; margin:0 auto; display:flex; gap:2rem; align-items:center; flex-wrap:wrap; }
        .stat-item { display:flex; align-items:center; gap:8px; }
        .stat-num { font-family:'Cormorant Garamond',serif; font-size:1.6rem; font-weight:700; color:var(--tanah); line-height:1; }
        .stat-label { font-size:0.7rem; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em; }
        .stat-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

        /* LAYOUT */
        .main-layout { display:grid; grid-template-columns:300px 1fr; height:calc(100vh - 160px); }

        /* SIDEBAR */
        .sidebar-publik { background:white; border-right:1px solid #d4c9b8; overflow-y:auto; display:flex; flex-direction:column; }
        .sidebar-header { padding:1rem; border-bottom:1px solid #e5e0d8; }
        .sidebar-header h6 { font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; color:#9ca3af; margin:0 0 8px; }

        /* Filter */
        .filter-group { padding:1rem; border-bottom:1px solid #f0ebe3; }
        .filter-label { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--tanah); margin-bottom:6px; }
        .filter-btn { display:flex; align-items:center; gap:8px; width:100%; padding:7px 10px; border:1px solid #d4c9b8; border-radius:3px; background:var(--krem); cursor:pointer; font-size:0.8rem; margin-bottom:5px; transition:all 0.15s; }
        .filter-btn:hover { border-color:var(--emas); background:rgba(var(--emas-rgb),0.05); }
        .filter-btn.active { border-color:var(--emas); background:rgba(var(--emas-rgb),0.1); font-weight:600; color:var(--tanah); }
        .filter-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

        /* OPK list */
        .opk-list { flex:1; overflow-y:auto; }
        .opk-item { padding:10px 1rem; border-bottom:1px solid #f0ebe3; cursor:pointer; transition:background 0.15s; display:flex; gap:10px; align-items:flex-start; }
        .opk-item:hover { background:rgba(var(--emas-rgb),0.04); }
        .opk-item.selected { background:rgba(var(--emas-rgb),0.08); border-left:3px solid var(--emas); }
        .opk-thumb { width:44px; height:44px; border-radius:3px; background:#e8e0d4; flex-shrink:0; overflow:hidden; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
        .opk-thumb img { width:100%; height:100%; object-fit:cover; }
        .opk-nama { font-weight:600; font-size:0.82rem; color:var(--tanah); line-height:1.3; }
        .opk-meta { font-size:0.68rem; color:#9ca3af; margin-top:3px; }
        .kondisi-pill { display:inline-block; padding:1px 7px; border-radius:10px; font-size:0.6rem; font-weight:700; margin-left:4px; }
        .pill-kritis { background:rgba(var(--merah-rgb),0.1); color:var(--merah); }
        .pill-waspada { background:rgba(var(--kuning-rgb),0.1); color:var(--kuning); }
        .pill-baik { background:rgba(var(--hijau-rgb),0.1); color:var(--hijau); }

        /* PETA */
        #petaPublik { width:100%; height:100%; z-index:1; }

        /* POPUP PETA */
        .peta-popup { min-width:220px; }
        .peta-popup-nama { font-family:'Cormorant Garamond',serif; font-size:1rem; font-weight:700; color:var(--tanah); margin-bottom:4px; }
        .peta-popup-meta { font-size:0.72rem; color:#6b7280; margin-bottom:8px; }
        .peta-popup-kondisi { display:inline-block; padding:2px 8px; border-radius:10px; font-size:0.65rem; font-weight:700; margin-bottom:8px; }
        .peta-popup-btn { display:block; text-align:center; padding:6px; background:var(--emas); color:var(--tanah); text-decoration:none; border-radius:3px; font-size:0.75rem; font-weight:600; }
        .peta-popup-btn:hover { background:var(--emas-muda); color:var(--tanah); }

        .leaflet-popup-content a { color:var(--tanah); }

        /* PANEL DETAIL (klik OPK di list) */
        .detail-panel { position:absolute; bottom:0; right:0; width:340px; background:white; border-top:1px solid #d4c9b8; border-left:1px solid #d4c9b8; border-top-left-radius:6px; z-index:400; max-height:65%; overflow-y:auto; display:none; box-shadow:-4px -4px 20px rgba(0,0,0,0.1); }
        .detail-panel.show { display:block; }

        /* LEGEND */
        .peta-legend { position:absolute; bottom:1rem; left:1rem; background:rgba(255,255,255,0.95); border:1px solid #d4c9b8; border-radius:4px; padding:10px 14px; z-index:400; font-size:0.72rem; }
        .legend-item { display:flex; align-items:center; gap:8px; margin-bottom:4px; }
        .legend-item:last-child { margin-bottom:0; }

        /* KATEGORI pills */
        .kat-pills { display:flex; flex-wrap:wrap; gap:4px; }
        .kat-pill { padding:3px 10px; border:1px solid #d4c9b8; border-radius:10px; font-size:0.68rem; cursor:pointer; background:white; transition:all 0.15s; }
        .kat-pill:hover, .kat-pill.active { background:var(--emas); border-color:var(--emas); color:var(--tanah); font-weight:600; }

        /* Search */
        .search-box { position:relative; }
        .search-box input { width:100%; border:1px solid #d4c9b8; border-radius:3px; padding:8px 12px 8px 32px; font-size:0.82rem; background:var(--krem); outline:none; }
        .search-box input:focus { border-color:var(--emas); }
        .search-box i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#9ca3af; font-size:0.85rem; }

        @media (max-width:768px) {
            .main-layout { grid-template-columns:1fr; }
            .sidebar-publik { display:none; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="publik-nav">
    <div class="nav-brand">
        <div class="nav-logo">𝔅</div>
        <div class="nav-title">SIOPK <span>Badung</span></div>
    </div>
    <div class="nav-links">
        <a href="{{ route('publik.dashboard') }}" class="active">🗺 Peta OPK</a>
        <a href="{{ route('publik.lapor.index') }}">Lapor OPK</a>
        <a href="{{ route('publik.lapor.status') }}">Cek Status</a>
        <a href="{{ route('login') }}" style="color:rgba(247,241,232,0.35);">Login Dinas</a>
    </div>
    <a href="{{ route('publik.lapor.index') }}" class="btn-lapor">
        <i class="bi bi-plus-circle me-1"></i>Lapor Sekarang
    </a>
</nav>

<!-- HERO -->
<div class="hero-strip">
    <h1>Peta Objek Pemajuan Kebudayaan</h1>
    <p>Kabupaten Badung, Bali · {{ $stats['total'] }} OPK terdokumentasi · Berdasarkan UU No. 5 Tahun 2017</p>
</div>

<!-- STATS BAR -->
<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat-item">
            <div><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total OPK</div></div>
        </div>
        <div style="width:1px;height:32px;background:#e5e0d8;"></div>
        <div class="stat-item">
            <div class="stat-dot" style="background:var(--merah);"></div>
            <div><div class="stat-num" style="color:var(--merah);">{{ $stats['kritis'] }}</div><div class="stat-label">Kritis</div></div>
        </div>
        <div class="stat-item">
            <div class="stat-dot" style="background:var(--kuning);"></div>
            <div><div class="stat-num" style="color:var(--kuning);">{{ $stats['waspada'] }}</div><div class="stat-label">Waspada</div></div>
        </div>
        <div class="stat-item">
            <div class="stat-dot" style="background:var(--hijau);"></div>
            <div><div class="stat-num" style="color:var(--hijau);">{{ $stats['baik'] }}</div><div class="stat-label">Baik</div></div>
        </div>
        <div style="margin-left:auto;">
            <div class="kat-pills" id="katPills">
                <button class="kat-pill active" data-kat="">Semua Jenis</button>
                @foreach($kategori->where('total','>',0) as $kat)
                <button class="kat-pill" data-kat="{{ $kat->id }}">{{ $kat->ikon }} {{ $kat->nama }} ({{ $kat->total }})</button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- MAIN LAYOUT -->
<div class="main-layout" style="position:relative;">

    <!-- SIDEBAR -->
    <div class="sidebar-publik">
        <div class="sidebar-header">
            <h6>Filter & Daftar OPK</h6>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchOpk" placeholder="Cari nama OPK...">
            </div>
        </div>

        <!-- Filter Kondisi -->
        <div class="filter-group">
            <div class="filter-label">Status Kondisi</div>
            <button class="filter-btn active" data-kondisi="" onclick="filterKondisi(this,'')">
                <span class="filter-dot" style="background:#9ca3af;"></span> Semua Kondisi
            </button>
            <button class="filter-btn" data-kondisi="kritis" onclick="filterKondisi(this,'kritis')">
                <span class="filter-dot" style="background:var(--merah);"></span> Kritis
            </button>
            <button class="filter-btn" data-kondisi="waspada" onclick="filterKondisi(this,'waspada')">
                <span class="filter-dot" style="background:var(--kuning);"></span> Waspada
            </button>
            <button class="filter-btn" data-kondisi="baik" onclick="filterKondisi(this,'baik')">
                <span class="filter-dot" style="background:var(--hijau);"></span> Baik
            </button>
        </div>

        <!-- Filter Kecamatan -->
        <div class="filter-group">
            <div class="filter-label">Kecamatan</div>
            <select id="filterKec" class="form-select form-select-sm" onchange="filterKecamatan(this.value)">
                <option value="">Semua Kecamatan</option>
                @foreach($kecamatans->where('total','>',0) as $kec)
                <option value="{{ $kec->id }}">{{ $kec->nama }} ({{ $kec->total }})</option>
                @endforeach
            </select>
        </div>

        <!-- Daftar OPK -->
        <div class="opk-list" id="opkList">
            <div style="padding:1rem;font-size:0.75rem;color:#9ca3af;text-align:center;">
                <i class="bi bi-arrow-up-circle" style="display:block;font-size:1.2rem;margin-bottom:4px;"></i>
                Pilih filter atau klik marker di peta
            </div>
        </div>
    </div>

    <!-- PETA -->
    <div style="position:relative;">
        <div id="petaPublik"></div>

        <!-- Legend -->
        <div class="peta-legend">
            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:6px;color:var(--tanah);">LEGENDA</div>
            <div class="legend-item"><div class="stat-dot" style="background:var(--merah);border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></div>Kritis</div>
            <div class="legend-item"><div class="stat-dot" style="background:var(--kuning);border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></div>Waspada</div>
            <div class="legend-item"><div class="stat-dot" style="background:var(--hijau);border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></div>Baik</div>
        </div>

        <!-- Loading indicator -->
        <div id="petaLoading" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(255,255,255,0.9);padding:12px 20px;border-radius:4px;font-size:0.82rem;z-index:300;display:none;">
            <i class="bi bi-arrow-clockwise" style="animation:spin 1s linear infinite;margin-right:6px;"></i>Memuat data...
        </div>
    </div>

</div>

<!-- OPK Terbaru (bawah peta) -->
<div style="background:white;border-top:1px solid #d4c9b8;padding:1.5rem;">
    <div style="max-width:1200px;margin:0 auto;">
        <div style="font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#9ca3af;margin-bottom:1rem;">
            OPK TERBARU TERDOKUMENTASI
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;">
            @foreach($terbaru as $opk)
            <a href="{{ route('publik.opk.show', $opk) }}" style="text-decoration:none;color:var(--tanah);">
                <div style="border:1px solid #d4c9b8;border-radius:4px;overflow:hidden;transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'" onmouseout="this.style.boxShadow=''">
                    <div style="height:100px;background:#e8e0d4;display:flex;align-items:center;justify-content:center;font-size:2rem;overflow:hidden;">
                        @if($opk->fotoUtama)
                            <img src="{{ asset('storage/'.$opk->fotoUtama->path) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ $opk->kategori?->ikon ?? '🏛️' }}
                        @endif
                    </div>
                    <div style="padding:10px;">
                        <div style="font-size:0.8rem;font-weight:600;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $opk->nama_opk }}</div>
                        <div style="font-size:0.68rem;color:#9ca3af;">{{ $opk->kategori?->nama }} · {{ $opk->kecamatan?->nama }}</div>
                        <span class="kondisi-pill pill-{{ $opk->kondisi }}">{{ ucfirst($opk->kondisi) }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
// ── State ──
let allData = [];
let markers = [];
let activeKondisi = '';
let activeKat = '';
let activeKec = '';
let selectedId = null;

// ── Init Peta ──
const peta = L.map('petaPublik', { zoomControl: true }).setView([-8.65, 115.18], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 19
}).addTo(peta);

// ── Baca warna dari design system ──
const ds = {
    merah:  getComputedStyle(document.documentElement).getPropertyValue('--merah').trim(),
    kuning: getComputedStyle(document.documentElement).getPropertyValue('--kuning').trim(),
    hijau:  getComputedStyle(document.documentElement).getPropertyValue('--hijau').trim(),
    emas:   getComputedStyle(document.documentElement).getPropertyValue('--emas').trim(),
};

// ── Warna & Icon ──
function getColor(kondisi) {
    return kondisi === 'kritis' ? ds.merah : kondisi === 'waspada' ? ds.kuning : ds.hijau;
}

function makeIcon(kondisi, selected = false) {
    const color = getColor(kondisi);
    const size  = selected ? 20 : 14;
    const pulse = kondisi === 'kritis' ? `<div style="position:absolute;inset:-4px;border-radius:50%;border:2px solid ${color};animation:pulseRing 1.5s infinite;"></div>` : '';
    return L.divIcon({
        className: '',
        html: `<div style="position:relative;width:${size}px;height:${size}px;">${pulse}<div style="width:${size}px;height:${size}px;border-radius:50%;background:${color};border:${selected ? 3 : 2}px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div></div>`,
        iconSize: [size, size], iconAnchor: [size/2, size/2],
    });
}

// ── Load data ──
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

// ── Render markers ──
let markerCluster;
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
            <div class="peta-popup" style="border-top:3px solid ${kondisiColor};padding-top:8px;">
                <div class="peta-popup-nama">${opk.nama}</div>
                <div class="peta-popup-meta">${opk.ikon || ''} ${opk.kategori || ''} &nbsp;·&nbsp; ${opk.kec || ''}</div>
                <span class="peta-popup-kondisi" style="background:${kondisiColor}22;color:${kondisiColor};">${opk.kondisi.charAt(0).toUpperCase()+opk.kondisi.slice(1)}</span>
                <a href="${opk.url}" class="peta-popup-btn">Lihat Detail →</a>
            </div>
        `, { maxWidth: 240 });

        m.on('click', () => selectOpk(opk.id));
        m._opkId = opk.id;
        markers.push(m);
        markerCluster.addLayer(m);
    });

    peta.addLayer(markerCluster);
}

// ── Render list sidebar ──
function renderList(data) {
    const list = document.getElementById('opkList');
    const q    = document.getElementById('searchOpk').value.toLowerCase();
    const filtered = q ? data.filter(o => o.nama.toLowerCase().includes(q)) : data;

    if (!filtered.length) {
        list.innerHTML = '<div style="padding:2rem;text-align:center;color:#9ca3af;font-size:0.82rem;"><i class="bi bi-inbox" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>Tidak ada OPK ditemukan.</div>';
        return;
    }

    list.innerHTML = filtered.map(opk => `
        <div class="opk-item ${selectedId === opk.id ? 'selected' : ''}" id="li-${opk.id}" onclick="selectOpk(${opk.id})">
            <div class="opk-thumb">
                ${opk.foto ? `<img src="${opk.foto}" alt="${opk.nama}">` : (opk.ikon || '🏛️')}
            </div>
            <div style="flex:1;min-width:0;">
                <div class="opk-nama">${opk.nama}</div>
                <div class="opk-meta">
                    📍 ${opk.kec || ''} · ${opk.desa || ''}
                    <span class="kondisi-pill pill-${opk.kondisi}">${opk.kondisi.charAt(0).toUpperCase()+opk.kondisi.slice(1)}</span>
                </div>
                <div class="opk-meta" style="margin-top:2px;">${opk.ikon || ''} ${opk.kategori || ''}</div>
            </div>
        </div>
    `).join('');
}

// ── Select OPK ──
function selectOpk(id) {
    selectedId = id;
    const opk = allData.find(o => o.id === id);
    if (!opk) return;

    // Update list
    document.querySelectorAll('.opk-item').forEach(el => el.classList.remove('selected'));
    const li = document.getElementById('li-' + id);
    if (li) { li.classList.add('selected'); li.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }

    // Update marker
    markers.forEach(m => m.setIcon(makeIcon(allData.find(o => o.id === m._opkId)?.kondisi || 'baik', m._opkId === id)));

    // Fly to marker
    if (opk.lat && opk.lng) {
        peta.flyTo([opk.lat, opk.lng], 15, { duration: 0.8 });
        const marker = markers.find(m => m._opkId === id);
        if (marker) setTimeout(() => marker.openPopup(), 900);
    }
}

// ── Filter kondisi ──
function filterKondisi(btn, val) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeKondisi = val;
    loadData();
}

// ── Filter kecamatan ──
function filterKecamatan(val) {
    activeKec = val;
    loadData();
}

// ── Filter kategori (pills) ──
document.getElementById('katPills').addEventListener('click', function(e) {
    const btn = e.target.closest('.kat-pill');
    if (!btn) return;
    document.querySelectorAll('.kat-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeKat = btn.dataset.kat;
    loadData();
});

// ── Search ──
let searchTimeout;
document.getElementById('searchOpk').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => renderList(allData), 300);
});

// ── CSS animasi ──
const style = document.createElement('style');
style.textContent = `
    @keyframes pulseRing { 0%{transform:scale(1);opacity:1} 100%{transform:scale(2.5);opacity:0} }
    @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
`;
document.head.appendChild(style);

// ── Init ──
loadData();
</script>
</body>
</html>
