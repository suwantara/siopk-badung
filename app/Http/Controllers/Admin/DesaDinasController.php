<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\DesaDinas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DesaDinasController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'nama' => 'required|string|max:100',
        ]);

        DesaDinas::create($validated);
        Cache::forget(CacheKeys::KECAMATAN_WITH_DESA);

        return back()->with('success', "Desa Dinas {$validated['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, DesaDinas $desaDina)
    {
        $validated = $request->validate(['nama' => 'required|string|max:100']);
        $desaDina->update($validated);
        Cache::forget(CacheKeys::KECAMATAN_WITH_DESA);

        return back()->with('success', "Desa Dinas {$desaDina->nama} berhasil diperbarui.");
    }

    public function destroy(DesaDinas $desaDina)
    {
        $desaDina->delete();
        Cache::forget(CacheKeys::KECAMATAN_WITH_DESA);

        return back()->with('success', "Desa Dinas {$desaDina->nama} berhasil dihapus.");
    }
}
