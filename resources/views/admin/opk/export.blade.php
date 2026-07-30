@extends('layouts.app')
@section('title', 'Export Data OPK')
@section('page-title', 'Export Data OPK')

@push('styles')
<style>
.export-page { max-width: 900px; margin: 0 auto; }

.export-section {
    background: #fff;
    border: 1px solid var(--garis, #e9ecef);
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.export-section-heading {
    display: flex; align-items: center; gap: 0.5rem;
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem; font-weight: 700;
    color: var(--tanah, #5c4033);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--garis, #e9ecef);
}
.export-section-heading i { color: var(--emas, #ce9f3c); font-size: 1.1rem; }

.sheet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; }
.sheet-card {
    border: 1.5px solid var(--garis, #e9ecef);
    border-radius: 6px; padding: 0.75rem 1rem;
    cursor: pointer; transition: all 0.15s; user-select: none;
    position: relative;
}
.sheet-card:hover { border-color: var(--emas, #ce9f3c); background: var(--surface-emas-light, #fdf8f0); }
.sheet-card.selected { border-color: var(--emas, #ce9f3c); background: var(--surface-emas-light, #fdf8f0); }
.sheet-card input { display: none; }
.sheet-card-title { font-weight: 600; font-size: 0.9rem; color: var(--tanah, #5c4033); }
.sheet-card-desc { font-size: 0.75rem; color: var(--abu, #6c757d); margin-top: 0.15rem; }
.sheet-card i { margin-right: 0.4rem; color: var(--emas, #ce9f3c); }

.sheet-select-all { font-size: 0.75rem; }
.sheet-select-all a { color: var(--emas, #ce9f3c); cursor: pointer; text-decoration: none; }
.sheet-select-all a:hover { text-decoration: underline; }

.filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .filter-row { grid-template-columns: 1fr; } }

.filter-group label {
    display: block;
    font-size: 0.78rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.05em; color: var(--abu-gelap, #495057);
    margin-bottom: 0.35rem;
}

.dropdown-filter .dropdown-toggle {
    text-align: left; font-size: 0.875rem;
    display: flex; align-items: center; justify-content: space-between;
    white-space: normal; word-break: break-word;
}
.dropdown-filter .dropdown-toggle::after { flex-shrink: 0; }
.dropdown-filter .dropdown-menu {
    padding: 0.5rem 0; min-width: 100%; max-height: 240px;
    overflow-y: auto; font-size: 0.85rem;
}
.dropdown-filter .form-check { padding: 0.2rem 1rem 0.2rem 2rem; }
.dropdown-filter .form-check:hover { background: var(--surface-emas-light, #fdf8f0); }
.dropdown-filter .dropdown-menu .select-all-row {
    padding: 0.2rem 1rem 0.4rem;
}
.dropdown-filter .dropdown-menu .select-all-row a {
    font-size: 0.75rem; cursor: pointer; color: var(--emas, #ce9f3c);
}
.dropdown-filter .dropdown-menu .divider { border-top: 1px solid var(--garis, #e9ecef); margin: 0.25rem 0; }

.status-pills { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.status-pill {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.65rem; border-radius: 20px;
    font-size: 0.78rem; cursor: pointer; user-select: none;
    border: 1.5px solid var(--garis, #e9ecef);
    background: #fff; color: var(--text-muted, #6c757d);
    transition: all 0.15s;
}
.status-pill.active {
    background: var(--surface-emas-light, #fdf8f0);
    border-color: var(--emas, #ce9f3c); color: var(--tanah, #5c4033);
    font-weight: 500;
}
.status-pill:hover { border-color: var(--emas, #ce9f3c); }
.status-pill input { display: none; }

.date-row { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.date-row input[type="date"] { width: 155px; font-size: 0.85rem; }
.date-row span { color: var(--abu, #6c757d); font-size: 0.8rem; }

.btn-export {
    width: 100%; padding: 0.75rem; font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.03em;
}
.export-info { font-size: 0.75rem; color: var(--abu, #6c757d); text-align: center; margin-top: 0.5rem; }
.export-info strong { color: var(--tanah, #5c40333); }
</style>
@endpush

@section('content')
<x-ui.back-link href="{{ route('admin.opk.index') }}" label="Kembali ke Daftar OPK" />

<div class="export-page">
    <x-ui.page-header title="Export Data OPK" subtitle="Unduh data OPK sesuai filter dalam format Excel (.xlsx) multi-sheet" />

    <form method="POST" action="{{ route('admin.opk.export.download') }}" id="exportForm">
        @csrf

        {{-- SECTION 1: PILIH DATA --}}
        <div class="export-section">
            <div class="export-section-heading">
                <i class="bi bi-grid-3x3-gap"></i> Pilih Data yang Diexport
            </div>

            <div class="sheet-grid">
                @php
                $sheetOptions = [
                    'foto'    => ['icon' => 'bi-images',            'title' => 'Foto',            'desc' => 'Daftar foto setiap OPK'],
                    'dokumen' => ['icon' => 'bi-file-earmark-pdf',  'title' => 'Dokumen',         'desc' => 'Daftar dokumen setiap OPK'],
                    'video'   => ['icon' => 'bi-camera-video',      'title' => 'Video',           'desc' => 'Daftar video setiap OPK'],
                    'riwayat' => ['icon' => 'bi-clock-history',     'title' => 'Riwayat Status',  'desc' => 'Histori perubahan status'],
                    'ai'      => ['icon' => 'bi-robot',             'title' => 'AI Analysis',     'desc' => 'Skor &amp; rekomendasi AI'],
                ];
                @endphp

                <div class="sheet-card selected" data-sheet="utama">
                    <input type="checkbox" checked disabled>
                    <div><i class="bi bi-table"></i></div>
                    <div class="sheet-card-title">Data Utama</div>
                    <div class="sheet-card-desc">Selalu disertakan — nama, jenis, lokasi, kondisi</div>
                </div>

                @foreach($sheetOptions as $key => $opt)
                <label class="sheet-card selected">
                    <input type="checkbox" name="sheets[]" value="{{ $key }}" checked>
                    <div><i class="bi {{ $opt['icon'] }}"></i></div>
                    <div class="sheet-card-title">{{ $opt['title'] }}</div>
                    <div class="sheet-card-desc">{{ $opt['desc'] }}</div>
                </label>
                @endforeach
            </div>

            <div class="mt-2 sheet-select-all">
                <a onclick="toggleAllSheets(true)">Pilih Semua</a>
                <span class="text-muted mx-1">|</span>
                <a onclick="toggleAllSheets(false)">Hanya Data Utama</a>
            </div>
        </div>

        {{-- SECTION 2: FILTER --}}
        <div class="export-section">
            <div class="export-section-heading">
                <i class="bi bi-funnel"></i> Filter Data (Opsional)
                <span style="font-size:0.75rem;font-weight:400;color:var(--abu, #6c757d);font-family:Inter,sans-serif;margin-left:0.5rem;">— kosongkan untuk mengexport semua</span>
            </div>

            <div class="filter-row">
                {{-- Status Verifikasi — pill toggle --}}
                <div class="filter-group">
                    <label>Status Verifikasi</label>
                    <div class="status-pills">
                        @foreach($statusList as $val => $label)
                        <label class="status-pill {{ $val === 'disetujui' ? 'active' : '' }}" data-status="{{ $val }}">
                            <input type="checkbox" name="status_verifikasi[]" value="{{ $val }}" {{ $val === 'disetujui' ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Kategori — dropdown multi-checkbox --}}
                <div class="filter-group">
                    <label>Kategori (Jenis OPK)</label>
                    <div class="dropdown dropdown-filter">
                        <button class="form-select dropdown-toggle" type="button" data-bs-toggle="dropdown" data-label-suffix="Kategori">
                            Semua Kategori
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-all-row">
                                <a onclick="DropdownFilter.selectAll(this, 'kategori_id')">Pilih Semua</a>
                            </div>
                            <div class="divider"></div>
                            @foreach($kategori as $kat)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kategori_id[]" value="{{ $kat->id }}" id="kat_{{ $kat->id }}" checked>
                                <label class="form-check-label" for="kat_{{ $kat->id }}">{{ $kat->ikon }} {{ $kat->nama }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Kecamatan — dropdown multi-checkbox --}}
                <div class="filter-group">
                    <label>Kecamatan</label>
                    <div class="dropdown dropdown-filter">
                        <button class="form-select dropdown-toggle" type="button" data-bs-toggle="dropdown" data-label-suffix="Kecamatan">
                            Semua Kecamatan
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-all-row">
                                <a onclick="DropdownFilter.selectAll(this, 'kecamatan_id')">Pilih Semua</a>
                            </div>
                            <div class="divider"></div>
                            @foreach($kecamatans as $kec)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kecamatan_id[]" value="{{ $kec->id }}" id="kec_{{ $kec->id }}" checked>
                                <label class="form-check-label" for="kec_{{ $kec->id }}">{{ $kec->nama }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Kondisi — dropdown multi-checkbox --}}
                <div class="filter-group">
                    <label>Kondisi</label>
                    <div class="dropdown dropdown-filter">
                        <button class="form-select dropdown-toggle" type="button" data-bs-toggle="dropdown" data-label-suffix="Kondisi">
                            Semua Kondisi
                        </button>
                        <div class="dropdown-menu">
                            <div class="select-all-row">
                                <a onclick="DropdownFilter.selectAll(this, 'kondisi')">Pilih Semua</a>
                            </div>
                            <div class="divider"></div>
                            @foreach($kondisiList as $k)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kondisi[]" value="{{ $k->kode }}" id="kond_{{ $k->kode }}" checked>
                                <label class="form-check-label" for="kond_{{ $k->kode }}">
                                    <span class="pill-{{ $k->kode }}" style="display:inline-block;width:8px;height:8px;border-radius:50%;vertical-align:middle;margin-right:4px;"></span>
                                    {{ $k->nama }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tanggal --}}
            <div class="filter-group mt-3">
                <label>Rentang Tanggal Lapor</label>
                <div class="date-row">
                    <input type="date" name="tanggal_dari" class="form-control form-control-sm">
                    <span>sampai</span>
                    <input type="date" name="tanggal_sampai" class="form-control form-control-sm">
                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" onclick="clearFilters()">
                        <i class="bi bi-arrow-repeat me-1"></i>Reset Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- EXPORT BUTTON --}}
        <button type="submit" class="btn btn-emas btn-export" id="btnExport">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export ke Excel (.xlsx)
        </button>
        <div class="export-info">
            Data akan diunduh sebagai file Excel dengan <strong>sheet terpisah</strong> untuk setiap jenis data yang dipilih.
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// ===== Sheet Cards: click to toggle =====
document.querySelectorAll('.sheet-card').forEach(card => {
    card.addEventListener('click', function(e) {
        e.preventDefault();
        if (this.querySelector('input[disabled]')) return;
        const cb = this.querySelector('input[type="checkbox"]');
        cb.checked = !cb.checked;
        this.classList.toggle('selected', cb.checked);
    });
});

function toggleAllSheets(select) {
    document.querySelectorAll('.sheet-card').forEach(card => {
        const cb = card.querySelector('input[type="checkbox"]');
        if (!cb || cb.disabled) return;
        cb.checked = select;
        card.classList.toggle('selected', select);
    });
}

// ===== Status Pills: click to toggle =====
document.querySelectorAll('.status-pill').forEach(pill => {
    pill.addEventListener('click', function(e) {
        e.preventDefault();
        if (e.target.tagName === 'INPUT') return;
        const cb = this.querySelector('input[type="checkbox"]');
        cb.checked = !cb.checked;
        this.classList.toggle('active', cb.checked);
    });
});

// ===== Dropdown Multi-Select Filters =====
const DropdownFilter = {
    suffixFor(btn) {
        return btn.dataset.labelSuffix || '';
    },

    selectAll(link) {
        const menu = link.closest('.dropdown-menu');
        const inputs = menu.querySelectorAll('input[type="checkbox"]');
        const allChecked = Array.from(inputs).every(c => c.checked);
        inputs.forEach(c => { c.checked = !allChecked; });
        this.update(link);
    },

    update(el) {
        const dd = el.closest('.dropdown-filter');
        if (!dd) return;
        const btn = dd.querySelector('.dropdown-toggle');
        const inputs = dd.querySelectorAll('input[type="checkbox"]');
        const checked = Array.from(inputs).filter(c => c.checked);
        const total = inputs.length;
        const suffix = this.suffixFor(btn);
        if (!btn || total === 0) return;

        let label;
        if (checked.length === total) {
            label = btn.dataset.allText || ('Semua' + (suffix ? ' ' + suffix : ''));
        } else if (checked.length === 0) {
            label = 'Tidak ada yang dipilih';
        } else if (checked.length <= 2) {
            label = checked.map(c => {
                const l = c.closest('.form-check')?.querySelector('.form-check-label');
                return l ? l.textContent.trim() : '';
            }).filter(Boolean).join(', ');
        } else {
            label = checked.length + ' ' + suffix;
        }
        btn.textContent = label;
    }
};

document.querySelectorAll('.dropdown-filter').forEach(dd => {
    const btn = dd.querySelector('.dropdown-toggle');
    if (btn && !btn.dataset.allText) btn.dataset.allText = btn.textContent.trim();

    // Update label when any checkbox inside changes
    dd.addEventListener('change', function(e) {
        if (e.target.type === 'checkbox') DropdownFilter.update(dd);
    });

    // Prevent dropdown from closing on label/checkbox interaction
    dd.querySelector('.dropdown-menu').addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

// ===== Clear Filters =====
function clearFilters() {
    document.querySelectorAll('.status-pill').forEach(p => {
        p.querySelector('input').checked = false;
        p.classList.remove('active');
    });
    document.querySelectorAll('.dropdown-filter input[type="checkbox"]').forEach(c => { c.checked = true; });
    document.querySelector('input[name="tanggal_dari"]').value = '';
    document.querySelector('input[name="tanggal_sampai"]').value = '';
    document.querySelectorAll('.dropdown-filter').forEach(dd => DropdownFilter.update(dd));
}

// ===== Export Button Loading =====
document.getElementById('exportForm').addEventListener('submit', function() {
    const btn = document.getElementById('btnExport');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyiapkan file...';
    // Reset after download starts (page stays, no reload)
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = orig;
    }, 3000);
});
</script>
@endpush
