@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Eksekutif')

@section('content')
<x-ui.page-header title="Dashboard OPK" subtitle="Pemantauan real-time Objek Pemajuan Kebudayaan · Kabupaten Badung" action-label="Tambah Laporan" action-url="{{ route('publik.lapor.index') }}" action-target="_blank" action-icon="bi-plus-circle" />

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card kpi-card h-100">
            <div class="card-body">
                <div class="kpi-label">Total OPK Terdaftar</div>
                <div class="kpi-value">{{ $stats['total_opk'] }}</div>
                <div class="kpi-sub d-none d-sm-block">Di 6 kecamatan Badung</div>
                <div class="mt-2" style="color:var(--hijau)" class="t-caption">
                    <i class="bi bi-arrow-up"></i> +{{ $stats['bulan_ini'] }} bulan ini
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card kpi-card kritis h-100">
            <div class="card-body">
                <div class="kpi-label">Butuh Perhatian</div>
                <div class="kpi-value text-danger">{{ $stats['kritis'] }}</div>
                <div class="kpi-sub">Status kritis / terancam</div>
                <div class="mt-2" style="color:#c0392b" class="t-caption">
                    <i class="bi bi-exclamation-triangle"></i> Perlu tindakan segera
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card kpi-card waspada h-100">
            <div class="card-body">
                <div class="kpi-label">Menunggu Verifikasi</div>
                <div class="kpi-value" style="color:var(--kuning)">{{ $stats['menunggu'] }}</div>
                <div class="kpi-sub">Laporan dari masyarakat</div>
                @if($stats['menunggu'] > 0)
                <div class="mt-2">
                    <a href="{{ route('admin.verifikasi.index') }}" style="color:var(--kuning)" class="t-caption">
                        Review sekarang →
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card kpi-card hijau h-100">
            <div class="card-body">
                <div class="kpi-label">OPK Terlindungi</div>
                <div class="kpi-value" style="color:var(--hijau)">{{ $stats['terlindungi'] }}</div>
                <div class="kpi-sub">Kondisi baik & aktif</div>
                <div class="mt-2" style="color:var(--hijau)" class="t-caption">
                    {{ $stats['total_opk'] > 0 ? round($stats['terlindungi']/$stats['total_opk']*100) : 0 }}% dari total terdaftar
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Peta + AI Panel --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-8">
        <div class="card h-100">
            <div class="card-header-custom">
                <span class="title"><i class="bi bi-map me-2 text-warning"></i>Peta Sebaran OPK</span>
                <div class="d-flex gap-2 align-items-center">
                    <select id="filterKondisiPeta" class="form-select form-select-sm" style="width:120px;">
                        <option value="">Semua Status</option>
                        <option value="kritis">Kritis</option>
                        <option value="waspada">Waspada</option>
                        <option value="baik">Baik</option>
                    </select>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="peta" style="height:360px;"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mt-3 mt-md-0">
        <div class="ai-panel h-100 p-3">
            <div class="d-flex align-items-center gap-2 mb-3 pb-2" style="border-bottom:1px solid var(--border-emas);">
                <span class="ai-blink" style="width:8px;height:8px;border-radius:50%;background:var(--emas-muda);display:inline-block;"></span>
                <span style="font-weight:700;color:var(--emas-muda);text-transform:uppercase;letter-spacing:0.1em" class="t-caption">AI · Rekomendasi Tindakan</span>
            </div>

            @if($prioritas->count() > 0)
                @php $top = $prioritas->first() @endphp
                <div style="background:rgba(192,57,43,0.15);border-left:3px solid var(--merah);padding:10px 12px;border-radius:0 3px 3px 0;margin-bottom:0.8rem;line-height:1.6" class="t-body">
                    <strong style="color:var(--emas-muda);">🔴 Prioritas Tinggi:</strong><br>
                    <span style="opacity:0.9;">{{ $top->nama_opk }} — Kec. {{ $top->kecamatan?->nama }}</span><br>
                    <span style="opacity:0.7" class="t-caption">AI Score: {{ number_format($top->ai_urgency_score, 1) }}/10</span>
                </div>
            @else
                <div style="opacity:0.7;margin-bottom:0.8rem" class="t-body">
                    Tidak ada OPK kritis terdeteksi saat ini.
                </div>
            @endif

            @if($stats['menunggu'] > 0)
            <div style="background:rgba(200,146,42,0.12);border-left:3px solid var(--emas);padding:10px 12px;border-radius:0 3px 3px 0;margin-bottom:0.8rem;line-height:1.6" class="t-body">
                <strong style="color:var(--emas-muda);">📋 Antrian Verifikasi:</strong><br>
                <span style="opacity:0.9;">{{ $stats['menunggu'] }} laporan masyarakat menunggu review Dinas.</span>
            </div>
            @endif

            <div class="d-flex flex-column gap-2 mt-3">
                <a href="{{ route('admin.verifikasi.index') }}"
                   style="background:var(--surface-emas-hover);border:1px solid var(--border-emas);color:var(--krem);padding:8px 12px;border-radius:3px;text-decoration:none;display:flex;align-items:center;justify-content:space-between" class="t-body">
                    <span>✓ Review Laporan Warga</span> <span style="opacity:0.5;">→</span>
                </a>
                <a href="{{ route('admin.opk.index') }}?kondisi=kritis"
                   style="background:var(--surface-emas-hover);border:1px solid var(--border-emas);color:var(--krem);padding:8px 12px;border-radius:3px;text-decoration:none;display:flex;align-items:center;justify-content:space-between" class="t-body">
                    <span>🔴 OPK Status Kritis</span> <span style="opacity:0.5;">→</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Prioritas Pemeliharaan --}}
