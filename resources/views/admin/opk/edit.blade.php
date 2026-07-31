@extends('layouts.app')
@section('title', 'Edit OPK')
@section('page-title', 'Edit Data OPK')

@php
    $fotoCount = $laporan->fotos->count();
    $videoCount = $laporan->videos->count();
    $dokumenCount = $laporan->dokumens->count();
    $bahasaOptions = ['Bahasa Bali (Alus)','Bahasa Bali (Madya)','Bahasa Bali (Kasar)','Bahasa Kawi / Jawa Kuno','Bahasa Sanskerta','Bahasa Indonesia','Multibahasa','Tidak ada / non-verbal'];
    $aksaraOptions = ['Aksara Bali','Aksara Latin','Aksara Jawa','Tidak ada / lisan'];
    $frekuensiOptions = [
        'rutin_harian' => 'Rutin — setiap hari / minggu',
        'rutin_bulanan' => 'Rutin — setiap bulan',
        'rutin_6bulanan' => 'Rutin — setiap 6 bulan / Odalan',
        'rutin_tahunan' => 'Rutin — setiap tahun',
        'langka' => 'Langka — beberapa tahun sekali',
        'sangat_langka' => 'Sangat langka — sudah jarang',
        'tidak_ada' => 'Sudah tidak dilaksanakan',
    ];
    $kepemilikanOptions = [
        'desa_adat' => 'Milik Desa Adat / Komunal',
        'pura_keagamaan' => 'Milik Pura / Lembaga Keagamaan',
        'pribadi_keluarga' => 'Milik Pribadi / Keluarga',
        'negara_pemerintah' => 'Milik Negara / Pemerintah',
        'tidak_jelas' => 'Tidak jelas / sengketa',
    ];
    $pelindunganOptions = [
        'belum_terdaftar' => 'Belum Terdaftar',
        'sudah_didata_dinas' => 'Sudah Didata Dinas',
        'sk_kabupaten' => 'SK Kabupaten',
        'sk_provinsi' => 'SK Provinsi',
        'wbtb_nasional' => 'WBTB Nasional',
    ];
    $tipePelaporOptions = [
        'masyarakat' => 'Masyarakat Umum',
        'tokoh_adat' => 'Tokoh Adat / Praktisi',
        'petugas_dinas' => 'Petugas Dinas',
    ];
@endphp

@section('content')
<x-ui.back-link href="{{ route('admin.opk.show', $laporan) }}" label="Kembali ke Detail" />

