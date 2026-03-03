@extends('layouts.app')

@section('hideBackButton', '1')

@section('content')

<section class="section-heading split">
  <div>
    <h2 class="section-title">Produk Terlaris</h2>
    <p class="section-subtitle">Favorit pelanggan minggu ini.</p>
  </div>
</section>

<div class="grid cards">
  @forelse ($topProducts as $product)
    <a href="/produk/{{ $product->id }}" class="card product-card">
      <div class="thumb">
        <img
          src="{{ asset($product->image_url) }}"
          alt="{{ $product->name }}"
          loading="lazy"
          onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';"
        >
        <span class="pill pill-red">{{ $product->sold_count }} terjual</span>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="rating">★ {{ number_format($product->rating_avg, 1) }}</div>
          <div class="muted">Stok: {{ $product->stock }}</div>
        </div>

        <div class="product-name">{{ $product->name }}</div>

        <div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
      </div>
    </a>
  @empty
    <div class="empty-box">Tidak ada produk terlaris untuk ditampilkan.</div>
  @endforelse
</div>

<section class="section-heading split">
  <div>
    <h2 class="section-title">Semua Produk</h2>
  </div>

  {{-- Sticky Tabs Wrapper --}}
  <div class="category-bar">
    <div class="tabs">
      @php
        $tabs = ['Semua Produk', 'Pancake Durian', 'Durian Segar', 'Ice Cream'];
      @endphp

      @foreach ($tabs as $tab)
        <a href="/produk?category={{ urlencode($tab) }}"
           class="tab {{ $category === $tab ? 'active' : '' }}">
          {{ $tab }}
        </a>
      @endforeach
    </div>
  </div>
</section>

<div class="grid cards">
  @forelse ($products as $product)
    <a href="/produk/{{ $product->id }}" class="card product-card">
      <div class="thumb">
        <img
          src="{{ asset($product->image_url) }}"
          alt="{{ $product->name }}"
          loading="lazy"
          onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';"
        >
        <span class="pill pill-red">{{ $product->sold_count }} terjual</span>
      </div>

      <div class="card-body">
        <div class="row">
          <div class="rating">
            ★ {{ number_format($product->rating_avg, 1) }}
            <span class="muted">({{ $product->rating_count }})</span>
          </div>
          <div class="muted">Stok: {{ $product->stock }}</div>
        </div>

        <div class="product-name">{{ $product->name }}</div>

        <div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
      </div>
    </a>
  @empty
    <div class="empty-box">Produk tidak ditemukan untuk kategori ini.</div>
  @endforelse
</div>

{{-- CSS khusus halaman ini (boleh dipindah ke app.css kalau mau) --}}
<style>
  /* Sticky kategori/tabs */
  .category-bar{
    position: sticky;
    top: 70px; /* sesuaikan jika navbar kamu lebih tinggi/rendah */
    z-index: 30;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(10px);
    padding: 8px 0;
    border-radius: 14px;
  }

  /* efek klik tab supaya terasa interaktif */
  .category-bar .tab{
    transition: transform .12s ease, box-shadow .2s ease, background .2s ease;
  }
  .category-bar .tab:active{
    transform: scale(.97);
  }

  /* Reveal on scroll (produk muncul saat scroll) */
  .reveal {
    opacity: 0;
    transform: translateY(12px);
    transition: opacity .35s ease, transform .35s ease;
  }
  .reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
  }

  @media (prefers-reduced-motion: reduce){
    .reveal, .reveal.is-visible { transition: none; transform: none; opacity: 1; }
  }
</style>

{{-- JS untuk reveal on scroll --}}
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.product-card');
    cards.forEach(el => el.classList.add('reveal'));

    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('is-visible');
          io.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });

    cards.forEach(el => io.observe(el));
  });
</script>

@endsection