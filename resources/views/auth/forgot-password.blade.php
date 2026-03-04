@extends('layouts.app')

@section('content')
<div class="auth-card">
    <h2>Lupa Password</h2>
    <p class="auth-helper">Masukkan email pelanggan Anda. Kami akan kirim link untuk reset password.</p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <button type="submit" class="btn primary full">Kirim Link Reset</button>
    </form>

    <div class="auth-links">
        <a href="/login">Kembali ke Login</a>
        <a href="/">Kembali ke Beranda</a>
    </div>
</div>

<style>
    .auth-helper {
        margin-bottom: 14px;
        color: #6f6b61;
        line-height: 1.6;
    }
</style>
@endsection
