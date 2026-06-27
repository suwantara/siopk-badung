<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OpkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = OpkCategory::withCount('laporans')
            ->orderBy('nomor')
            ->get();

        return view('admin.kategori.index', compact('kategori'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor' => 'required|integer|min:1|max:99|unique:opk_categories,nomor',
            'nama'  => 'required|string|max:100',
            'ikon'  => 'nullable|string|max:10',
            'deskripsi' => 'nullable|string|max:255',
        ], [
            'nomor.unique' => 'Nomor kategori sudah digunakan.',
        ]);

        $cat = OpkCategory::create($validated);
        Cache::forget('kategori_list');
        return back()->with('success', "Kategori '{$cat->nama}' berhasil ditambahkan.");
    }

    public function update(Request $request, OpkCategory $kategori)
    {
        $validated = $request->validate([
            'nomor' => "required|integer|min:1|max:99|unique:opk_categories,nomor,{$kategori->id}",
            'nama'  => 'required|string|max:100',
            'ikon'  => 'nullable|string|max:10',
            'deskripsi' => 'nullable|string|max:255',
        ], [
            'nomor.unique' => 'Nomor kategori sudah digunakan.',
        ]);

        $kategori->update($validated);
        Cache::forget('kategori_list');
        return back()->with('success', "Kategori '{$kategori->nama}' berhasil diperbarui.");
    }

    public function destroy(OpkCategory $kategori)
    {
        if ($kategori->laporans()->count() > 0) {
            return back()->with('error', "Kategori '{$kategori->nama}' tidak dapat dihapus karena digunakan oleh {$kategori->laporans()->count()} laporan OPK.");
        }

        $nama = $kategori->nama;
        $kategori->delete();
        Cache::forget('kategori_list');
        return back()->with('success', "Kategori '{$nama}' berhasil dihapus.");
    }
}
