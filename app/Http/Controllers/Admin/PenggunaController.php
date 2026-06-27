<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreUserRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.pengguna.index', compact('users'));
    }

    public function create()
    {
        return view('admin.pengguna.form', ['user' => null]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'nip'       => $validated['nip'] ?? null,
            'no_hp'     => $validated['no_hp'] ?? null,
            'instansi'  => $validated['instansi'] ?? null,
            'is_active' => true,
        ]);
        $user->role = $validated['role'];
        $user->save();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', "Pengguna {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(User $pengguna)
    {
        $this->authorize('update', $pengguna);
        return view('admin.pengguna.form', ['user' => $pengguna]);
    }

    public function update(StoreUserRequest $request, User $pengguna)
    {
        $this->authorize('update', $pengguna);

        $validated = $request->validated();

        $pengguna->name      = $validated['name'];
        $pengguna->email     = $validated['email'];
        $pengguna->role      = $validated['role'];
        $pengguna->nip       = $validated['nip'] ?? $pengguna->nip;
        $pengguna->no_hp     = $validated['no_hp'] ?? $pengguna->no_hp;
        $pengguna->instansi  = $validated['instansi'] ?? $pengguna->instansi;
        $pengguna->is_active = $validated['is_active'] ?? true;

        if (!empty($validated['password'])) {
            $pengguna->password = $validated['password'];
        }

        $pengguna->save();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', "Data pengguna {$pengguna->name} berhasil diperbarui.");
    }

    public function toggleAktif(User $pengguna)
    {
        $this->authorize('toggleActive', $pengguna);
        $pengguna->update(['is_active' => !$pengguna->is_active]);
        $status = $pengguna->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Pengguna {$pengguna->name} berhasil {$status}.");
    }

    public function destroy(User $pengguna)
    {
        $this->authorize('delete', $pengguna);
        $nama = $pengguna->name;
        $pengguna->delete();
        return redirect()->route('admin.pengguna.index')
                         ->with('success', "Pengguna {$nama} berhasil dihapus.");
    }
}