<div class="row g-3">
    <div class="col-12 col-md-8">
        <div class="card">
            <div class="card-header-custom">
                <span class="title">Edit: {{ Str::limit($laporan->nama_opk, 40) }}</span>
                <span class="text-abu t-caption">{{ $laporan->kode_laporan }}</span>
            </div>
            <div class="card-body">
                @if($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-3 t-body">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Gagal menyimpan.</strong> Periksa kembali isian berikut:
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('admin.opk.update', $laporan) }}" id="form-update-opk" enctype="multipart/form-data"
                      data-api-desa-dinas="{{ route('api.desa-dinas') }}"
                      data-foto-count="{{ $fotoCount }}"
                      data-video-count="{{ $videoCount }}"
                      data-dokumen-count="{{ $dokumenCount }}"
                      data-max-foto="10">
                    @csrf @method('PUT')

                    {{-- ===== SECTION: Identitas OPK ===== --}}
                    <div class="section-heading">
                        <i class="bi bi-building me-2"></i>Identitas OPK
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama OPK <span class="text-required">*</span></label>
                        <input type="text" name="nama_opk" class="form-control @error('nama_opk') is-invalid @enderror"
                               value="{{ old('nama_opk', $laporan->nama_opk) }}">
                        @error('nama_opk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jenis OPK <span class="text-required">*</span></label>
                            <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                                <option value="">— Pilih Jenis —</option>
                                @foreach($kategori as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_id', $laporan->kategori_id) == $kat->id ? 'selected' : '' }}>
                                    {{ $kat->ikon }} {{ $kat->nomor }}. {{ $kat->nama }}
                                </option>
                                @endforeach
                            </select>
                            @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Diketahui</label>
                            <input type="text" name="tahun_keterangan" class="form-control"
                                   value="{{ old('tahun_keterangan', $laporan->tahun_keterangan) }}" placeholder="Contoh: 1920, abad ke-17">
                            <input type="hidden" name="tahun_diketahui" value="{{ old('tahun_diketahui', $laporan->tahun_diketahui) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-0">
                        <div class="col-md-6">
                            <label class="form-label">Kondisi <span class="text-required">*</span></label>
                            <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror">
                                @foreach($kondisiList as $k)
                                <option value="{{ $k->kode }}" {{ old('kondisi', $laporan->kondisi) === $k->kode ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                            @error('kondisi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Pelindungan <span class="text-required">*</span></label>
                            <select name="status_pelindungan" class="form-select @error('status_pelindungan') is-invalid @enderror">
                                @foreach($pelindunganOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('status_pelindungan', $laporan->status_pelindungan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status_pelindungan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ===== SECTION: Lokasi & Wilayah ===== --}}
                    <div class="section-heading">
                        <i class="bi bi-geo-alt me-2"></i>Lokasi & Wilayah
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan <span class="text-required">*</span></label>
                            <select name="kecamatan_id" id="selKecamatan" class="form-select @error('kecamatan_id') is-invalid @enderror">
                                <option value="">— Pilih Kecamatan —</option>
                                @foreach($kecamatans as $kec)
                                <option value="{{ $kec->id }}" {{ old('kecamatan_id', $laporan->kecamatan_id) == $kec->id ? 'selected' : '' }}>
                                    {{ $kec->nama }}
                                </option>
                                @endforeach
                            </select>
                            @error('kecamatan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Desa Dinas <span class="text-required">*</span></label>
                            <select name="desa_dinas_id" id="selDesaDinas" class="form-select @error('desa_dinas_id') is-invalid @enderror">
                                <option value="">— Pilih Kecamatan Dulu —</option>
                                @if($laporan->desaDinas)
                                <option value="{{ $laporan->desa_dinas_id }}" selected>{{ $laporan->desaDinas->nama }}</option>
                                @endif
                            </select>
                            @error('desa_dinas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Desa Adat <span class="text-required">*</span></label>
                            <input type="text" name="nama_desa_adat" class="form-control @error('nama_desa_adat') is-invalid @enderror"
                                   value="{{ old('nama_desa_adat', $laporan->nama_desa_adat) }}" placeholder="Contoh: Desa Adat Kuta">
                            @error('nama_desa_adat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Banjar Adat</label>
                            <input type="text" name="banjar_adat" class="form-control"
                                   value="{{ old('banjar_adat', $laporan->banjar_adat) }}" placeholder="Nama banjar jika diketahui">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Lokasi Spesifik</label>
                        <input type="text" name="lokasi_spesifik" class="form-control"
                               value="{{ old('lokasi_spesifik', $laporan->lokasi_spesifik) }}" placeholder="Contoh: Pura Dalem Desa Adat Kuta">
                    </div>

                    <div class="row g-3 mb-0">
                        <div class="col-md-6">
                            <label class="form-label">Latitude (GPS)</label>
                            <input type="text" name="latitude" class="form-control"
                                   value="{{ old('latitude', $laporan->latitude) }}" placeholder="-8.xxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude (GPS)</label>
                            <input type="text" name="longitude" class="form-control"
                                   value="{{ old('longitude', $laporan->longitude) }}" placeholder="115.xxxxxx">
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ===== SECTION: Deskripsi & Detail ===== --}}
                    <div class="section-heading">
                        <i class="bi bi-file-text me-2"></i>Deskripsi & Detail
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi Umum <span class="text-required">*</span></label>
                        <textarea name="deskripsi_umum" class="form-control @error('deskripsi_umum') is-invalid @enderror" rows="4"
                            placeholder="Ceritakan tentang OPK ini — apa itu, bagaimana penampilannya, apa fungsinya dalam masyarakat Bali...">{{ old('deskripsi_umum', $laporan->deskripsi_umum) }}</textarea>
                        <div class="form-text">Minimal 10 karakter</div>
                        @error('deskripsi_umum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sejarah & Asal-Usul</label>
                        <textarea name="sejarah_asal_usul" class="form-control" rows="3"
                            placeholder="Kapan dan bagaimana OPK ini mulai ada? Tokoh atau peristiwa penting?">{{ old('sejarah_asal_usul', $laporan->sejarah_asal_usul) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai & Makna Budaya</label>
                        <textarea name="nilai_makna_budaya" class="form-control" rows="3"
                            placeholder="Apa makna OPK ini bagi masyarakat? Terkait upacara adat tertentu?">{{ old('nilai_makna_budaya', $laporan->nilai_makna_budaya) }}</textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Bahasa yang Digunakan</label>
                            <select name="bahasa_digunakan" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach($bahasaOptions as $b)
                                <option value="{{ $b }}" {{ old('bahasa_digunakan', $laporan->bahasa_digunakan) === $b ? 'selected' : '' }}>{{ $b }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aksara yang Digunakan</label>
                            <select name="aksara_digunakan" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach($aksaraOptions as $a)
                                <option value="{{ $a }}" {{ old('aksara_digunakan', $laporan->aksara_digunakan) === $a ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Frekuensi Pelaksanaan</label>
                            <select name="frekuensi_pelaksanaan" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach($frekuensiOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('frekuensi_pelaksanaan', $laporan->frekuensi_pelaksanaan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Kepemilikan</label>
                            <select name="status_kepemilikan" class="form-select">
                                <option value="">— Pilih —</option>
                                @foreach($kepemilikanOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('status_kepemilikan', $laporan->status_kepemilikan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="info-callout mb-0">
                        <div class="t-label mb-3">Informasi Praktisi / Narasumber</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nama Praktisi</label>
                                <input type="text" name="praktisi_nama" class="form-control"
                                    value="{{ old('praktisi_nama', $laporan->praktisi_nama) }}" placeholder="Nama tokoh / seniman">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Usia (perkiraan)</label>
                                <input type="number" name="praktisi_usia" class="form-control"
                                    value="{{ old('praktisi_usia', $laporan->praktisi_usia) }}" placeholder="Contoh: 65" min="1" max="120">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kontak</label>
                                <input type="text" name="praktisi_kontak" class="form-control"
                                    value="{{ old('praktisi_kontak', $laporan->praktisi_kontak) }}" placeholder="No. HP">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ===== SECTION: Foto, Video & Dokumen ===== --}}
                    <div class="section-heading">
                        <i class="bi bi-images me-2"></i>Foto, Video & Dokumen
                    </div>

                    {{-- Foto --}}
                    <div class="mb-4">
                        <label class="form-label mb-2">
                            Foto (<span id="foto-count">{{ $fotoCount }}</span>/10)
                        </label>

                        @if($fotoCount > 0)
                        <div class="d-flex flex-wrap gap-sm mb-12">
                            @foreach($laporan->fotos as $foto)
                            <div class="edit-foto-thumb {{ $foto->is_utama ? 'edit-foto-thumb-utama' : 'edit-foto-thumb-normal' }}">
                                <img src="{{ asset('storage/'.$foto->path) }}" alt="">
                                <button type="button" class="edit-foto-remove btn-hapus-foto" data-id="{{ $foto->id }}" title="Hapus foto">&times;</button>
                                @if($foto->is_utama)
                                <span class="edit-foto-utama-badge">UTAMA</span>
                                @else
                                <button type="button" class="edit-foto-set-utama btn-jadikan-utama" data-id="{{ $foto->id }}" title="Jadikan foto utama">&#9733;</button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-abu mb-12">Belum ada foto.</p>
                        @endif

                        <label class="btn btn-outline-secondary btn-sm cursor-pointer" id="btn-tambah-foto">
                            <i class="bi bi-plus me-1"></i>Tambah Foto
                            <input type="file" id="input-fotos" name="fotos[]" accept=".jpg,.jpeg,.png" multiple
                                   class="d-none" onchange="handleFotoChange(this)">
                        </label>
                        <small id="foto-quota-warn" class="text-danger d-none ms-2">Maksimal 10 foto tercapai</small>
                        @error('fotos')<div class="text-danger d-block">{{ $message }}</div>@enderror
                        @error('fotos.*')<div class="text-danger d-block">{{ $message }}</div>@enderror
                        <div id="foto-preview" class="d-flex flex-wrap gap-sm mt-2"></div>
                        <small class="text-abu">Max 2MB per file. JPG/PNG. Maksimal 10 foto.</small>
                    </div>

                    <input type="hidden" name="hapus_foto_ids" id="hapus_foto_ids" value="">
                    <input type="hidden" name="foto_utama_id" id="foto_utama_id" value="">

                    {{-- Video --}}
                    <div class="mb-4">
                        <label class="form-label mb-2">
                            Video (<span id="video-count">{{ $videoCount }}</span>)
                        </label>

                        @if($videoCount > 0)
                        <div id="video-list" class="mb-12">
                            @foreach($laporan->videos as $video)
                            <div class="edit-media-item video-item-existing">
                                <i class="bi bi-play-circle text-hijau"></i>
                                <span class="edit-media-item-name t-caption">{{ $video->link_eksternal }}</span>
                                <button type="button" class="edit-media-item-remove btn-hapus-video btn btn-sm" data-id="{{ $video->id }}" title="Hapus video">&times;</button>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-abu mb-12">Belum ada video.</p>
                        @endif

                        <div id="video-inputs">
                            <div class="input-group input-group-sm mb-2 video-link-row">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="url" name="video_links[]" class="form-control" placeholder="https://youtube.com/watch?v=...">
                            </div>
                        </div>
                        <button type="button" id="btn-tambah-video" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plus me-1"></i>Tambah Link Video
                        </button>
                        <small class="text-abu ms-2">YouTube / Google Drive</small>
                        @error('video_links.*')<div class="text-danger d-block">{{ $message }}</div>@enderror
                    </div>

                    <input type="hidden" name="hapus_video_ids" id="hapus_video_ids" value="">

                    {{-- Dokumen --}}
                    <div class="mb-0">
                        <label class="form-label mb-2">
                            Dokumen Pendukung (<span id="dokumen-count">{{ $dokumenCount }}</span>)
                        </label>

                        @if($dokumenCount > 0)
                        <div id="dokumen-list" class="mb-12">
                            @foreach($laporan->dokumens as $dok)
                            <div class="edit-media-item dokumen-item-existing">
                                <i class="bi bi-file-earmark-text text-emas"></i>
                                <span class="t-caption">{{ $dok->nama_file }}</span>
                                <small class="text-abu">{{ $dok->ukuran_bytes ? number_format($dok->ukuran_bytes / 1024).' KB' : '' }}</small>
                                <a href="{{ $dok->url }}" target="_blank" title="Lihat" class="text-hijau"><i class="bi bi-eye"></i></a>
                                <button type="button" class="edit-media-item-remove btn-hapus-dokumen btn btn-sm" data-id="{{ $dok->id }}" title="Hapus dokumen">&times;</button>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-abu mb-12">Belum ada dokumen.</p>
                        @endif

                        <label class="btn btn-outline-secondary btn-sm cursor-pointer">
                            <i class="bi bi-plus me-1"></i>Tambah Dokumen
                            <input type="file" name="dokumen_baru[]" accept=".pdf,.doc,.docx" multiple
                                   class="d-none" onchange="handleDokumenChange(this)">
                        </label>
                        <small class="text-abu ms-2">PDF / DOC · Max 20MB per file</small>
                        <div id="dokumen-preview" class="mt-2"></div>
                        @error('dokumen_baru.*')<div class="text-danger d-block">{{ $message }}</div>@enderror
                    </div>

                    <input type="hidden" name="hapus_dokumen_ids" id="hapus_dokumen_ids" value="">

                    <hr class="my-4">

                    {{-- ===== SECTION: Data Pelapor ===== --}}
                    <div class="section-heading">
                        <i class="bi bi-person-check me-2"></i>Data Pelapor
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saya melaporkan sebagai <span class="text-required">*</span></label>
                        <div class="row g-2">
                            @foreach($tipePelaporOptions as $val => $label)
                            <div class="col-6 col-sm-4">
                                <label class="edit-tipe-label {{ old('tipe_pelapor', $laporan->tipe_pelapor) === $val ? 'selected' : '' }}" id="lbl-{{ $val }}" onclick="setTipe(this,'{{ $val }}')">
                                    <input type="radio" name="tipe_pelapor" value="{{ $val }}"
                                        {{ old('tipe_pelapor', $laporan->tipe_pelapor) === $val ? 'checked' : '' }}
                                        class="accent-emas">
                                    <span class="fw-semibold">{{ $label }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('tipe_pelapor')<div class="text-danger d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-required">*</span></label>
                            <input type="text" name="pelapor_nama" class="form-control @error('pelapor_nama') is-invalid @enderror"
                                value="{{ old('pelapor_nama', $laporan->pelapor_nama) }}" placeholder="Nama sesuai KTP">
                            @error('pelapor_nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK <span class="text-required">*</span></label>
                            <input type="text" name="pelapor_nik" class="form-control @error('pelapor_nik') is-invalid @enderror"
                                value="{{ old('pelapor_nik', $laporan->pelapor_nik) }}" placeholder="16 digit NIK KTP" maxlength="16">
                            @error('pelapor_nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">No. WhatsApp <span class="text-required">*</span></label>
                            <input type="text" name="pelapor_whatsapp" class="form-control @error('pelapor_whatsapp') is-invalid @enderror"
                                value="{{ old('pelapor_whatsapp', $laporan->pelapor_whatsapp) }}" placeholder="08xxxxxxxxxx">
                            @error('pelapor_whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="pelapor_email" class="form-control"
                                value="{{ old('pelapor_email', $laporan->pelapor_email) }}" placeholder="email@domain.com">
                        </div>
                    </div>

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

    {{-- RIGHT SIDEBAR --}}
    <div class="col-12 col-md-4 mt-3 mt-md-0">
        <div class="card mb-3">
            <div class="card-header-custom"><span class="title">Info Laporan</span></div>
            <div class="card-body p-0">
                <div class="px-3">
                    <x-ui.info-rows :rows="[
                        ['Kode', $laporan->kode_laporan],
                        ['Tgl Lapor', $laporan->created_at->isoFormat('D MMM Y')],
                        ['Tgl Diperbarui', $laporan->updated_at->isoFormat('D MMM Y, HH:mm')],
                        ['Status', ucwords(str_replace('_',' ',$laporan->status_verifikasi))],
                    ]" key-width="90px" />
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header-custom"><span class="title">Navigasi Cepat</span></div>
            <div class="card-body">
                <ul class="list-unstyled" style="line-height:2.2;">
                    <li><a href="#" onclick="document.querySelector('[name=nama_opk]').focus();return false;" class="edit-nav-link"><i class="bi bi-building me-1"></i>Identitas OPK</a></li>
                    <li><a href="#" onclick="document.querySelector('[name=kecamatan_id]').focus();return false;" class="edit-nav-link"><i class="bi bi-geo-alt me-1"></i>Lokasi & Wilayah</a></li>
                    <li><a href="#" onclick="document.querySelector('[name=deskripsi_umum]').focus();return false;" class="edit-nav-link"><i class="bi bi-file-text me-1"></i>Deskripsi & Detail</a></li>
                    <li><a href="#" onclick="document.querySelector('#input-fotos').closest('label').scrollIntoView({behavior:'smooth'});return false;" class="edit-nav-link"><i class="bi bi-images me-1"></i>Foto, Video & Dokumen</a></li>
                    <li><a href="#" onclick="document.querySelector('[name=pelapor_nama]').focus();return false;" class="edit-nav-link"><i class="bi bi-person-check me-1"></i>Data Pelapor</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
