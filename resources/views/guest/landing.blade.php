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
@endsection
