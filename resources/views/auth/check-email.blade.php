@extends('layouts.app')

@section('content')
<div class="auth-card">
    <h2>Cek Email Anda</h2>
    <p class="auth-helper">
        Kami sudah mengirim link reset password ke
        <strong>{{ $email ?: 'alamat email yang Anda masukkan' }}</strong>.
    </p>
    <p class="auth-helper">
        Jika email belum masuk, periksa folder spam atau tunggu beberapa menit sebelum mencoba lagi.
    </p>

    <div class="auth-links">
        <a href="{{ route('password.request') }}">Kirim Ulang Link Reset</a>
        <a href="/login">Kembali ke Login</a>
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
