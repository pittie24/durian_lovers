<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Durian Lovers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="theme-cream">
    <header class="navbar">
        <div class="container nav-inner">
            <a href="/" class="logo">Durian Lovers</a>
            <nav class="nav-links">
                @auth
                    <a href="/produk">Produk</a>
                    <a href="/riwayat">Riwayat</a>
                    <a href="/keranjang">Keranjang</a>
                    <span class="nav-user">Halo, {{ auth()->user()->name }}</span>
                    <form action="/logout" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="link-button">Logout</button>
                    </form>
                @else
                    <a href="/login">Login</a>
                    <a href="/register">Daftar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="container main-content">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            © 2024 Durian Lovers. Sistem Informasi Penjualan Berbasis Web.
        </div>
    </footer>
</body>
</html>
