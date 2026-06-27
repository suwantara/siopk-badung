<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesaAdat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DesaAdatController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'nama'         => 'required|string|max:150',
        ]);

        DesaAdat::create($validated);
        Cache::forget('kecamatan_with_desa');

        return back()->with('success', "Desa Adat {$validated['nama']} berhasil ditambahkan.");
    }

    public function update(Request $request, DesaAdat $desaAdat)
    {
        $validated = $request->validate(['nama' => 'required|string|max:150']);
        $desaAdat->update($validated);
        Cache::forget('kecamatan_with_desa');

        return back()->with('success', "Desa Adat {$desaAdat->nama} berhasil diperbarui.");
    }

    public function destroy(DesaAdat $desaAdat)
    {
        $desaAdat->delete();
        Cache::forget('kecamatan_with_desa');

        return back()->with('success', "Desa Adat {$desaAdat->nama} berhasil dihapus.");
    }
}
