<?php

namespace App\Http\Controllers;

use App\Models\Penyewa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenyewaProfileController extends Controller
{
    public function edit(): View
    {
        $penyewa = auth()->user()->penyewa ?: new Penyewa([
            'nama_lengkap' => auth()->user()->name,
        ]);

        return view('penyewa.profil.edit', compact('penyewa'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:30'],
            'alamat' => ['required', 'string', 'max:1000'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
        ]);

        $user = $request->user();
        $user->update(['name' => $data['nama_lengkap']]);
        $user->penyewa()->updateOrCreate(['user_id' => $user->id], $data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
