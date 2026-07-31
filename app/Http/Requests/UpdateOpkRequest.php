<?php

namespace App\Http\Requests;

use App\Helpers\CacheKeys;
use App\Models\KondisiOpk;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class UpdateOpkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kondisiKodes = $this->getKondisiKodes();

        return [
            'nama_opk' => 'required|string|max:200',
            'kategori_id' => 'required|exists:opk_categories,id',
            'tahun_diketahui' => 'nullable|integer|min:1|max:'.date('Y'),
            'tahun_keterangan' => 'nullable|string|max:100',
            'kondisi' => ['required', Rule::in($kondisiKodes)],
            'status_pelindungan' => 'required|string',
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'desa_dinas_id' => ['required', Rule::exists('desa_dinas', 'id')->where('kecamatan_id', $this->kecamatan_id)],
            'nama_desa_adat' => 'required|string|max:150',
            'banjar_adat' => 'nullable|string|max:150',
            'lokasi_spesifik' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'deskripsi_umum' => 'required|string|min:10',
            'sejarah_asal_usul' => 'nullable|string',
            'nilai_makna_budaya' => 'nullable|string',
            'bahasa_digunakan' => 'nullable|string|max:100',
            'aksara_digunakan' => 'nullable|string|max:100',
            'frekuensi_pelaksanaan' => 'nullable|string',
            'status_kepemilikan' => 'nullable|string',
            'praktisi_nama' => 'nullable|string|max:150',
            'praktisi_usia' => 'nullable|integer|min:1|max:120',
            'praktisi_kontak' => 'nullable|string|max:50',
            'tipe_pelapor' => 'required|in:masyarakat,tokoh_adat,petugas_dinas',
            'pelapor_nama' => 'required|string|max:150',
            'pelapor_nik' => 'required|string|size:16|regex:/^\d+$/',
            'pelapor_whatsapp' => 'required|string|max:20',
            'pelapor_email' => 'nullable|email|max:150',
            'fotos' => 'nullable|array|max:10',
            'fotos.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'hapus_foto_ids' => 'nullable|string',
            'foto_utama_id' => 'nullable|integer|exists:opk_fotos,id',
            'hapus_video_ids' => 'nullable|string',
            'video_links' => 'nullable|array',
            'video_links.*' => 'nullable|url|max:500',
            'hapus_dokumen_ids' => 'nullable|string',
            'dokumen_baru' => 'nullable|array|max:10',
            'dokumen_baru.*' => 'file|mimes:pdf,doc,docx|max:20480',
        ];
    }

    public function messages(): array
    {
        return [
            'kategori_id.required' => 'Jenis OPK wajib dipilih.',
            'kecamatan_id.required' => 'Kecamatan wajib dipilih.',
            'desa_dinas_id.required' => 'Desa Dinas wajib dipilih.',
            'desa_dinas_id.exists' => 'Desa Dinas tidak sesuai dengan kecamatan yang dipilih.',
            'nama_desa_adat.required' => 'Nama Desa Adat wajib diisi.',
            'tipe_pelapor.required' => 'Tipe pelapor wajib dipilih.',
            'pelapor_nama.required' => 'Nama pelapor wajib diisi.',
            'pelapor_nik.required' => 'NIK wajib diisi.',
            'pelapor_nik.regex' => 'NIK harus berupa 16 digit angka.',
            'pelapor_nik.size' => 'NIK harus 16 digit.',
            'pelapor_whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'fotos.*.uploaded' => 'Foto gagal diupload. Maksimal 2MB per file. Pastikan format JPG atau PNG.',
            'fotos.*.image' => 'File harus berupa gambar (JPG/PNG).',
            'fotos.*.mimes' => 'Format foto harus JPG atau PNG.',
            'fotos.*.max' => 'Ukuran foto maksimal 2MB per file.',
            'fotos.max' => 'Maksimal 10 foto per upload.',
            'dokumen_baru.*.max' => 'Ukuran dokumen maksimal 20MB.',
            'dokumen_baru.*.uploaded' => 'Dokumen gagal diupload.',
            'dokumen_baru.max' => 'Maksimal 10 dokumen per upload.',
            'video_links.*.url' => 'Format URL video tidak valid.',
        ];
    }

    private function getKondisiKodes(): array
    {
        return Cache::remember(CacheKeys::KONDISI_KODES, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->pluck('kode')->toArray();
        });
    }
}
