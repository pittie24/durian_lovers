@extends('layouts.app')

@section('content')
<section class="section-heading">
    <h2>Produk Terlaris</h2>
    <p>Favorit pelanggan minggu ini.</p>
</section>

<div class="grid cards">
    @foreach ($topProducts as $product)
        <a href="/produk/{{ $product->id }}" class="card product-card">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            <div class="card-body">
                <span class="badge small">{{ $product->sold_count }} terjual</span>
                <h4>{{ $product->name }}</h4>
                <div class="rating">★ {{ number_format($product->rating_avg, 1) }}</div>
                <div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                <div class="stock">Stok: {{ $product->stock }}</div>
            </div>
        </a>
    @endforeach
</div>

<section class="section-heading">
    <h2>Semua Produk</h2>
    <div class="tabs">
        @php
            $tabs = ['Semua Produk', 'Pancake Durian', 'Durian Segar', 'Ice Cream'];
        @endphp
        @foreach ($tabs as $tab)
            <a href="/produk?category={{ urlencode($tab) }}" class="tab {{ $category === $tab ? 'active' : '' }}">
                {{ $tab }}
            </a>
        @endforeach
    </div>
</section>

<div class="grid cards">
    @foreach ($products as $product)
        <a href="/produk/{{ $product->id }}" class="card product-card">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
            <div class="card-body">
                <h4>{{ $product->name }}</h4>
                <div class="rating">★ {{ number_format($product->rating_avg, 1) }} ({{ $product->rating_count }})</div>
                <div class="sold">{{ $product->sold_count }} terjual</div>
                <div class="price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                <div class="stock">Stok: {{ $product->stock }}</div>
            </div>
        </a>
    @endforeach
</div>
@endsection
