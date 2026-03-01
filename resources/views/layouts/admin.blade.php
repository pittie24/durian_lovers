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
    <a href="/admin/payment-confirmations" class="{{ request()->is('admin/payment-confirmations*') ? 'active' : '' }}">Pesanan</a>
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
        <div class="page-backbar">
            <button
                type="button"
                class="page-backbtn admin"
                onclick="goBackPage('{{ url('/admin/dashboard') }}')"
            >
                ← Kembali
            </button>
        </div>
        @include('partials.flash')
        @yield('content')
    </main>

    <script>
        function goBackPage(fallbackUrl) {
            if (window.history.length > 1 && document.referrer) {
                window.history.back();
                return;
            }

            window.location.href = fallbackUrl;
        }
    </script>

    <style>
        .page-backbar {
            margin-bottom: 18px;
        }

        .page-backbtn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            color: #1f2937;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }

        .page-backbtn.admin:hover {
            background: linear-gradient(135deg, #eef2ff, #f8fafc);
        }
    </style>
</body>
</html>
