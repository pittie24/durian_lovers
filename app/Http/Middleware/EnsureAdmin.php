<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/admin/login');
        }

        if (Auth::user()->email !== 'admin@durianlovers.com') {
            Auth::logout();
            return redirect('/admin/login');
        }

        return $next($request);
    }
}
