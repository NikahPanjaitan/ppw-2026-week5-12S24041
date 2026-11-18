<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    /**
     * Tampilkan formulir login pustakawan.
     */
    public function showLoginForm(Request $request)
    {
        $lastEmail = $request->cookie('del_last_librarian_email');

        return view('auth.login', compact('lastEmail'));
    }

    /**
     * Proses autentikasi pustakawan, regenerasi session ID, dan pemasangan Cookie.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // 1. WAJIB: Regenerasi Session ID untuk menangkal serangan Session Fixation
            $request->session()->regenerate();

            // 2. Simpan State Sesi Pengguna di Server-Side
            session([
                'login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB',
                'librarian_name'  => Auth::user()->name,
            ]);

            // 3. Simpan HTTP Cookie di Sisi Klien (Masa aktif 7 hari / 10080 menit, HttpOnly)
            Cookie::queue('del_last_librarian_email', $request->email, 10080);

            return redirect()->intended(route('buku.index'))
                ->with('success', 'Sesi pustakawan aktif. Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Kredensial login pustakawan tidak valid. Silakan periksa kembali email dan kata sandi Anda.');
    }

    /**
     * Hancurkan sesi autentikasi dan invalidasi token (Logout).
     */
    public function logout(Request $request)
    {
        Auth::logout();

        // Bersihkan seluruh data sesi dan regenerasi token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Sesi server telah ditutup dengan aman. Sampai jumpa kembali!');
    }
}
