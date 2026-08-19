<?php

namespace App\Http\Controllers;

use App\Models\PembayaranAwal;
use App\Models\PembayaranBulanan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        abort_if(str_contains($path, '..'), 404);
        abort_unless($this->hasAllowedPrefix($path), 404);
        abort_unless($this->canAccess($request->user(), $path), 403);
        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    private function hasAllowedPrefix(string $path): bool
    {
        return str_starts_with($path, 'dummy/')
            || str_starts_with($path, 'pembayaran-awal/')
            || str_starts_with($path, 'pembayaran-bulanan/');
    }

    private function canAccess(User $user, string $path): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isPenyewa()) {
            $penyewaId = $user->penyewa?->id;

            return $penyewaId
                && (
                    PembayaranAwal::where('bukti_bayar', $path)
                        ->whereHas('pemesanan', fn ($query) => $query->where('penyewa_id', $penyewaId))
                        ->exists()
                    || PembayaranBulanan::where('bukti_bayar', $path)
                        ->whereHas('tagihanBulanan.penghuni', fn ($query) => $query->where('penyewa_id', $penyewaId))
                        ->exists()
                );
        }

        if ($user->isPenyediaKos()) {
            $kosIds = $user->penyediaKos?->kos()->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];

            return $kosIds !== []
                && (
                    PembayaranAwal::where('bukti_bayar', $path)
                        ->whereHas('pemesanan.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds))
                        ->exists()
                    || PembayaranBulanan::where('bukti_bayar', $path)
                        ->whereHas('tagihanBulanan.penghuni.kamar', fn ($query) => $query->whereIn('kos_id', $kosIds))
                        ->exists()
                );
        }

        return false;
    }
}
