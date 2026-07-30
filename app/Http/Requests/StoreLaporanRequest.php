<?php

namespace App\Http\Requests;

use App\Helpers\CacheKeys;
use App\Models\KondisiOpk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class StoreLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kondisiKodes = $this->getKondisiKodes();

        return [
            // Step 1
            'nama_opk' => 'required|string|max:200',
            'kategori_id' => 'required|exists:opk_categories,id',
            'tahun_diketahui' => 'nullable|integer|min:1|max:'.date('Y'),
            'tahun_keterangan' => 'nullable|string|max:100',
            'status_pelindungan' => 'required|in:belum_terdaftar,sudah_didata_dinas,sk_kabupaten,sk_provinsi,wbtb_nasional',
            'kondisi' => ['required', Rule::in($kondisiKodes)],
            // Step 2
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'desa_dinas_id' => ['required', Rule::exists('desa_dinas', 'id')->where('kecamatan_id', $this->kecamatan_id)],
            'nama_desa_adat' => 'required|string|max:150',
            'banjar_adat' => 'nullable|string|max:150',
            'lokasi_spesifik' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            // Step 3
            'deskripsi_umum' => 'required|string|min:50',
            'sejarah_asal_usul' => 'nullable|string',
            'nilai_makna_budaya' => 'nullable|string',
            'bahasa_digunakan' => 'nullable|string|max:100',
            'aksara_digunakan' => 'nullable|string|max:100',
            'frekuensi_pelaksanaan' => 'nullable|string',
            'status_kepemilikan' => 'nullable|string',
            'praktisi_nama' => 'nullable|string|max:150',
            'praktisi_usia' => 'nullable|integer|min:1|max:120',
            'praktisi_kontak' => 'nullable|string|max:50',
            // Step 4
            'fotos' => 'nullable|array|max:10',
            'fotos.*' => 'image|mimes:jpg,jpeg,png,heic,webp|max:10240',
            'keterangan_foto_utama' => 'nullable|string|max:255',
            'link_video' => 'nullable|url|max:500',
            'dokumen' => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            // Step 5
            'tipe_pelapor' => 'required|in:masyarakat,tokoh_adat,petugas_dinas',
            'pelapor_nama' => 'required|string|max:150',
            'pelapor_nik' => 'required|string|size:16|regex:/^\d+$/',
            'pelapor_whatsapp' => 'required|string|max:20',
            'pelapor_email' => 'nullable|email|max:150',
            'setuju_1' => 'accepted',
            'setuju_2' => 'accepted',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_opk.required' => 'Nama objek budaya wajib diisi.',
            'kategori_id.required' => 'Jenis OPK wajib dipilih.',
            'kondisi.required' => 'Kondisi OPK wajib dipilih.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'desa_dinas_id.required' => 'Desa dinas wajib dipilih.',
            'desa_dinas_id.exists' => 'Desa dinas tidak sesuai dengan kecamatan yang dipilih.',
            'nama_desa_adat.required' => 'Nama desa adat wajib diisi.',
            'deskripsi_umum.required' => 'Deskripsi umum wajib diisi.',
            'deskripsi_umum.min' => 'Deskripsi minimal 50 karakter.',
            'fotos.*.image' => 'File foto harus berupa gambar.',
            'fotos.*.mimes' => 'Format foto harus JPG, PNG, HEIC, atau WebP.',
            'fotos.*.uploaded' => 'Foto gagal diupload. Pastikan ukuran file tidak melebihi 10MB.',
            'fotos.*.max' => 'Ukuran foto maksimal 10MB per file.',
            'fotos.max' => 'Maksimal 10 foto yang dapat diupload.',
            'dokumen.max' => 'Ukuran dokumen maksimal 20MB.',
            'dokumen.uploaded' => 'Dokumen gagal diupload. Pastikan ukuran file tidak melebihi 20MB.',
            'pelapor_nama.required' => 'Nama pelapor wajib diisi.',
            'pelapor_nik.required' => 'NIK wajib diisi.',
            'pelapor_nik.regex' => 'NIK harus berupa 16 digit angka.',
            'pelapor_nik.size' => 'NIK harus 16 digit.',
            'pelapor_whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'setuju_1.accepted' => 'Anda harus menyetujui pernyataan pertama.',
            'setuju_2.accepted' => 'Anda harus menyetujui pernyataan kedua.',
        ];
    }

    private function getKondisiKodes(): array
    {
        return Cache::remember(CacheKeys::KONDISI_KODES, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->pluck('kode')->toArray();
        });
    }
}
