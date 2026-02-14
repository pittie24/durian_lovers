@extends('layouts.app')

@section('content')
<div class="product-detail">
    <div class="product-image">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
    </div>
    <div class="product-info">
        <h2>{{ $product->name }}</h2>
        <div class="rating">★ {{ number_format($product->rating_avg, 1) }} · {{ $product->sold_count }} terjual · {{ $product->rating_count }} rating</div>
        <div class="price large">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
        <p>{{ $product->description }}</p>
        <div class="meta">
            <div>Berat/Ukuran: {{ $product->weight }}</div>
            <div>Stok tersedia: {{ $product->stock }}</div>
            <div>Komposisi: {{ $product->composition }}</div>
        </div>
        <form method="POST" action="/keranjang/tambah" class="qty-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <label>Quantity</label>
            <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" {{ $product->stock == 0 ? 'disabled' : '' }}>
            <button type="submit" class="btn primary" {{ $product->stock == 0 ? 'disabled' : '' }}>Tambah ke Keranjang</button>
        </form>
    </div>
</div>

<section class="section-heading">
    <h3>Rating & Ulasan</h3>
</section>

<div class="card">
    <div class="card-body">
        @if ($ratings->isEmpty())
            <p>Belum ada rating untuk produk ini.</p>
        @else
            @foreach ($ratings as $rating)
                <div class="rating-item">
                    <div class="rating">★ {{ $rating->stars }}</div>
                    <p>{{ $rating->comment }}</p>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection
