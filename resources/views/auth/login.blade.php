@extends('layouts.app')

@section('content')
<div class="auth-card">
    <h2>Login Pelanggan</h2>
    <form method="POST" action="/login">
        @csrf
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn primary full">Masuk</button>
    </form>
    <div class="auth-links">
        <a href="/register">Belum punya akun? Daftar di sini</a>
        <a href="/">Kembali ke Beranda</a>
        <span class="muted">Admin? <a href="/admin/login">Login Admin</a></span>
    </div>
</div>
@endsection
