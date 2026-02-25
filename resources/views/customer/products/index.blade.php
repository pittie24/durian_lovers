@extends('layouts.app')

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

@endsection
