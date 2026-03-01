<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $this->isAdminEmail($user->email)) {
            return redirect('/admin/dashboard')
                ->with('error', 'Akun admin tidak dapat mengakses halaman pelanggan.');
        }

        return $next($request);
    }

    private function isAdminEmail(string $email): bool
    {
        return Admin::where('email', $email)->exists()
            || strcasecmp($email, 'admin@durianlovers.com') === 0;
    }
}
