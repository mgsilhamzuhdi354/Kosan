<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\PenyediaKos;
use App\Models\Penyewa;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type' => ['required', 'in:penyewa,penyedia_kos'],
            'no_hp' => ['required', 'string', 'max:30'],
            'alamat' => ['required', 'string', 'max:1000'],
            'jenis_kelamin' => ['required_if:account_type,penyewa', 'nullable', 'in:Laki-laki,Perempuan'],
            'nama_kos' => ['required_if:account_type,penyedia_kos', 'nullable', 'string', 'max:255'],
            'kota' => ['required_if:account_type,penyedia_kos', 'nullable', 'string', 'max:255'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $role = $request->account_type === User::ROLE_PENYEDIA_KOS
                ? User::ROLE_PENYEDIA_KOS
                : User::ROLE_PENYEWA;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $role,
            ]);

            if ($role === User::ROLE_PENYEDIA_KOS) {
                $penyedia = PenyediaKos::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $request->name,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                ]);

                Kos::create([
                    'penyedia_kos_id' => $penyedia->id,
                    'nama_kos' => $request->nama_kos,
                    'alamat' => $request->alamat,
                    'kota' => $request->kota,
                    'deskripsi' => 'Kos baru yang siap dikelola melalui platform.',
                    'latitude' => -2.8836,
                    'longitude' => 104.2169,
                    'status' => Kos::STATUS_AKTIF,
                ]);
            } else {
                Penyewa::create([
                    'user_id' => $user->id,
                    'nama_lengkap' => $request->name,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jenis_kelamin' => $request->jenis_kelamin,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route($user->isPenyediaKos() ? 'penyedia.dashboard' : 'penyewa.dashboard', absolute: false));
    }
}
