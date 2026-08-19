<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminUserAccountController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with(['penyewa', 'penyediaKos'])
            ->whereIn('role', [User::ROLE_PENYEWA, User::ROLE_PENYEDIA_KOS])
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('email', 'like', '%'.$request->q.'%')
                        ->orWhereHas('penyewa', fn ($profile) => $profile->where('nama_lengkap', 'like', '%'.$request->q.'%'))
                        ->orWhereHas('penyediaKos', fn ($profile) => $profile->where('nama_lengkap', 'like', '%'.$request->q.'%'));
                });
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.akun.index', compact('users'));
    }

    public function resetPassword(User $user): RedirectResponse
    {
        if (! in_array($user->role, [User::ROLE_PENYEWA, User::ROLE_PENYEDIA_KOS], true)) {
            return back()->with('error', 'Password admin tidak bisa direset dari halaman ini.');
        }

        $password = 'KosPutri'.Str::upper(Str::random(6));

        $user->update([
            'password' => Hash::make($password),
        ]);

        return back()->with('temporary_password', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $password,
        ])->with('success', 'Password baru berhasil dibuat. Catat password sementara sebelum berpindah halaman.');
    }
}
