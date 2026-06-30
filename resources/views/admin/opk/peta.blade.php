@extends('layouts.app')
@section('title', 'Peta OPK')
@section('page-title', 'Peta Sebaran OPK')

@push('styles')
<style>
    .peta-container {
        position: relative;
        height: calc(100vh - 180px);
        min-height: 500px;
    }
    #peta { width: 100%; height: 100%; border-radius: 4px; }

    .peta-stats {
        position: absolute; top: 1rem; left: 1rem; z-index: 400;
        display: flex; gap: 10px;
    }
    .peta-stat {
        background: rgba(255,255,255,0.95); border: 1px solid var(--border-default);
        border-radius: 4px; padding: 8px 14px; text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        min-width: 60px;
    }
    .peta-stat-num { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 700; line-height: 1; }
    .peta-stat-label { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--abu-gelap); margin-top: 2px; }
    .peta-stat.total .peta-stat-num { color: var(--tanah); }
    .peta-stat.kritis .peta-stat-num { color: var(--merah); }
    .peta-stat.waspada .peta-stat-num { color: var(--kuning); }
    .peta-stat.baik .peta-stat-num { color: var(--hijau); }

    .peta-legend {
        position: absolute; bottom: 1.5rem; left: 1rem; z-index: 400;
        background: rgba(255,255,255,0.95); border: 1px solid var(--border-default);
        border-radius: 4px; padding: 8px 12px; font-size: 0.72rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .peta-legend-item { display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
    .peta-legend-item:last-child { margin-bottom: 0; }
    .peta-legend-dot { width: 10px; height: 10px; border-radius: 50%; border: 2px solid white; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }

    .peta-loading {
        position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: rgba(255,255,255,0.9); padding: 10px 20px; border-radius: 4px;
        font-size: 0.82rem; color: var(--abu-gelap); z-index: 300; display: none;
    }

    @media (max-width: 768px) {
        .peta-container { height: calc(100vh - 160px); }
        .peta-stats { top: 0.5rem; left: 0.5rem; gap: 6px; }
        .peta-stat { padding: 6px 10px; min-width: 50px; }
        .peta-stat-num { font-size: 1.1rem; }
        .peta-legend { display: none; }
    }
</style>
@endpush

@section('content')
<x-ui.page-header title="Peta Sebaran OPK" subtitle="Distribusi geografis Objek Pemajuan Kebudayaan Kabupaten Badung">
    <x-slot:action>
        <div class="d-flex gap-2 align-items-center">
            <select id="filterKondisiPeta" class="form-select form-select-sm" style="width:140px;">
                <option value="">Semua Status</option>
                <option value="kritis">🔴 Kritis</option>
                <option value="waspada">🟡 Waspada</option>
                <option value="baik">🟢 Baik</option>
            </select>
            <a href="{{ route('admin.opk.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-table me-1"></i>Tabel
            </a>
        </div>
    </x-slot:action>
</x-ui.page-header>

<div class="peta-container">
    <div id="peta"></div>

    <div class="peta-stats" id="petaStats">
        <div class="peta-stat total">
            <div class="peta-stat-num" id="statTotal">-</div>
            <div class="peta-stat-label">Total</div>
        </div>
        <div class="peta-stat kritis">
            <div class="peta-stat-num" id="statKritis">-</div>
            <div class="peta-stat-label">Kritis</div>
        </div>
        <div class="peta-stat waspada">
            <div class="peta-stat-num" id="statWaspada">-</div>
            <div class="peta-stat-label">Waspada</div>
        </div>
        <div class="peta-stat baik">
            <div class="peta-stat-num" id="statBaik">-</div>
            <div class="peta-stat-label">Baik</div>
        </div>
    </div>

    <div class="peta-legend">
        <div class="peta-legend-item"><span class="peta-legend-dot" style="background:var(--merah);"></span> Kritis — perlu tindakan</div>
        <div class="peta-legend-item"><span class="peta-legend-dot" style="background:var(--kuning);"></span> Waspada — perlu pantau</div>
        <div class="peta-legend-item"><span class="peta-legend-dot" style="background:var(--hijau);"></span> Baik — terlindungi</div>
    </div>

    <div class="peta-loading" id="petaLoading">
        <i class="bi bi-arrow-clockwise me-2" style="animation:spin 1s linear infinite;"></i>Memuat data peta...
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
(function() {

const peta = L.map('peta').setView([-8.65, 115.18], 11);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 18
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

function makeIcon(kondisi) {
    const color = getColor(kondisi);
    return L.divIcon({
        className: '',
        html: `<div style="width:14px;height:14px;border-radius:50%;background:${color};border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);${kondisi==='kritis'?'animation:pulse 1.5s infinite':''}"></div>`,
        iconSize: [14, 14],
        iconAnchor: [7, 7],
    });
}

let markerCluster;

function loadPeta(kondisi = '') {
    const loading = document.getElementById('petaLoading');
    loading.style.display = 'block';

    const url = "{{ route('admin.peta.data') }}" + (kondisi ? `?kondisi=${kondisi}` : '');
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (markerCluster) peta.removeLayer(markerCluster);
            markerCluster = L.markerClusterGroup({ maxClusterRadius: 50 });

            let kritis = 0, waspada = 0, baik = 0;

            data.forEach(opk => {
                if (!opk.lat || !opk.lng) return;
                if (opk.kondisi === 'kritis') kritis++;
                else if (opk.kondisi === 'waspada') waspada++;
                else baik++;

                const marker = L.marker([opk.lat, opk.lng], { icon: makeIcon(opk.kondisi) });
                marker.bindPopup(`
                    <div class="popup-${opk.kondisi}" style="min-width:200px;padding:4px 0;">
                        <strong style="font-size:0.88rem;">${opk.nama}</strong><br>
                        <span style="font-size:0.72rem;color:#666;">${opk.ikon_kategori} ${opk.kategori}</span><br>
                        <span style="font-size:0.72rem;color:#666;">📍 ${opk.kecamatan} · ${opk.desa_adat}</span>
                        <hr style="margin:6px 0;">
                        <a href="${opk.detail_url}" style="font-size:0.75rem;color:${ds.emas};font-weight:600;">
                            Lihat Detail →
                        </a>
                    </div>
                `, { maxWidth: 250 });
                markerCluster.addLayer(marker);
            });

            peta.addLayer(markerCluster);

            document.getElementById('statTotal').textContent = data.length;
            document.getElementById('statKritis').textContent = kritis;
            document.getElementById('statWaspada').textContent = waspada;
            document.getElementById('statBaik').textContent = baik;
        })
        .finally(() => {
            loading.style.display = 'none';
        });
}

loadPeta();

document.getElementById('filterKondisiPeta').addEventListener('change', function() {
    loadPeta(this.value);
});

document.head.appendChild(Object.assign(document.createElement('style'), {
    textContent: '@keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }'
}));

})();
</script>
@endpush
