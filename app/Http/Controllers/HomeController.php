<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $featuredKos = Kos::with(['penyediaKos', 'kamars' => fn ($query) => $query->where('status', Kamar::STATUS_TERSEDIA)->with('fasilitas')])
            ->withCount(['kamars' => fn ($query) => $query->where('status', Kamar::STATUS_TERSEDIA)])
            ->where('status', Kos::STATUS_AKTIF)
            ->orderByDesc('is_promoted')
            ->orderBy('nama_kos')
            ->take(8)
            ->get();

        $kamars = Kamar::with('fasilitas')
            ->with('kos')
            ->where('status', Kamar::STATUS_TERSEDIA)
            ->inActiveKos()
            ->latest()
            ->take(6)
            ->get();

        $kosMarkers = $this->activeKosMarkers();
        $stats = [
            'total_kos' => Kos::where('status', Kos::STATUS_AKTIF)->count(),
            'total_kamar' => Kamar::whereHas('kos', fn ($query) => $query->where('status', Kos::STATUS_AKTIF))->count(),
            'promo' => Kos::where('status', Kos::STATUS_AKTIF)->where('is_promoted', true)->count(),
        ];

        return view('public.home', compact('featuredKos', 'kamars', 'kosMarkers', 'stats'));
    }

    public function kamarIndex(Request $request): View
    {
        $kamars = Kamar::with('fasilitas')
            ->with('kos')
            ->whereHas('kos', function ($query) use ($request) {
                $query->where('status', Kos::STATUS_AKTIF)
                    ->when($request->boolean('promo'), fn ($kosQuery) => $kosQuery->where('is_promoted', true))
                    ->when($request->filled('lokasi'), function ($kosQuery) use ($request) {
                        $kosQuery->where(function ($inner) use ($request) {
                            $inner->where('nama_kos', 'like', '%'.$request->lokasi.'%')
                                ->orWhere('alamat', 'like', '%'.$request->lokasi.'%')
                                ->orWhere('kota', 'like', '%'.$request->lokasi.'%');
                        });
                    });
            })
            ->when($request->filled('q'), fn ($query) => $query->where('nama_kamar', 'like', '%'.$request->q.'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('tipe'), fn ($query) => $query->where('tipe_kamar', $request->tipe))
            ->when($request->filled('harga_min'), fn ($query) => $query->where('harga_bulanan', '>=', (int) $request->harga_min))
            ->when($request->filled('harga_max'), fn ($query) => $query->where('harga_bulanan', '<=', (int) $request->harga_max))
            ->orderBy('nama_kamar')
            ->paginate(9)
            ->withQueryString();

        $tipes = Kamar::query()
            ->inActiveKos()
            ->select('tipe_kamar')
            ->distinct()
            ->orderBy('tipe_kamar')
            ->pluck('tipe_kamar');

        return view('public.kamar.index', [
            'kamars' => $kamars,
            'statuses' => Kamar::STATUSES,
            'tipes' => $tipes,
            'kosMarkers' => $this->activeKosMarkers(),
        ]);
    }

    public function kamarShow(Kamar $kamar): View
    {
        abort_unless($kamar->isInActiveKos(), 404);

        $kamar->load('fasilitas');
        $kamar->load('kos');

        return view('public.kamar.show', compact('kamar'));
    }

    public function maps(Request $request): View
    {
        $kos = Kos::with(['kamars' => fn ($query) => $query->where('status', Kamar::STATUS_TERSEDIA)])
            ->where('status', Kos::STATUS_AKTIF)
            ->when($request->boolean('promo'), fn ($query) => $query->where('is_promoted', true))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('public.maps', [
            'kos' => $kos,
            'markers' => $this->activeKosMarkers($kos),
        ]);
    }

    private function activeKosMarkers($kos = null): array
    {
        $items = $kos ?: Kos::withCount(['kamars' => fn ($query) => $query->where('status', Kamar::STATUS_TERSEDIA)])
            ->where('status', Kos::STATUS_AKTIF)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $items->map(fn (Kos $kos) => [
            'id' => $kos->id,
            'name' => $kos->nama_kos,
            'address' => $kos->alamat,
            'lat' => $kos->latitude,
            'lng' => $kos->longitude,
            'promo' => $kos->is_promoted,
            'url' => route('public.kamar.index', ['lokasi' => $kos->nama_kos]),
        ])->values()->all();
    }
}
