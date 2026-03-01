<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Durian Lovers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="theme-admin">
    <header class="admin-navbar">
        <div class="container nav-inner">
            <div class="logo">
    <i class="bi bi-box-seam"></i>
    <span>Admin Panel</span>
</div>

<nav class="nav-links">
    <a href="/admin/dashboard" class="{{ request()->is('admin/dashboard*') ? 'active' : '' }}">Dashboard</a>
    <a href="/admin/produk" class="{{ request()->is('admin/produk*') ? 'active' : '' }}">Produk</a>
    <a href="/admin/pesanan" class="{{ request()->is('admin/pesanan*') ? 'active' : '' }}">Pesanan</a>
    <a href="/admin/payment-confirmations" class="{{ request()->is('admin/payment-confirmations*') ? 'active' : '' }}">Konfirmasi Pembayaran</a>
    <a href="/admin/laporan" class="{{ request()->is('admin/laporan*') ? 'active' : '' }}">Laporan</a>
    <a href="/admin/pelanggan" class="{{ request()->is('admin/pelanggan*') ? 'active' : '' }}">Pelanggan</a>

    <form action="/admin/logout" method="POST" class="inline-form">
        @csrf
        <button type="submit" class="link-button logout">
            <i class="bi bi-box-arrow-right"></i> Logout
        </button>
    </form>
</nav>

        </div>
    </header>

    <main class="container main-content">
        @include('partials.flash')
        @yield('content')
    </main>
</body>
</html>
