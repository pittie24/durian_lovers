@extends('layouts.app')

@section('content')
<section class="hero">
    <div class="hero-text">
        <h1>Selamat Datang di Toko Durian Lovers</h1>
        <p>Platform belanja online untuk produk durian terbaik: pancake durian, durian montong, ice cream durian, dan masih banyak lagi!</p>
        <div class="hero-actions">
            <a href="/produk" class="btn primary">Lihat Produk</a>
            <a href="/login" class="btn outline">Riwayat Pesanan</a>
        </div>
    </div>
    <div class="hero-card">
        <div class="hero-highlight">
            <span class="badge">Fresh & Premium</span>
            <h3>Durian Montong Asli</h3>
            <p>Rasa legit, daging tebal, dan kualitas terjaga.</p>
        </div>
    </div>
</section>

<section class="landing-promo">
    <div class="landing-promo-badge">Promo Free Item</div>
    <h3>Belanja minimal Rp 300.000</h3>
    <p>Dapat gratis 1 Pancake Durian Mini secara otomatis saat checkout.</p>
</section>

<section class="feature-grid">
    <div class="feature-card">
        <h4>Belanja Mudah</h4>
        <p>Proses cepat dari pilih produk sampai bayar online.</p>
    </div>
    <div class="feature-card">
        <h4>Tracking Real-time</h4>
        <p>Pantau status pesanan dari awal hingga selesai.</p>
    </div>
    <div class="feature-card">
        <h4>Rating & Review</h4>
        <p>Berikan ulasan untuk membantu pelanggan lainnya.</p>
    </div>
</section>

<style>
    .landing-promo {
        margin: 22px 0;
        padding: 18px 20px;
        border-radius: 18px;
        background: linear-gradient(135deg, #fff7df, #fffdf5);
        border: 1px solid rgba(180, 128, 23, 0.14);
        box-shadow: 0 14px 28px rgba(180, 128, 23, 0.08);
    }

    .landing-promo-badge {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f59e0b;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
    }

    .landing-promo h3 {
        margin: 10px 0 6px;
        color: #7c4a03;
    }

    .landing-promo p {
        margin: 0;
        color: #8a5a00;
        font-weight: 700;
    }
</style>
@endsection
