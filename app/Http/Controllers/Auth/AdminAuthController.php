<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!$this->isAdminEmail($credentials['email'])) {
            return back()
                ->withErrors(['email' => 'Hanya akun admin yang dapat mengakses halaman admin.'])
                ->onlyInput('email');
        }

        // Pakai guard default (web) karena admin kamu tersimpan di tabel users
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()
            ->withErrors(['email' => 'Email atau password admin tidak valid.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Logout dari guard default (web)
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    private function isAdminEmail(string $email): bool
    {
        return Admin::where('email', $email)->exists()
            || strcasecmp($email, 'admin@durianlovers.com') === 0;
    }
}
