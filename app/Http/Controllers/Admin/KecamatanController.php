<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class KecamatanController extends Controller
{
    public function index(Request $request)
    {
        $kecamatans = Kecamatan::withCount(['desaDinas', 'desaAdats'])
            ->orderBy('nama')->get();

        $selectedId = $request->kecamatan_id ?? $kecamatans->first()?->id;
        $selectedKec = $selectedId ? Kecamatan::with(['desaDinas', 'desaAdats'])->find($selectedId) : null;
        $desaDinas = $selectedKec ? $selectedKec->desaDinas()->orderBy('nama')->get() : collect();
        $desaAdats = $selectedKec ? $selectedKec->desaAdats()->orderBy('nama')->get() : collect();

        return view('admin.wilayah.index', compact(
            'kecamatans', 'selectedKec', 'selectedId',
            'desaDinas', 'desaAdats'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:kecamatans,kode',
        ], ['kode.unique' => 'Kode kecamatan sudah digunakan.']);

        $kec = Kecamatan::create($validated);
        Cache::forget(CacheKeys::KECAMATAN_LIST);

        return redirect()->route('admin.wilayah.index', ['kecamatan_id' => $kec->id])
            ->with('success', "Kecamatan {$kec->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Kecamatan $kecamatan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => "required|string|max:20|unique:kecamatans,kode,{$kecamatan->id}",
        ], ['kode.unique' => 'Kode kecamatan sudah digunakan.']);

        $kecamatan->update($validated);
        Cache::forget(CacheKeys::KECAMATAN_LIST);

        return back()->with('success', "Kecamatan {$kecamatan->nama} berhasil diperbarui.");
    }

    public function destroy(Kecamatan $kecamatan)
    {
        $nama = $kecamatan->nama;
        try {
            $kecamatan->delete();
            Cache::forget(CacheKeys::KECAMATAN_LIST);
            Cache::forget(CacheKeys::KECAMATAN_WITH_DESA);

            return redirect()->route('admin.wilayah.index')
                ->with('success', "Kecamatan {$nama} dan semua desa/desa adat terkait berhasil dihapus.");
        } catch (QueryException $e) {
            return back()->with('error', "Kecamatan {$nama} tidak dapat dihapus karena masih memiliki data OPK terkait. Pindahkan atau hapus data OPK terlebih dahulu.");
        }
    }
}
