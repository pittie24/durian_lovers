@extends('layouts.app')

@section('content')
<div class="auth-card">
    <h2>Reset Password</h2>
    <p class="auth-helper">Masukkan password baru untuk akun pelanggan Anda.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $email) }}" required>

        <label>Password Baru</label>
        <div class="password-field">
            <input type="password" name="password" id="newPasswordField" required>
            <button type="button" class="password-toggle" onclick="togglePasswordField('newPasswordField', this)">Tampilkan</button>
        </div>

        <label>Konfirmasi Password Baru</label>
        <div class="password-field">
            <input type="password" name="password_confirmation" id="confirmPasswordField" required>
            <button type="button" class="password-toggle" onclick="togglePasswordField('confirmPasswordField', this)">Tampilkan</button>
        </div>

        <button type="submit" class="btn primary full">Simpan Password Baru</button>
    </form>

    <div class="auth-links">
        <a href="/login">Kembali ke Login</a>
    </div>
</div>

<style>
    .auth-helper {
        margin-bottom: 14px;
        color: #6f6b61;
        line-height: 1.6;
    }

    .password-field {
        position: relative;
    }

    .password-field input {
        padding-right: 110px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #8f5a0a;
        font-weight: 700;
        cursor: pointer;
    }
</style>

<script>
    function togglePasswordField(fieldId, button) {
        const field = document.getElementById(fieldId);
        const showing = field.type === 'text';

        field.type = showing ? 'password' : 'text';
        button.textContent = showing ? 'Tampilkan' : 'Sembunyikan';
    }
</script>
@endsection
