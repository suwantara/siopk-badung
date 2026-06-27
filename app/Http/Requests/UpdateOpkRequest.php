<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_opk'           => 'required|string|max:200',
            'kondisi'            => 'required|in:baik,waspada,kritis',
            'status_pelindungan' => 'required|string',
            'deskripsi_umum'     => 'required|string|min:10',
            'sejarah_asal_usul'  => 'nullable|string',
            'nilai_makna_budaya' => 'nullable|string',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'fotos'              => 'nullable|array|max:10',
            'fotos.*'            => 'image|mimes:jpg,jpeg,png|max:2048',
            'hapus_foto_ids'     => 'nullable|string',
            'foto_utama_id'      => 'nullable|integer|exists:opk_fotos,id',
        ];
    }

    public function messages(): array
    {
        return [
            'fotos.*.uploaded' => 'Foto gagal diupload. Maksimal 2MB per file. Pastikan format JPG atau PNG.',
            'fotos.*.image'    => 'File harus berupa gambar (JPG/PNG).',
            'fotos.*.mimes'    => 'Format foto harus JPG atau PNG.',
            'fotos.*.max'      => 'Ukuran foto maksimal 2MB per file.',
            'fotos.max'        => 'Maksimal 10 foto per upload.',
        ];
    }
}