<div class="row g-3">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header-custom">
                <span class="title">Prioritas Pemeliharaan <small class="text-muted fw-normal">(diurutkan AI Score)</small></span>
                <a href="{{ route('admin.opk.index') }}" style="color:var(--emas);" class="t-caption">Lihat Semua →</a>
            </div>
            <div class="card-body p-0 table-responsive-si">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="padding-left:1.25rem;">Nama OPK</th>
                            <th>Jenis</th>
                            <th>Kecamatan</th>
                            <th>Status</th>
                            <th>AI Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prioritas as $opk)
                        <tr>
                            <td style="padding-left:1.25rem;">
                                <a href="{{ route('admin.opk.show', $opk) }}" class="text-decoration-none fw-semibold" style="color:var(--tanah);">
                                    {{ $opk->nama_opk }}
                                </a>
                            </td>
                            <td><x-ui.badge-kategori :ikon="$opk->kategori?->ikon" :nama="$opk->kategori?->nama" /></td>
                            <td class="t-body">{{ $opk->kecamatan?->nama }}</td>
                            <td><x-ui.badge-kondisi :kondisi="$opk->kondisi" /></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <x-ui.ai-score :score="$opk->ai_urgency_score ?? 0" :kondisi="$opk->kondisi" />
                                    <div class="urgency-bar" style="width:50px;">
                                        <div class="urgency-fill" style="width:{{ ($opk->ai_urgency_score ?? 0) * 10 }}%;background:{{ $opk->kondisi === 'kritis' ? 'var(--merah)' : 'var(--kuning)' }}"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4" class="t-body">
                                <i class="bi bi-check-circle text-success me-2"></i>
                                Tidak ada OPK kritis atau waspada saat ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 mt-3 mt-md-0">
        <div class="card h-100">
            <div class="card-header-custom">
                <span class="title">OPK per Kecamatan</span>
            </div>
            <div class="card-body">
                @foreach($perKecamatan as $kec)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" class="t-body">
                        <span>{{ $kec->nama }}</span>
                        <strong>{{ $kec->total }}</strong>
                    </div>
                    <div class="urgency-bar">
                        @php $max = $perKecamatan->max('total') ?: 1 @endphp
                        <div class="urgency-fill" style="width:{{ $kec->total / $max * 100 }}%;background:var(--emas);"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
// Inisialisasi peta Leaflet
const peta = L.map('peta').setView([-8.65, 115.18], 11);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 18
}).addTo(peta);

// Baca warna dari design system
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

// Load data OPK dari API
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
