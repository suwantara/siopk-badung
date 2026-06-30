@extends('layouts.publik')

@section('title', 'Lapor OPK — SIOPK Badung')

@section('content')
<div class="container-lapor">
    <div class="mb-4">
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;margin-bottom:4px;">
            Lapor Objek Pemajuan Kebudayaan
        </h1>
        <p style="color:var(--abu-gelap)" class="t-body">
            Bantu Pemkab Badung memetakan & melindungi warisan budaya Bali · UU No. 5 Tahun 2017
        </p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger mb-3">
        <i class="bi bi-exclamation-circle me-2"></i>
        <strong>Mohon perbaiki input berikut:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Step Nav --}}
    <div class="step-nav" id="stepNav">
        <div class="step-tab active" id="stab-1">
            <div class="step-num">Langkah 1</div>
            <div class="step-label">Identitas OPK</div>
        </div>
        <div class="step-tab" id="stab-2">
            <div class="step-num">Langkah 2</div>
            <div class="step-label">Lokasi & Wilayah</div>
        </div>
        <div class="step-tab" id="stab-3">
            <div class="step-num">Langkah 3</div>
            <div class="step-label">Deskripsi Detail</div>
        </div>
        <div class="step-tab" id="stab-4">
            <div class="step-num">Langkah 4</div>
            <div class="step-label">Foto & Media</div>
        </div>
        <div class="step-tab" id="stab-5">
            <div class="step-num">Langkah 5</div>
            <div class="step-label">Data Pelapor</div>
        </div>
    </div>

    <div class="d-flex gap-3 align-items-start">

        {{-- FORM --}}
        <div class="flex-grow-1">
        <form method="POST" action="{{ route('publik.lapor.store') }}"
              enctype="multipart/form-data" id="formLapor">
        @csrf

        {{-- ====== STEP 1 ====== --}}
        <div id="step-1" class="form-card">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--emas);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--tanah);flex-shrink:0;">1</div>
                <div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:700;">Identitas OPK</div>
                    <div style="color:var(--abu);" class="t-caption">Informasi dasar tentang OPK yang dilaporkan</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Objek Budaya <span class="required-star">*</span></label>
                <input type="text" name="nama_opk" class="form-control @error('nama_opk') is-invalid @enderror"
                       value="{{ old('nama_opk') }}"
                       placeholder="Contoh: Tari Legong Keraton, Lontar Usada, Gamelan Gambang...">
                <div class="form-text">Tulis nama lengkap dan spesifik jika diketahui</div>
                @error('nama_opk')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label class="form-label">Jenis OPK <span class="required-star">*</span></label>
                    <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror">
                        <option value="">— Pilih Jenis —</option>
                        @foreach($kategori as $kat)
                        <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                            {{ $kat->ikon }} {{ $kat->nomor }}. {{ $kat->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tahun Diketahui <span class="optional-badge">(opsional)</span></label>
                    <input type="text" name="tahun_keterangan" class="form-control"
                           value="{{ old('tahun_keterangan') }}"
                           placeholder="Contoh: 1920, abad ke-17, pra-kolonial...">
                    <div class="form-text">Perkiraan juga diterima</div>
                    <input type="hidden" name="tahun_diketahui" value="{{ old('tahun_diketahui') }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Status Pelindungan Saat Ini <span class="required-star">*</span></label>
                <select name="status_pelindungan" class="form-select @error('status_pelindungan') is-invalid @enderror">
                    <option value="belum_terdaftar" {{ old('status_pelindungan','belum_terdaftar') === 'belum_terdaftar' ? 'selected' : '' }}>Belum terdaftar / tidak diketahui</option>
                    <option value="sudah_didata_dinas" {{ old('status_pelindungan') === 'sudah_didata_dinas' ? 'selected' : '' }}>Sudah didata Dinas Kebudayaan</option>
                    <option value="sk_kabupaten" {{ old('status_pelindungan') === 'sk_kabupaten' ? 'selected' : '' }}>Sudah ada SK Penetapan Kabupaten</option>
                    <option value="sk_provinsi"  {{ old('status_pelindungan') === 'sk_provinsi'  ? 'selected' : '' }}>Sudah ada SK Penetapan Provinsi</option>
                    <option value="wbtb_nasional" {{ old('status_pelindungan') === 'wbtb_nasional' ? 'selected' : '' }}>Warisan Budaya Tak Benda Nasional</option>
                </select>
                @error('status_pelindungan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Kondisi Saat Ini <span class="required-star">*</span></label>
                <div class="row g-2">
                    <div class="col-6 col-sm-4">
                        <label class="kondisi-label" id="lbl-baik">
                            <input type="radio" name="kondisi" value="baik" {{ old('kondisi','baik') === 'baik' ? 'checked' : '' }} onchange="setKondisi('baik')">
                            <div>
                                <div style="font-weight:600" class="t-body"><i class="bi bi-check-circle-fill" style="color:var(--hijau);"></i> Baik</div>
                                <div style="color:var(--abu)" class="t-caption">Aktif & terpelihara</div>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="kondisi-label" id="lbl-waspada">
                            <input type="radio" name="kondisi" value="waspada" {{ old('kondisi') === 'waspada' ? 'checked' : '' }} onchange="setKondisi('waspada')">
                            <div>
                                <div style="font-weight:600" class="t-body"><i class="bi bi-exclamation-triangle-fill" style="color:var(--kuning);"></i> Waspada</div>
                                <div style="color:var(--abu)" class="t-caption">Praktisi berkurang</div>
                            </div>
                        </label>
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="kondisi-label" id="lbl-kritis">
                            <input type="radio" name="kondisi" value="kritis" {{ old('kondisi') === 'kritis' ? 'checked' : '' }} onchange="setKondisi('kritis')">
                            <div>
                                <div style="font-weight:600" class="t-body"><i class="bi bi-exclamation-circle-fill" style="color:var(--merah);"></i> Kritis</div>
                                <div style="color:var(--abu)" class="t-caption">Terancam punah</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="button" class="btn btn-emas px-4" onclick="goStep(2)">
                    Lanjut ke Lokasi <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

        {{-- ====== STEP 2 ====== --}}
        <div id="step-2" class="form-card" style="display:none;">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--emas);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--tanah);flex-shrink:0;">2</div>
                <div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:700;">Lokasi & Wilayah</div>
                    <div style="color:var(--abu);" class="t-caption">Wilayah administratif tempat OPK berada</div>
                </div>
            </div>

            <div class="wilayah-box mb-3">
                <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.8rem" class="t-caption">Wilayah Administratif</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Kecamatan <span class="required-star">*</span></label>
                        <select name="kecamatan_id" id="selKecamatan" class="form-select @error('kecamatan_id') is-invalid @enderror">
                            <option value="">— Pilih Kecamatan —</option>
                            @foreach($kecamatans as $kec)
                            <option value="{{ $kec->id }}" {{ old('kecamatan_id') == $kec->id ? 'selected' : '' }}>
                                {{ $kec->nama }}
                            </option>
                            @endforeach
                        </select>
                        @error('kecamatan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Desa Dinas <span class="required-star">*</span></label>
                        <select name="desa_dinas_id" id="selDesaDinas" class="form-select @error('desa_dinas_id') is-invalid @enderror">
                            <option value="">— Pilih Kecamatan Dulu —</option>
                        </select>
                        @error('desa_dinas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="desa-adat-box mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span style="font-weight:700;text-transform:uppercase;letter-spacing:0.08em" class="t-caption">Desa Adat (Desa Pakraman)</span>
                    <span style="background:var(--surface-emas-hover);color:var(--emas);padding:2px 8px;border-radius:10px;font-weight:600" class="t-caption">Khas Bali</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Desa Adat <span class="required-star">*</span></label>
                        <input type="text" name="nama_desa_adat" class="form-control @error('nama_desa_adat') is-invalid @enderror"
                               value="{{ old('nama_desa_adat') }}"
                               placeholder="Contoh: Desa Adat Kuta, Desa Adat Sempidi...">
                        @error('nama_desa_adat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Banjar Adat <span class="optional-badge">(opsional)</span></label>
                        <input type="text" name="banjar_adat" class="form-control"
                               value="{{ old('banjar_adat') }}" placeholder="Nama banjar jika diketahui">
                    </div>
                </div>
                <div class="form-text mt-2">Desa Adat dan Desa Dinas bisa berbeda — keduanya penting untuk pendataan OPK di Bali</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Lokasi Spesifik <span class="optional-badge">(opsional)</span></label>
                <input type="text" name="lokasi_spesifik" class="form-control"
                       value="{{ old('lokasi_spesifik') }}"
                       placeholder="Contoh: Pura Dalem Desa Adat Kuta, Balai Banjar Tegal...">
            </div>

            <div class="mb-3">
                <label class="form-label">Koordinat GPS <span class="optional-badge">(opsional — disarankan)</span></label>
                <div class="row g-2 align-items-end">
                    <div class="col">
                        <div class="form-text mb-1">Latitude</div>
                        <input type="text" name="latitude" id="inputLat" class="form-control"
                               value="{{ old('latitude') }}" placeholder="-8.6xxxxx">
                    </div>
                    <div class="col">
                        <div class="form-text mb-1">Longitude</div>
                        <input type="text" name="longitude" id="inputLng" class="form-control"
                               value="{{ old('longitude') }}" placeholder="115.1xxxxx">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm" onclick="ambilGPS()"
                                style="background:var(--hijau);color:white;border:none;padding:8px 14px;">
                            <i class="bi bi-geo-alt me-1"></i>Ambil GPS
                        </button>
                    </div>
                </div>
                <div class="form-text mt-1">GPS otomatis menggunakan lokasi perangkat Anda</div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(1)">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </button>
                <button type="button" class="btn btn-emas px-4" onclick="goStep(3)">
                    Lanjut ke Deskripsi <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

        {{-- ====== STEP 3 ====== --}}
        <div id="step-3" class="form-card" style="display:none;">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--emas);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--tanah);flex-shrink:0;">3</div>
                <div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:700;">Deskripsi & Detail</div>
                    <div style="color:var(--abu);" class="t-caption">Semakin lengkap, semakin mudah diverifikasi Dinas</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi Umum <span class="required-star">*</span></label>
                <textarea name="deskripsi_umum" class="form-control @error('deskripsi_umum') is-invalid @enderror"
                          rows="4" placeholder="Ceritakan tentang OPK ini — apa itu, bagaimana penampilannya, apa fungsinya dalam masyarakat Bali...">{{ old('deskripsi_umum') }}</textarea>
                <div class="form-text">Minimal 50 karakter</div>
                @error('deskripsi_umum')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Sejarah & Asal-Usul <span class="optional-badge">(opsional)</span></label>
                <textarea name="sejarah_asal_usul" class="form-control" rows="3"
                          placeholder="Kapan dan bagaimana OPK ini mulai ada? Tokoh atau peristiwa penting?">{{ old('sejarah_asal_usul') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Nilai & Makna Budaya <span class="optional-badge">(opsional)</span></label>
                <textarea name="nilai_makna_budaya" class="form-control" rows="3"
                          placeholder="Apa makna OPK ini bagi masyarakat? Terkait upacara adat tertentu?">{{ old('nilai_makna_budaya') }}</textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Bahasa yang Digunakan <span class="optional-badge">(opsional)</span></label>
                    <select name="bahasa_digunakan" class="form-select">
                        <option value="">— Pilih —</option>
                        @foreach(['Bahasa Bali (Alus)','Bahasa Bali (Madya)','Bahasa Bali (Kasar)','Bahasa Kawi / Jawa Kuno','Bahasa Sanskerta','Bahasa Indonesia','Multibahasa','Tidak ada / non-verbal'] as $b)
                        <option value="{{ $b }}" {{ old('bahasa_digunakan') === $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Aksara yang Digunakan <span class="optional-badge">(opsional)</span></label>
                    <select name="aksara_digunakan" class="form-select">
                        <option value="">— Pilih —</option>
                        @foreach(['Aksara Bali','Aksara Latin','Aksara Jawa','Tidak ada / lisan'] as $a)
                        <option value="{{ $a }}" {{ old('aksara_digunakan') === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Frekuensi Pelaksanaan <span class="optional-badge">(opsional)</span></label>
                    <select name="frekuensi_pelaksanaan" class="form-select">
                        <option value="">— Pilih —</option>
                        <option value="rutin_harian">Rutin — setiap hari / minggu</option>
                        <option value="rutin_bulanan">Rutin — setiap bulan</option>
                        <option value="rutin_6bulanan">Rutin — setiap 6 bulan / Odalan</option>
                        <option value="rutin_tahunan">Rutin — setiap tahun</option>
                        <option value="langka">Langka — beberapa tahun sekali</option>
                        <option value="sangat_langka">Sangat langka — sudah jarang</option>
                        <option value="tidak_ada">Sudah tidak dilaksanakan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status Kepemilikan <span class="optional-badge">(opsional)</span></label>
                    <select name="status_kepemilikan" class="form-select">
                        <option value="">— Pilih —</option>
                        <option value="desa_adat">Milik Desa Adat / Komunal</option>
                        <option value="pura_keagamaan">Milik Pura / Lembaga Keagamaan</option>
                        <option value="pribadi_keluarga">Milik Pribadi / Keluarga</option>
                        <option value="negara_pemerintah">Milik Negara / Pemerintah</option>
                        <option value="tidak_jelas">Tidak jelas / sengketa</option>
                    </select>
                </div>
            </div>

            <div style="background:var(--krem);border:1px solid var(--garis);border-radius:3px;padding:1rem;">
                <div style="font-weight:700;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.8rem" class="t-caption">
                    Informasi Praktisi / Narasumber
                    <span style="color:var(--abu);font-weight:400;text-transform:none;letter-spacing:0;margin-left:4px" class="t-caption">(opsional)</span>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" class="t-caption">Nama Praktisi</label>
                        <input type="text" name="praktisi_nama" class="form-control" value="{{ old('praktisi_nama') }}" placeholder="Nama tokoh / seniman">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" class="t-caption">Usia (perkiraan)</label>
                        <input type="number" name="praktisi_usia" class="form-control" value="{{ old('praktisi_usia') }}" placeholder="Contoh: 65" min="1" max="120">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" class="t-caption">Kontak</label>
                        <input type="text" name="praktisi_kontak" class="form-control" value="{{ old('praktisi_kontak') }}" placeholder="No. HP (jika bersedia)">
                    </div>
                </div>
                <div class="form-text mt-2">Data praktisi bersifat rahasia, hanya dapat diakses Dinas Kebudayaan</div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(2)">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </button>
                <button type="button" class="btn btn-emas px-4" onclick="goStep(4)">
                    Lanjut ke Foto <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

        {{-- ====== STEP 4 ====== --}}
        <div id="step-4" class="form-card" style="display:none;">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--emas);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--tanah);flex-shrink:0;">4</div>
                <div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:700;">Foto & Dokumentasi</div>
                    <div style="color:var(--abu);" class="t-caption">Upload hingga 10 foto · video · dokumen pendukung</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Foto OPK <span class="required-star">*</span> <span class="optional-badge">— minimal 1, maks 10 foto</span></label>
                <input type="file" name="fotos[]" id="inputFotos" multiple accept="image/*"
                       class="d-none @error('fotos') is-invalid @enderror" onchange="previewFotos(this)">
                <div class="foto-grid" id="fotoGrid">
                    <div class="foto-add" onclick="document.getElementById('inputFotos').click()">
                        <i class="bi bi-camera" style="font-size:1.4rem;color:var(--abu);"></i>
                        <span style="color:var(--abu)" class="t-caption">Tambah Foto</span>
                    </div>
                </div>
                <div class="form-text mt-1">Format: JPG, PNG, HEIC, WebP · Maks 10MB per foto</div>
                @error('fotos')<div class="text-danger" class="t-caption">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Keterangan Foto Utama <span class="optional-badge">(opsional)</span></label>
                <input type="text" name="keterangan_foto_utama" class="form-control"
                       value="{{ old('keterangan_foto_utama') }}"
                       placeholder="Contoh: Pertunjukan Tari Kecak di Pura Uluwatu, 2024">
            </div>

            <div class="mb-3">
                <label class="form-label">Link Video YouTube / Google Drive <span class="optional-badge">(opsional)</span></label>
                <input type="url" name="link_video" class="form-control @error('link_video') is-invalid @enderror"
                       value="{{ old('link_video') }}" placeholder="https://youtube.com/watch?v=...">
                @error('link_video')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Dokumen Pendukung <span class="optional-badge">(opsional)</span></label>
                <input type="file" name="dokumen" id="inputDokumen" accept=".pdf,.doc,.docx"
                       class="d-none" onchange="previewDokumen(this)">
                <div class="upload-zone" onclick="document.getElementById('inputDokumen').click()" id="dokZone">
                    <i class="bi bi-file-earmark-text" style="font-size:1.8rem;color:var(--abu);"></i>
                    <div style="color:var(--abu);margin-top:6px" class="t-body">SK, sertifikat, artikel, atau dokumen resmi</div>
                    <div style="color:var(--abu)" class="t-caption">PDF, DOC · Maks 20MB</div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(3)">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </button>
                <button type="button" class="btn btn-emas px-4" onclick="goStep(5)">
                    Lanjut ke Data Pelapor <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>

        {{-- ====== STEP 5 ====== --}}
        <div id="step-5" class="form-card" style="display:none;">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--emas);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--tanah);flex-shrink:0;">5</div>
                <div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.2rem;font-weight:700;">Data Pelapor</div>
                    <div style="color:var(--abu);" class="t-caption">Identitas untuk konfirmasi & notifikasi status laporan</div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Saya melaporkan sebagai <span class="required-star">*</span></label>
                <div class="row g-2">
                    <div class="col-6 col-sm-4">
                        <label class="tipe-label" id="lbl-masyarakat" onclick="setTipe(this,'masyarakat')">
                            <input type="radio" name="tipe_pelapor" value="masyarakat" {{ old('tipe_pelapor','masyarakat') === 'masyarakat' ? 'checked' : '' }} style="accent-color:var(--emas);">
                            <i class="bi bi-person" style="font-size:1.4rem;"></i>
                            <span style="font-weight:600;" class="t-caption">Masyarakat Umum</span>
                        </label>
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="tipe-label" id="lbl-tokoh_adat" onclick="setTipe(this,'tokoh_adat')">
                            <input type="radio" name="tipe_pelapor" value="tokoh_adat" {{ old('tipe_pelapor') === 'tokoh_adat' ? 'checked' : '' }} style="accent-color:var(--emas);">
                            <i class="bi bi-people" style="font-size:1.4rem;"></i>
                            <span style="font-weight:600;" class="t-caption">Tokoh Adat / Praktisi</span>
                        </label>
                    </div>
                    <div class="col-6 col-sm-4">
                        <label class="tipe-label" id="lbl-petugas_dinas" onclick="setTipe(this,'petugas_dinas')">
                            <input type="radio" name="tipe_pelapor" value="petugas_dinas" {{ old('tipe_pelapor') === 'petugas_dinas' ? 'checked' : '' }} style="accent-color:var(--emas);">
                            <i class="bi bi-building" style="font-size:1.4rem;"></i>
                            <span style="font-weight:600;" class="t-caption">Petugas Dinas</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap <span class="required-star">*</span></label>
                    <input type="text" name="pelapor_nama" class="form-control @error('pelapor_nama') is-invalid @enderror"
                           value="{{ old('pelapor_nama') }}" placeholder="Nama sesuai KTP">
                    @error('pelapor_nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">NIK <span class="required-star">*</span></label>
                    <input type="text" name="pelapor_nik" class="form-control @error('pelapor_nik') is-invalid @enderror"
                           value="{{ old('pelapor_nik') }}" placeholder="16 digit NIK KTP" maxlength="16">
                    @error('pelapor_nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">No. WhatsApp <span class="required-star">*</span></label>
                    <input type="text" name="pelapor_whatsapp" class="form-control @error('pelapor_whatsapp') is-invalid @enderror"
                           value="{{ old('pelapor_whatsapp') }}" placeholder="08xxxxxxxxxx">
                    <div class="form-text">Notifikasi status laporan dikirim ke sini</div>
                    @error('pelapor_whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="optional-badge">(opsional)</span></label>
                    <input type="email" name="pelapor_email" class="form-control"
                           value="{{ old('pelapor_email') }}" placeholder="email@domain.com">
                </div>
            </div>

            <div style="background:var(--krem);border:1px solid var(--garis);border-radius:3px;padding:1rem;margin-bottom:1rem;">
                <label style="display:flex;cursor:pointer;align-items:flex-start" class="gap-sm">
                    <input type="checkbox" name="setuju_1" value="1" {{ old('setuju_1') ? 'checked' : '' }}
                           style="margin-top:3px;accent-color:var(--emas);" required>
                    <span style="line-height:1.6" class="t-body">
                        Saya menyatakan bahwa informasi yang dilaporkan adalah <strong>benar dan dapat dipertanggungjawabkan</strong>. Saya memahami data ini akan diverifikasi oleh Dinas Kebudayaan Kabupaten Badung.
                    </span>
                </label>
            </div>
            <div style="background:var(--surface-emas-light);border:1px solid var(--border-emas);border-radius:3px;padding:1rem;margin-bottom:1.5rem;">
                <label style="display:flex;cursor:pointer;align-items:flex-start" class="gap-sm">
                    <input type="checkbox" name="setuju_2" value="1" {{ old('setuju_2') ? 'checked' : '' }}
                           style="margin-top:3px;accent-color:var(--emas);" required>
                    <span style="line-height:1.6" class="t-body">
                        Saya menyetujui data yang diinput digunakan untuk keperluan <strong>pelestarian budaya oleh Pemkab Badung</strong> sesuai UU No. 5 Tahun 2017 tentang Pemajuan Kebudayaan.
                    </span>
                </label>
            </div>

            <div class="d-flex justify-content-between gap-3">
                <button type="button" class="btn btn-outline-secondary px-4" onclick="goStep(4)">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </button>
                <button type="submit" class="btn btn-emas flex-grow-1 py-3" style="letter-spacing:0.05em" class="t-body-lg">
                    <i class="bi bi-send me-2"></i>Kirim Laporan OPK
                </button>
            </div>
        </div>

        </form>
        </div>

        {{-- SIDEBAR KANAN --}}
        <div class="lapor-sidebar d-none d-lg-block">
            <div style="background:white;border:1px solid var(--garis);border-radius:4px;padding:1rem;">
                <div style="font-weight:700;color:var(--tanah);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.75rem" class="t-caption">Progress</div>
                <div style="height:5px;background:var(--input-bg);border-radius:3px;margin-bottom:10px;">
                    <div id="progBar" style="height:100%;width:20%;background:var(--emas);border-radius:3px;transition:width 0.3s;"></div>
                </div>
                <div id="progList">
                    @foreach(['Identitas OPK','Lokasi & Wilayah','Deskripsi Detail','Foto & Media','Data Pelapor'] as $i => $label)
                    <div class="prog-item" id="prog-{{ $i+1 }}">
                        <div class="prog-dot" style="background:{{ $i === 0 ? 'var(--emas)' : 'var(--input-bg)' }};color:{{ $i === 0 ? 'var(--tanah)' : 'var(--abu)' }};">
                            {{ $i+1 }}
                        </div>
                        <span style="color:{{ $i === 0 ? 'var(--emas)' : 'var(--abu)' }};font-weight:{{ $i === 0 ? '600' : '400' }};">
                            {{ $label }}
                        </span>
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
let step = 1;
let maxReached = 1;

function validateStep(n) {
    const errors = [];
    const form = document.getElementById('formLapor');

    if (n >= 2) {
        const nama = form.querySelector('[name="nama_opk"]');
        const kategori = form.querySelector('[name="kategori_id"]');
        const kondisi = form.querySelector('[name="kondisi"]:checked');
        if (!nama.value.trim()) errors.push('Nama Objek Budaya wajib diisi.');
        if (!kategori.value)     errors.push('Jenis OPK wajib dipilih.');
        if (!kondisi)            errors.push('Kondisi OPK wajib dipilih.');
    }

    if (n >= 3) {
        const kec = form.querySelector('[name="kecamatan_id"]');
        const desa = form.querySelector('[name="desa_dinas_id"]');
        const adat = form.querySelector('[name="nama_desa_adat"]');
        if (!kec.value)    errors.push('Kecamatan wajib dipilih.');
        if (!desa.value)   errors.push('Desa Dinas wajib dipilih.');
        if (!adat.value.trim()) errors.push('Nama Desa Adat wajib diisi.');
    }

    if (n >= 4) {
        const deskripsi = form.querySelector('[name="deskripsi_umum"]');
        if (!deskripsi.value.trim() || deskripsi.value.trim().length < 50) {
            errors.push('Deskripsi Umum wajib diisi minimal 50 karakter.');
        }
    }

    // Step 4 — optional, allow through

    if (errors.length) {
        showStepErrors(errors);
        return false;
    }
    return true;
}

function showStepErrors(errors) {
    const existing = document.querySelector('.step-error-alert');
    if (existing) existing.remove();
    const card = document.getElementById('step-' + step);
    const alert = document.createElement('div');
    alert.className = 'step-error-alert alert alert-danger py-2 px-3 mb-3';
    alert.style.cssText = 'font-size:0.8rem;margin-top:-0.5rem;';
    alert.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i><strong>Lengkapi isian berikut:</strong><ul style="margin:4px 0 0 1rem;padding:0;">' +
        errors.map(e => '<li>' + e + '</li>').join('') + '</ul>';
    card.insertBefore(alert, card.firstChild);
    setTimeout(function() {
        if (alert.parentNode) alert.remove();
    }, 6000);
}

function goStep(n) {
    if (n > step && !validateStep(n)) {
        return;
    }

    if (n > maxReached) maxReached = n;

    for (let i = 1; i <= 5; i++) {
        document.getElementById('step-' + i).style.display = 'none';
        const tab = document.getElementById('stab-' + i);
        tab.classList.remove('active', 'done');
        if (i < n)  tab.classList.add('done');
    }
    document.getElementById('step-' + n).style.display = 'block';
    document.getElementById('stab-' + n).classList.add('active');
    step = n;

    document.getElementById('progBar').style.width = (Math.max(maxReached, n) * 20) + '%';

    for (let i = 1; i <= 5; i++) {
        const p = document.getElementById('prog-' + i);
        if (!p) continue;
        const dot = p.querySelector('.prog-dot');
        const lbl = p.querySelector('span');
        if (i <= maxReached) {
            dot.style.background = i < n ? 'var(--hijau)' : (i === n ? 'var(--emas)' : 'var(--hijau)');
            dot.style.color = i === n ? 'var(--tanah)' : 'white';
            dot.textContent = i < n ? '\u2713' : i;
            lbl.style.color = i < n ? 'var(--hijau)' : 'var(--emas)';
            lbl.style.fontWeight = '600';
        } else {
            dot.style.background = 'var(--input-bg)'; dot.style.color = 'var(--abu)'; dot.textContent = i;
            lbl.style.color = 'var(--abu)'; lbl.style.fontWeight = '400';
        }
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.querySelectorAll('#stepNav .step-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        const target = parseInt(this.id.replace('stab-', ''));
        if (target <= maxReached || target <= step) {
            goStep(target);
        }
    });
});

function setKondisi(val) {
    ['baik','waspada','kritis'].forEach(k => {
        const lbl = document.getElementById('lbl-' + k);
        lbl.style.borderColor = '';
        lbl.style.background = '';
    });
    const colors = { baik: ['var(--hijau)','rgba(45,90,39,0.06)'], waspada: ['var(--kuning)','rgba(212,160,23,0.06)'], kritis: ['var(--merah)','rgba(192,57,43,0.06)'] };
    const el = document.getElementById('lbl-' + val);
    el.style.borderColor = colors[val][0];
    el.style.background   = colors[val][1];
}

function setTipe(el, val) {
    document.querySelectorAll('.tipe-label').forEach(l => l.classList.remove('selected'));
    el.classList.add('selected');
}

function ambilGPS() {
    if (!navigator.geolocation) { alert('GPS tidak didukung perangkat ini.'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('inputLat').value = pos.coords.latitude.toFixed(8);
        document.getElementById('inputLng').value = pos.coords.longitude.toFixed(8);
    }, () => alert('Gagal mendapatkan GPS. Pastikan izin lokasi diaktifkan.'));
}

const MAX_FOTO_SIZE = 10 * 1024 * 1024; // 10MB

function formatSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
}

function previewFotos(input) {
    const grid = document.getElementById('fotoGrid');
    grid.querySelectorAll('.foto-thumb').forEach(el => el.remove());
    const warnEl = document.getElementById('fotoSizeWarning');
    if (warnEl) warnEl.remove();

    const files = Array.from(input.files).slice(0, 10);
    let hasOversized = false;

    files.forEach((file, i) => {
        const isTooBig = file.size > MAX_FOTO_SIZE;
        if (isTooBig) hasOversized = true;

        const reader = new FileReader();
        reader.onload = e => {
            const thumb = document.createElement('div');
            thumb.className = 'foto-thumb' + (isTooBig ? ' foto-toobig' : '');
            thumb.innerHTML = `<img src="${e.target.result}"><button type="button" class="foto-del" onclick="hapusFoto(${i})">✕</button><div class="foto-size">${formatSize(file.size)}</div>`;
            grid.insertBefore(thumb, grid.querySelector('.foto-add'));
        };
        reader.readAsDataURL(file);
    });

    if (hasOversized) {
        const warn = document.createElement('div');
        warn.id = 'fotoSizeWarning';
        warn.style.cssText = 'margin-top:8px;font-size:0.72rem;color:var(--merah);display:flex;align-items:center;gap:6px;';
        warn.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Foto yang ditandai merah melebihi 10MB dan akan ditolak. Silakan compress terlebih dahulu.';
        grid.parentNode.insertBefore(warn, grid.nextSibling);
    }
}

function hapusFoto(i) {
    document.getElementById('fotoGrid').querySelectorAll('.foto-thumb')[i]?.remove();
}

function previewDokumen(input) {
    if (input.files[0]) {
        document.getElementById('dokZone').innerHTML =
            `<i class="bi bi-file-earmark-check" style="font-size:1.8rem;color:var(--hijau);"></i>
             <div style="margin-top:6px" class="t-body">${input.files[0].name}</div>
             <div style="color:var(--abu)" class="t-caption">Klik untuk ganti</div>`;
    }
}

document.getElementById('selKecamatan')?.addEventListener('change', function() {
    const id = this.value;
    const sel = document.getElementById('selDesaDinas');
    sel.innerHTML = '<option value="">Memuat...</option>';
    if (!id) { sel.innerHTML = '<option value="">— Pilih Kecamatan Dulu —</option>'; return; }
    fetch(`{{ route('api.desa-dinas') }}?kecamatan_id=${id}`)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">— Pilih Desa Dinas —</option>';
            data.forEach(d => sel.innerHTML += `<option value="${d.id}">${d.nama}</option>`);
        });
});

document.addEventListener('DOMContentLoaded', () => {
    const checked = document.querySelector('[name="kondisi"]:checked');
    if (checked) setKondisi(checked.value);
    const tipe = document.querySelector('[name="tipe_pelapor"]:checked');
    if (tipe) document.getElementById('lbl-' + tipe.value)?.classList.add('selected');

    @if($errors->any())
        @php
            $errFields = array_keys($errors->messages());
            $s1 = ['nama_opk','kategori_id','kondisi','status_pelindungan'];
            $s2 = ['kecamatan_id','desa_dinas_id','nama_desa_adat'];
            $s3 = ['deskripsi_umum'];
            $s5 = ['pelapor_nama','pelapor_nik','pelapor_whatsapp'];
            $jumpStep = 1;
            foreach($errFields as $f) {
                if(in_array($f, $s2)) { $jumpStep = 2; break; }
                if(in_array($f, $s3)) { $jumpStep = 3; break; }
                if(in_array($f, $s5)) { $jumpStep = 5; break; }
            }
        @endphp
        goStep({{ $jumpStep }});
    @endif
});
</script>
@endpush
