@extends('layouts.app')
@section('title','Laporan & Statistik')
@section('page-title','Laporan & Statistik OPK')

@section('content')
<x-ui.page-header title="Laporan &amp; Statistik" subtitle="Rekap data OPK Kabupaten Badung" action-label="Export CSV" action-url="{{ route('admin.laporan.export') }}" action-icon="bi-download" />

{{-- KPI --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total OPK Resmi', $stats['total'], '', 'bi-collection', 'var(--emas)'],
        ['Kritis', $stats['kritis'], 'Perlu tindakan', 'bi-exclamation-triangle', 'var(--merah)'],
        ['Waspada', $stats['waspada'], 'Perlu pantau', 'bi-eye', 'var(--kuning)'],
        ['Baik', $stats['baik'], 'Terlindungi', 'bi-shield-check', 'var(--hijau)'],
        ['Menunggu Verif.', $stats['menunggu'], 'Antrian', 'bi-clock', 'var(--abu-gelap)'],
        ['Bulan Ini', $stats['bulan_ini'], 'Laporan baru', 'bi-calendar3', '#2980b9'],
    ] as [$label, $val, $sub, $icon, $color])
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card h-100" style="border-top:3px solid {{ $color }};">
            <div class="card-body text-center py-3">
                <i class="bi {{ $icon }}" style="font-size:1.3rem;color:{{ $color }};"></i>
                <div style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;color:var(--tanah);line-height:1;margin:4px 0;">{{ $val }}</div>
                <div style="color:var(--abu);text-transform:uppercase;letter-spacing:0.06em" class="t-caption">{{ $label }}</div>
                @if($sub)<div style="color:var(--abu);margin-top:2px" class="t-caption">{{ $sub }}</div>@endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    {{-- Tren Bulanan --}}
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-header-custom"><span class="title">Tren Laporan 6 Bulan Terakhir</span></div>
            <div class="card-body">
                <canvas id="chartTren" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Per Kategori (Chart) --}}
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <div class="card h-100">
            <div class="card-header-custom"><span class="title">Distribusi OPK per Jenis</span></div>
            <div class="card-body">
                <canvas id="chartKategori" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Per Kecamatan (Chart) --}}
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <div class="card h-100">
            <div class="card-header-custom"><span class="title">OPK per Kecamatan</span></div>
            <div class="card-body">
                <canvas id="chartKecamatan" height="220"></canvas>
            </div>
        </div>
    </div>

    {{-- Kondisi Breakdown --}}
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <div class="card h-100">
            <div class="card-header-custom"><span class="title">Kondisi OPK</span></div>
            <div class="card-body">
                <canvas id="chartKondisi" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Top Urgensi --}}
<div class="card mb-4">
    <div class="card-header-custom">
        <span class="title">Top 10 OPK Urgensi Tertinggi (AI Score)</span>
        <a href="{{ route('admin.opk.index') }}?kondisi=kritis" style="color:var(--emas)" class="t-caption">Lihat Semua →</a>
    </div>
    <div class="card-body p-0 table-responsive-si">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;width:40px;">#</th>
                    <th>Nama OPK</th>
                    <th>Jenis</th>
                    <th>Kecamatan</th>
                    <th>Kondisi</th>
                    <th>AI Score</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topUrgensi as $i => $opk)
                <tr>
                    <td style="padding-left:1.25rem;">
                        <div style="width:24px;height:24px;border-radius:50%;background:{{ $i < 3 ? 'var(--merah)' : ($i < 6 ? 'var(--kuning)' : 'var(--abu)') }};color:white;display:flex;align-items:center;justify-content:center;font-weight:700" class="t-caption">{{ $i+1 }}</div>
                    </td>
                    <td>
                        <a href="{{ route('admin.opk.show', $opk) }}" style="font-weight:600;color:var(--tanah);text-decoration:none" class="t-body">{{ $opk->nama_opk }}</a>
                    </td>
                    <td><x-ui.badge-kategori :ikon="$opk->kategori?->ikon" :nama="$opk->kategori?->nama" /></td>
                    <td class="t-body">{{ $opk->kecamatan?->nama }}</td>
                    <td><x-ui.badge-kondisi :kondisi="$opk->kondisi" /></td>
                    <td>
                        <span style="font-family:'Courier New',monospace;font-weight:700;color:{{ $opk->kondisi === 'kritis' ? 'var(--merah)' : 'var(--kuning)' }}">
                            {{ number_format($opk->ai_urgency_score, 1) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.opk.show', $opk) }}" class="btn-icon">
                            <i class="bi bi-eye" class="t-caption"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
const ds = {
    emas:   getComputedStyle(document.documentElement).getPropertyValue('--emas').trim() || 'var(--emas)',
    merah:  getComputedStyle(document.documentElement).getPropertyValue('--merah').trim() || 'var(--merah)',
    kuning: getComputedStyle(document.documentElement).getPropertyValue('--kuning').trim() || 'var(--kuning)',
    hijau:  getComputedStyle(document.documentElement).getPropertyValue('--hijau').trim() || 'var(--hijau)',
    tanah:  getComputedStyle(document.documentElement).getPropertyValue('--tanah').trim() || 'var(--tanah)',
};

Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size = 11;

// Tren Bulanan
new Chart(document.getElementById('chartTren'), {
    type: 'line',
    data: {
        labels: @json($tren->pluck('label')),
        datasets: [{
            label: 'Laporan Masuk',
            data: @json($tren->pluck('total')),
            borderColor: ds.emas,
            backgroundColor: ds.emas + '22',
            fill: true,
            tension: 0.3,
            pointRadius: 4,
            pointBackgroundColor: ds.emas,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// Per Kategori
new Chart(document.getElementById('chartKategori'), {
    type: 'bar',
    data: {
        labels: @json($perKategori->pluck('nama')),
        datasets: [{
            label: 'Total OPK',
            data: @json($perKategori->pluck('total')),
            backgroundColor: ds.emas + '99',
            borderColor: ds.emas,
            borderWidth: 1,
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});

// Per Kecamatan
new Chart(document.getElementById('chartKecamatan'), {
    type: 'bar',
    data: {
        labels: @json($perKecamatan->pluck('nama')),
        datasets: [{
            label: 'Kritis',
            data: @json($perKecamatan->pluck('kritis')),
            backgroundColor: ds.merah + '99',
        }, {
            label: 'Non-Kritis',
            data: @json($perKecamatan->map(fn($k) => $k->total - $k->kritis)),
            backgroundColor: ds.hijau + '99',
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { stacked: true }
        }
    }
});

// Kondisi
new Chart(document.getElementById('chartKondisi'), {
    type: 'doughnut',
    data: {
        labels: ['Kritis', 'Waspada', 'Baik'],
        datasets: [{
            data: [{{ $stats['kritis'] }}, {{ $stats['waspada'] }}, {{ $stats['baik'] }}],
            backgroundColor: [ds.merah + 'CC', ds.kuning + 'CC', ds.hijau + 'CC'],
            borderColor: 'white',
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true } }
        }
    }
});
</script>
@endpush
