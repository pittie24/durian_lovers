<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="theme-admin auth-page">
    <div class="auth-card dark">
        <h2>Login Admin</h2>
        @if ($errors->any())
            <div class="alert danger auth-alert">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="/admin/login">
            @csrf
            <label>Email Admin</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn primary full">Masuk ke Dashboard</button>
        </form>
        <div class="auth-links">
            <a href="/">Kembali ke Beranda</a>
            <span class="muted">Bukan admin? <a href="/login">Login sebagai Pembeli</a></span>
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
</body>
</html>
