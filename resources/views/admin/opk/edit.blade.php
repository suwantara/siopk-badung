@extends('layouts.app')
@section('title', 'Edit OPK')
@section('page-title', 'Edit Data OPK')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.opk.show', $laporan) }}" style="font-size:0.8rem;color:var(--emas);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Detail
    </a>
</div>

<div class="row g-3">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header-custom">
                <span class="title">Edit: {{ Str::limit($laporan->nama_opk, 50) }}</span>
                <span style="font-size:0.72rem;color:var(--abu);">{{ $laporan->kode_laporan }}</span>
            </div>
            <div class="card-body">
                {{-- Validation Error Summary --}}
                @if($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:0.8rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Gagal menyimpan.</strong> Periksa kembali isian berikut:
                    <ul class="mb-0 mt-1" style="padding-left:1.2rem;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.opk.update', $laporan) }}" id="form-update-opk" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Nama OPK <span style="color:var(--merah)">*</span></label>
                        <input type="text" name="nama_opk" class="form-control @error('nama_opk') is-invalid @enderror"
                               value="{{ old('nama_opk', $laporan->nama_opk) }}">
                        @error('nama_opk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Kondisi <span style="color:var(--merah)">*</span></label>
                            <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror">
                                <option value="baik"    {{ old('kondisi',$laporan->kondisi) === 'baik'    ? 'selected' : '' }}>✅ Baik</option>
                                <option value="waspada" {{ old('kondisi',$laporan->kondisi) === 'waspada' ? 'selected' : '' }}>⚠️ Waspada</option>
                                <option value="kritis"  {{ old('kondisi',$laporan->kondisi) === 'kritis'  ? 'selected' : '' }}>🔴 Kritis</option>
                            </select>
                            @error('kondisi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Status Pelindungan <span style="color:var(--merah)">*</span></label>
                            <select name="status_pelindungan" class="form-select @error('status_pelindungan') is-invalid @enderror">
                                @foreach(['belum_terdaftar'=>'Belum Terdaftar','sudah_didata_dinas'=>'Sudah Didata Dinas','sk_kabupaten'=>'SK Kabupaten','sk_provinsi'=>'SK Provinsi','wbtb_nasional'=>'WBTB Nasional'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status_pelindungan',$laporan->status_pelindungan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status_pelindungan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Deskripsi Umum <span style="color:var(--merah)">*</span></label>
                        <textarea name="deskripsi_umum" class="form-control @error('deskripsi_umum') is-invalid @enderror" rows="4">{{ old('deskripsi_umum', $laporan->deskripsi_umum) }}</textarea>
                        @error('deskripsi_umum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Sejarah & Asal-Usul</label>
                        <textarea name="sejarah_asal_usul" class="form-control" rows="3">{{ old('sejarah_asal_usul', $laporan->sejarah_asal_usul) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Nilai & Makna Budaya</label>
                        <textarea name="nilai_makna_budaya" class="form-control" rows="3">{{ old('nilai_makna_budaya', $laporan->nilai_makna_budaya) }}</textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Latitude (GPS)</label>
                            <input type="text" name="latitude" class="form-control"
                                   value="{{ old('latitude', $laporan->latitude) }}" placeholder="-8.xxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Longitude (GPS)</label>
                            <input type="text" name="longitude" class="form-control"
                                   value="{{ old('longitude', $laporan->longitude) }}" placeholder="115.xxxxxx">
                        </div>
                    </div>

                    {{-- Foto Management --}}
                    @php $fotoCount = $laporan->fotos->count(); @endphp
                    <div class="mb-4">
                        <label class="form-label mb-2" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">
                            Foto (<span id="foto-count">{{ $fotoCount }}</span>/10)
                        </label>

                        @if($laporan->fotos->count() > 0)
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
                            @foreach($laporan->fotos as $foto)
                            <div style="position:relative;width:100px;height:100px;border-radius:4px;overflow:hidden;border:2px solid {{ $foto->is_utama ? 'var(--emas)' : 'var(--garis)' }};">
                                <img src="{{ asset('storage/'.$foto->path) }}" style="width:100%;height:100%;object-fit:cover;">
                                <button type="button" class="btn-hapus-foto" data-id="{{ $foto->id }}"
                                        style="position:absolute;top:2px;right:2px;background:rgba(220,53,69,0.9);color:white;border:none;border-radius:50%;width:22px;height:22px;font-size:12px;line-height:1;cursor:pointer;"
                                        title="Hapus foto">&times;</button>
                                @if($foto->is_utama)
                                <span style="position:absolute;bottom:0;left:0;right:0;background:var(--emas);color:white;font-size:0.55rem;text-align:center;padding:1px 0;">UTAMA</span>
                                @else
                                <button type="button" class="btn-jadikan-utama" data-id="{{ $foto->id }}"
                                        style="position:absolute;bottom:2px;left:2px;background:rgba(0,0,0,0.5);color:white;border:none;border-radius:4px;font-size:0.5rem;padding:1px 4px;cursor:pointer;"
                                        title="Jadikan foto utama">★</button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p style="font-size:0.78rem;color:var(--abu);margin-bottom:12px;">Belum ada foto.</p>
                        @endif

                        <label class="btn btn-outline-secondary btn-sm" style="cursor:pointer;" id="btn-tambah-foto">
                            <i class="bi bi-plus me-1"></i>Tambah Foto
                            <input type="file" id="input-fotos" name="fotos[]" accept=".jpg,.jpeg,.png" multiple
                                   style="display:none;" onchange="handleFotoChange(this)">
                        </label>
                        <small id="foto-quota-warn" class="text-danger" style="display:none;margin-left:8px;font-size:0.72rem;">Maksimal 10 foto tercapai</small>
                        @error('fotos')<div class="text-danger" style="font-size:0.72rem;">{{ $message }}</div>@enderror
                        @error('fotos.*')<div class="text-danger" style="font-size:0.72rem;">{{ $message }}</div>@enderror
                        <div id="foto-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;"></div>
                        <small style="color:var(--abu);">Max 2MB per file. JPG/PNG. Maksimal 10 foto.</small>
                    </div>

                    <input type="hidden" name="hapus_foto_ids" id="hapus_foto_ids" value="">
                    <input type="hidden" name="foto_utama_id" id="foto_utama_id" value="">

                </form>

                <div class="d-flex gap-3 mt-3">
                    <button type="submit" form="form-update-opk" class="btn btn-emas px-4">
                        <i class="bi bi-check2 me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.opk.show', $laporan) }}" class="btn btn-outline-secondary px-4">Batal</a>

                    @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('admin.opk.destroy', $laporan) }}" class="ms-auto"
                          onsubmit="event.preventDefault(); swalKonfirmasi({title:'Arsipkan OPK',text:'Arsipkan OPK ini? Data tidak dihapus permanen.',icon:'warning',confirmText:'Arsipkan',confirmColor:'#dc3545',onConfirm:()=>this.submit()})">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-archive me-1"></i>Arsipkan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4 mt-3 mt-md-0">
        <div class="card">
            <div class="card-header-custom"><span class="title">Info Laporan</span></div>
            <div class="card-body p-0">
                <div style="padding:0 1rem;">
                    @foreach([
                        ['Kode',       $laporan->kode_laporan],
                        ['Jenis OPK',  $laporan->kategori?->ikon.' '.$laporan->kategori?->nama],
                        ['Kecamatan',  $laporan->kecamatan?->nama],
                        ['Desa Adat',  $laporan->nama_desa_adat],
                        ['Pelapor',    $laporan->pelapor_nama],
                        ['Tgl Lapor',  $laporan->created_at->isoFormat('D MMM Y')],
                    ] as [$k,$v])
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--garis-terang);font-size:0.8rem;">
                        <span style="color:var(--abu);width:90px;flex-shrink:0;">{{ $k }}</span>
                        <span style="font-weight:500;text-align:right;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const MAX_FOTO = 10;
let existingCount = {{ $fotoCount }};
let deletedCount  = 0;
let newFileCount  = 0;
const hapusIdsSet = new Set();

function updateQuota() {
    const current = existingCount - deletedCount + newFileCount;
    document.getElementById('foto-count').textContent = current;
    document.getElementById('foto-count').style.color = current >= MAX_FOTO ? 'var(--merah)' : '';
    const warn = document.getElementById('foto-quota-warn');
    const btn  = document.getElementById('btn-tambah-foto');
    const input = document.getElementById('input-fotos');
    if (current >= MAX_FOTO) {
        warn.style.display = 'inline';
        btn.style.opacity = '0.4';
        btn.style.pointerEvents = 'none';
        input.disabled = true;
    } else {
        warn.style.display = 'none';
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
        input.disabled = false;
    }
}

function handleFotoChange(input) {
    const files = Array.from(input.files);
    const current = existingCount - deletedCount + newFileCount;
    const available = MAX_FOTO - current;
    if (files.length > available) {
        swalToast('warning', 'Maksimal ' + MAX_FOTO + ' foto. Hanya ' + available + ' slot tersisa.');
        input.value = '';
        return;
    }
    const preview = document.getElementById('foto-preview');
    preview.innerHTML = '';
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.style.cssText = 'position:relative;width:80px;height:80px;border-radius:4px;overflow:hidden;border:2px solid var(--garis);';
            div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;"><span style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.5);color:white;font-size:0.5rem;text-align:center;padding:1px 0;">BARU</span>';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    newFileCount = files.length;
    updateQuota();
}

document.querySelectorAll('.btn-hapus-foto').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        if (!hapusIdsSet.has(id)) {
            hapusIdsSet.add(id);
            deletedCount++;
        }
        document.getElementById('hapus_foto_ids').value = Array.from(hapusIdsSet).join(',');
        this.closest('div').style.opacity = '0.3';
        this.disabled = true;
        this.style.display = 'none';
        updateQuota();
    });
});

document.querySelectorAll('.btn-jadikan-utama').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('foto_utama_id').value = this.dataset.id;
        document.querySelectorAll('.btn-jadikan-utama').forEach(function(b) { b.textContent = '\u2605'; });
        this.textContent = '\u25CF';
    });
});

updateQuota();
</script>
@endpush
