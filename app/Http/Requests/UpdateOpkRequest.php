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
            'kondisi' => ['required', Rule::in($kondisiKodes)],
            'status_pelindungan' => 'required|string',
            'deskripsi_umum' => 'required|string|min:10',
            'sejarah_asal_usul' => 'nullable|string',
            'nilai_makna_budaya' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'fotos' => 'nullable|array|max:10',
            'fotos.*' => 'image|mimes:jpg,jpeg,png|max:2048',
            'hapus_foto_ids' => 'nullable|string',
            'foto_utama_id' => 'nullable|integer|exists:opk_fotos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'fotos.*.uploaded' => 'Foto gagal diupload. Maksimal 2MB per file. Pastikan format JPG atau PNG.',
            'fotos.*.image' => 'File harus berupa gambar (JPG/PNG).',
            'fotos.*.mimes' => 'Format foto harus JPG atau PNG.',
            'fotos.*.max' => 'Ukuran foto maksimal 2MB per file.',
            'fotos.max' => 'Maksimal 10 foto per upload.',
        ];
    }

    private function getKondisiKodes(): array
    {
        return Cache::remember(CacheKeys::KONDISI_KODES, now()->addHours(24), function () {
            return KondisiOpk::orderBy('urutan')->pluck('kode')->toArray();
        });
    }
}
