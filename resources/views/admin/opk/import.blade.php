@extends('layouts.app')
@section('title', 'Impor Data OPK')
@section('page-title', 'Impor Data OPK')

@section('content')
@php $result = session('import_result'); @endphp

<x-ui.page-header title="Impor Massal Data OPK" subtitle="Unggah file CSV/XLSX untuk menambah banyak data OPK sekaligus (tanpa entri manual)">
    <x-slot:action>
        <a href="{{ route('admin.opk.import.template', ['format' => 'xlsx']) }}" class="btn btn-sm btn-outline-success me-1">
            <i class="bi bi-file-earmark-excel me-1"></i>Template Excel
        </a>
        <a href="{{ route('admin.opk.import.template', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-filetype-csv me-1"></i>Template CSV
        </a>
    </x-slot:action>
</x-ui.page-header>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($result)
    @if($result['fatal'])
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Impor tidak dijalankan.</strong>
        <div class="mt-1" style="font-size:0.85rem;">{{ $result['fatal'] }}</div>
    </div>
    @elseif($result['dry_run'])
    <div class="alert alert-info">
        <strong><i class="bi bi-clipboard-check me-1"></i>Validasi selesai (mode uji coba).</strong>
        <div class="mt-1" style="font-size:0.85rem;">
            {{ number_format($result['total_read'], 0, ',', '.') }} baris dibaca
            @if($result['blank'] > 0)
                ({{ number_format($result['blank'], 0, ',', '.') }} baris kosong dilewati)
            @endif
            : <strong>{{ number_format($result['valid'], 0, ',', '.') }}</strong> valid,
            <strong>{{ number_format($result['invalid'], 0, ',', '.') }}</strong> bermasalah.
            Hapus centang "Validasi saja" untuk benar-benar mengimpor.
        </div>
    </div>
    @elseif($result['imported'] > 0)
    <div class="alert alert-success">
        <strong><i class="bi bi-check-circle me-1"></i>Impor berhasil.</strong>
        <div class="mt-1" style="font-size:0.85rem;">
            {{ number_format($result['imported'], 0, ',', '.') }} data OPK ditambahkan
            @if($result['invalid'] > 0)
                , {{ number_format($result['invalid'], 0, ',', '.') }} baris dilewati karena tidak valid
            @endif
            .
            <br>{{ number_format($result['total_read'], 0, ',', '.') }} baris dibaca@if($result['blank'] > 0), {{ number_format($result['blank'], 0, ',', '.') }} baris kosong dilewati@endif.
            @if($result['schedule_hint'])
                <br>{{ number_format($result['schedule_hint'], 0, ',', '.') }} data berstatus <em>menunggu</em> akan dianalisis AI oleh scheduler.
            @endif
        </div>
    </div>
    @else
    <div class="alert alert-warning">
        <strong><i class="bi bi-info-circle me-1"></i>Tidak ada data yang diimpor.</strong>
        <div class="mt-1" style="font-size:0.85rem;">
            {{ number_format($result['invalid'], 0, ',', '.') }} baris bermasalah ditemukan
            dari {{ number_format($result['total_read'], 0, ',', '.') }} baris yang dibaca@if($result['blank'] > 0), {{ number_format($result['blank'], 0, ',', '.') }} baris kosong dilewati@endif.
        </div>
    </div>
    @endif

    @if(!empty($result['errors']))
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong class="t-body"><i class="bi bi-list-check me-1"></i>Kesalahan ditemukan ({{ number_format($result['invalid'], 0, ',', '.') }} baris)</strong>
                @if($result['error_file'])
                <a href="{{ route('admin.opk.import.errors', $result['error_file']) }}" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-download me-1"></i>Unduh semua kesalahan (CSV)
                </a>
                @endif
            </div>
            @if($result['invalid'] > count($result['errors']))
            <p class="text-muted mb-2" style="font-size:0.78rem;">Menampilkan {{ count($result['errors']) }} baris pertama. Unduh CSV untuk daftar lengkap.</p>
            @endif
            <div class="table-responsive-si">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width:80px;">Baris</th>
                            <th>Masalah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['errors'] as $error)
                        <tr>
                            <td class="t-body">{{ $error['row'] }}</td>
                            <td class="t-body">{{ implode(' ', $error['messages']) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;font-weight:700;margin-bottom:0.75rem;">Unggah File</h2>

                <form action="{{ route('admin.opk.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label for="file" class="form-label t-caption" style="font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">File CSV / XLSX</label>
                        <input type="file" name="file" id="file" required accept=".csv,.txt,.xlsx"
                               class="form-control form-control-sm @error('file') is-invalid @enderror">
                        @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="text-muted mt-1" style="font-size:0.72rem;">Maksimal 10MB, maksimal {{ number_format($maxRows, 0, ',', '.') }} baris per file. Gunakan template resmi.</div>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="dry_run" id="dry_run" value="1">
                        <label class="form-check-label t-body" for="dry_run">
                            Validasi saja (uji coba, tidak menyimpan)
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="skip_invalid" id="skip_invalid" value="1">
                        <label class="form-check-label t-body" for="skip_invalid">
                            Lewati baris tidak valid, impor sisanya
                        </label>
                    </div>

                    <button type="submit" class="btn btn-sm btn-emas w-100">
                        <i class="bi bi-upload me-1"></i>Mulai Impor
                    </button>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.1rem;font-weight:700;margin-bottom:0.5rem;">Catatan Penting</h2>
                <ul class="mb-0 t-body" style="font-size:0.8rem;padding-left:1.1rem;">
                    <li>Data diimpor langsung berstatus <strong>disetujui</strong> (kecuali kolom Status Verifikasi diisi <em>menunggu</em>).</li>
                    <li>Tidak memicu notifikasi WhatsApp dan tidak menjalankan analisis AI — aman untuk ribuan baris.</li>
                    <li>Proses memakai <strong>bulk insert per 100 baris dalam satu transaksi</strong>; jika gagal di tengah, tidak ada data setengah jadi.</li>
                    <li>Hanya satu proses impor dapat berjalan bersamaan.</li>
                    <li>Baris duplikat (nama sama di kecamatan sama) otomatis ditolak.</li>
                    <li>NIK &amp; WhatsApp Pelapor boleh kosong untuk data administratif; jika diisi, format kolomnya sebagai <em>Teks</em>.</li>
                    <li>Untuk CSV dari Excel: pilih <em>CSV UTF-8</em>; format kolom koordinat sebagai <em>Teks</em>.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-0 table-responsive-si">
                <div class="px-3 pt-3">
                    <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.25rem;font-weight:700;margin-bottom:0.25rem;">Kolom Template</h2>
                    <p class="text-muted mb-2" style="font-size:0.78rem;">Jangan mengubah nama kolom di baris pertama. Nama kolom yang sama juga dikenali (mis. "deskripsi" untuk "Deskripsi Umum").</p>
                </div>
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="padding-left:1.25rem;">Kolom</th>
                            <th style="width:110px;">Wajib</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($columns as $key => $col)
                        <tr>
                            <td style="padding-left:1.25rem;" class="t-body">{{ $col['label'] }}</td>
                            <td>
                                @if($col['required'])
                                <span class="badge bg-danger">Wajib</span>
                                @else
                                <span class="badge bg-secondary">Opsional</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
