<?php

namespace App\Http\Controllers;

use App\Models\Fasilitas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FasilitasController extends Controller
{
    public function index(): View
    {
        return view('admin.fasilitas.index', [
            'fasilitas' => Fasilitas::withCount('kamars')->orderBy('nama_fasilitas')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.fasilitas.form', ['fasilitas' => new Fasilitas()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Fasilitas::create($request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:100', 'unique:fasilitas,nama_fasilitas'],
        ]));

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Fasilitas $fasilitas): View
    {
        return view('admin.fasilitas.form', compact('fasilitas'));
    }

    public function update(Request $request, Fasilitas $fasilitas): RedirectResponse
    {
        $fasilitas->update($request->validate([
            'nama_fasilitas' => ['required', 'string', 'max:100', Rule::unique('fasilitas', 'nama_fasilitas')->ignore($fasilitas)],
        ]));

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy(Fasilitas $fasilitas): RedirectResponse
    {
        $fasilitas->delete();

        return redirect()->route('admin.fasilitas.index')->with('success', 'Fasilitas berhasil dihapus.');
    }
}
