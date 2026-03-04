<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CustomerForgotPasswordController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function notice(Request $request)
    {
        return view('auth.check-email', [
            'email' => $request->session()->get('password_reset_email'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        if ($this->isAdminEmail($validated['email'])) {
            return back()
                ->withErrors(['email' => 'Akun admin tidak dapat diproses dari halaman pelanggan.'])
                ->onlyInput('email');
        }

        $status = Password::sendResetLink([
            'email' => $validated['email'],
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('password.notice')
                ->with('success', 'Link reset password sudah dikirim ke email Anda.')
                ->with('password_reset_email', $this->maskEmail($validated['email']));
        }

        return back()
            ->withErrors(['email' => $this->messageForStatus($status)])
            ->onlyInput('email');
    }

    public function edit(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $token,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(6)],
        ]);

        if ($this->isAdminEmail($validated['email'])) {
            return back()
                ->withErrors(['email' => 'Akun admin tidak dapat diproses dari halaman pelanggan.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $status = Password::reset(
            $validated,
            function ($user) use ($validated) {
                $user->forceFill([
                    'password' => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('success', 'Password berhasil diperbarui. Silakan login.');
        }

        return back()
            ->withErrors(['email' => $this->messageForStatus($status)])
            ->withInput($request->except(['password', 'password_confirmation']));
    }

    private function isAdminEmail(string $email): bool
    {
        return Admin::where('email', $email)->exists()
            || strcasecmp($email, 'admin@durianlovers.com') === 0;
    }

    private function messageForStatus(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'Email pelanggan tidak ditemukan.',
            Password::INVALID_TOKEN => 'Token reset password tidak valid atau sudah kedaluwarsa.',
            Password::RESET_THROTTLED => 'Permintaan terlalu sering. Coba lagi beberapa saat lagi.',
            default => 'Permintaan reset password gagal diproses.',
        };
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2);

        if (strlen($name) <= 2) {
            return str_repeat('*', strlen($name)).'@'.$domain;
        }

        return substr($name, 0, 2).str_repeat('*', max(strlen($name) - 2, 1)).'@'.$domain;
    }
}
