@extends('layouts.app')

@section('content')
<div class="auth-card">
    <h2>Registrasi Pelanggan</h2>
    <form method="POST" action="/register">
        @csrf
        <label>Nama Lengkap</label>
        <input type="text" name="name" value="{{ old('name') }}" required>

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>

        <label>Nomor Telepon</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required>

        <label>Alamat</label>
        <textarea name="address" rows="3" required>{{ old('address') }}</textarea>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit" class="btn primary full">Daftar</button>
    </form>
    <div class="auth-links">
        <a href="/login">Sudah punya akun? Login</a>
        <a href="/">Kembali ke Beranda</a>
    </div>
</div>
@endsection
