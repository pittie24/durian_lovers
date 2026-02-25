<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            // ✅ kalau halaman admin, jangan lempar ke /login pelanggan
            if ($request->is('admin') || $request->is('admin/*')) {
                return '/admin/login';
            }

            return route('login'); // login pelanggan
        }
    }
}
