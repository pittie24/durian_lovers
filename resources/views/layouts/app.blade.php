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

            <!-- tombol hamburger (muncul di mobile) -->
            <button class="nav-toggle" type="button" aria-label="Toggle menu" onclick="toggleNav()">
                <span></span><span></span><span></span>
            </button>

            <nav class="nav-links" id="navMenu">
                @auth
                    <a href="/produk" class="{{ request()->is('produk*') ? 'active' : '' }}">Produk</a>
                    <a href="/riwayat" class="{{ request()->is('riwayat*') ? 'active' : '' }}">Riwayat</a>
                    <a href="/keranjang" class="{{ request()->is('keranjang*') ? 'active' : '' }}">Keranjang</a>

                    <span class="nav-user">Halo, {{ auth()->user()->name }}</span>

                    <form action="/logout" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="link-button">Logout</button>
                    </form>
                @else
                    <a href="/login" class="{{ request()->is('login') ? 'active' : '' }}">Login</a>
                    <a href="/register" class="{{ request()->is('register') ? 'active' : '' }}">Daftar</a>
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

    <script>
        function toggleNav() {
            const menu = document.getElementById('navMenu');
            menu.classList.toggle('open');
        }
    </script>
</body>
</html>
