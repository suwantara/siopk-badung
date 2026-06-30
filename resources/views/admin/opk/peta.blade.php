@extends('layouts.app')
@section('title', 'Peta OPK')
@section('page-title', 'Peta Sebaran OPK')

@section('content')
<x-ui.page-header title="Peta Sebaran OPK" subtitle="Distribusi geografis Objek Pemajuan Kebudayaan Kabupaten Badung">
    <x-slot:action>
        <select id="filterKondisiPeta" class="form-select form-select-sm" style="width:130px;">
            <option value="">Semua Status</option>
            <option value="kritis">Kritis</option>
            <option value="waspada">Waspada</option>
            <option value="baik">Baik</option>
        </select>
        <a href="{{ route('admin.opk.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-table me-1"></i>Tabel
        </a>
    </x-slot:action>
</x-ui.page-header>

<div class="card">
    <div class="card-body p-0">
        <div id="peta" style="height:calc(100vh - 240px);min-height:500px;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
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
    const url = "{{ route('admin.peta.data') }}" + (kondisi ? `?kondisi=${kondisi}` : '');
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (markerCluster) peta.removeLayer(markerCluster);
            markerCluster = L.markerClusterGroup({ maxClusterRadius: 50 });

            data.forEach(opk => {
                const marker = L.marker([opk.lat, opk.lng], { icon: makeIcon(opk.kondisi) });
                marker.bindPopup(`
                    <div class="popup-${opk.kondisi}" style="min-width:200px;padding:4px 0;">
                        <strong class="t-body-lg">${opk.nama}</strong><br>
                        <span style="color:#666" class="t-caption">${opk.ikon_kategori} ${opk.kategori}</span><br>
                        <span style="color:#666" class="t-caption">📍 ${opk.kecamatan} · ${opk.desa_adat}</span>
                        <hr style="margin:6px 0;">
                        <a href="${opk.detail_url}" style="color:${ds.emas};font-weight:600;" class="t-caption">
                            Lihat Detail →
                        </a>
                    </div>
                `);
                markerCluster.addLayer(marker);
            });
            peta.addLayer(markerCluster);
        });
}

loadPeta();
document.getElementById('filterKondisiPeta').addEventListener('change', function() {
    loadPeta(this.value);
});
</script>
@endpush
