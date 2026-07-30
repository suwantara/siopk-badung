<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\KondisiOpk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class KondisiController extends Controller
{
    public function index()
    {
        $kondisiItems = KondisiOpk::withCount('laporans')
            ->orderBy('urutan')
            ->get();

        return view('admin.kondisi.index', compact('kondisiItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'store.kode' => 'required|alpha_dash|max:50|unique:kondisi_opks,kode',
            'store.nama' => 'required|string|max:100',
            'store.deskripsi' => 'nullable|string|max:255',
            'store.warna' => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'store.urutan' => 'required|integer|min:0|max:999',
        ], $this->messages());

        $data = $validated['store'];
        KondisiOpk::create($data);
        $this->invalidateCache();

        return redirect()->route('admin.kondisi.index')->with('success', "Kondisi '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, KondisiOpk $kondisi)
    {
        $key = "edit.{$kondisi->id}";

        $validated = $request->validate([
            "edit.{$kondisi->id}.kode" => ['required', 'alpha_dash', 'max:50', Rule::unique('kondisi_opks', 'kode')->ignore($kondisi->id)],
            "edit.{$kondisi->id}.nama" => 'required|string|max:100',
            "edit.{$kondisi->id}.deskripsi" => 'nullable|string|max:255',
            "edit.{$kondisi->id}.warna" => 'required|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            "edit.{$kondisi->id}.urutan" => 'required|integer|min:0|max:999',
        ], $this->messages());

        $data = $validated['edit'][$kondisi->id];
        $kondisi->update($data);
        $this->invalidateCache();

        return redirect()->route('admin.kondisi.index')->with('success', "Kondisi '{$data['nama']}' berhasil diperbarui.");
    }

    public function destroy(KondisiOpk $kondisi)
    {
        $count = $kondisi->laporans()->count();

        if ($count > 0) {
            return redirect()->route('admin.kondisi.index')
                ->with('error', "Kondisi '{$kondisi->nama}' tidak dapat dihapus karena digunakan oleh {$count} laporan OPK.");
        }

        $nama = $kondisi->nama;
        $kondisi->delete();
        $this->invalidateCache();

        return redirect()->route('admin.kondisi.index')->with('success', "Kondisi '{$nama}' berhasil dihapus.");
    }

    private function invalidateCache(): void
    {
        Cache::forget(CacheKeys::KONDISI_LIST);
        Cache::forget(CacheKeys::KONDISI_KODES);
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
    }

    private function messages(): array
    {
        return [
            'store.kode.unique' => 'Kode kondisi sudah digunakan.',
            'store.warna.regex' => 'Warna harus dalam format hex (#RRGGBB).',
            'edit.*.kode.unique' => 'Kode kondisi sudah digunakan.',
            'edit.*.warna.regex' => 'Warna harus dalam format hex (#RRGGBB).',
        ];
    }
}
