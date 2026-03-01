@extends('layouts.app')

@section('content')
<div class="auth-card">
    <h2>Login Pelanggan</h2>
    @if ($errors->any())
        <div class="alert danger auth-alert">
            {{ $errors->first() }}
        </div>
    @endif
    <form method="POST" action="/login">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn primary full">Login</button>
    </form>
    <div class="auth-links">
        <a href="/register">Belum punya akun? Register di sini</a>
        <a href="/">Kembali ke Beranda</a>
        <span class="muted">Admin? <a href="/admin/login">Login Admin</a></span>
    </div>
</div>
<style>
    .auth-alert {
        margin-bottom: 14px;
        padding: 12px 14px;
        border-radius: 10px;
        font-size: 14px;
    }
</style>
@endsection
