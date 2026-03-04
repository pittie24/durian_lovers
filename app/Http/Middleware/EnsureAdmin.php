<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('admin');

        if (!$guard->check()) {
            return redirect('/admin/login');
        }

        if (!$this->isAdminEmail((string) $guard->user()->email)) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/admin/login')
                ->withErrors(['email' => 'Hanya akun admin yang dapat mengakses halaman admin.']);
        }

        return $next($request);
    }

    private function isAdminEmail(string $email): bool
    {
        return Admin::where('email', $email)->exists()
            || in_array(strtolower($email), ['admin@durianlovers.com', 'admin@durianlovers.test'], true);
    }
}
