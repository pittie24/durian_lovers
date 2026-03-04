<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    private const DEFAULT_ADMIN_EMAILS = [
        'admin@durianlovers.com',
        'admin@durianlovers.test',
    ];

    private const DEFAULT_ADMIN_PASSWORD = 'Admin@2026';

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

        $this->ensureDefaultAdminAccount();

        if (!$this->isAdminEmail($credentials['email'])) {
            return back()
                ->withErrors(['email' => 'Hanya akun admin yang dapat mengakses halaman admin.'])
                ->onlyInput('email');
        }

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        return back()
            ->withErrors(['email' => 'Email atau password admin tidak valid.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    private function isAdminEmail(string $email): bool
    {
        return Admin::where('email', $email)->exists()
            || in_array(strtolower($email), self::DEFAULT_ADMIN_EMAILS, true);
    }

    private function ensureDefaultAdminAccount(): void
    {
        if (Admin::exists()) {
            return;
        }

        Admin::create([
            'name' => 'Admin Durian Lovers',
            'email' => self::DEFAULT_ADMIN_EMAILS[0],
            'password' => Hash::make(self::DEFAULT_ADMIN_PASSWORD),
        ]);
    }
}
