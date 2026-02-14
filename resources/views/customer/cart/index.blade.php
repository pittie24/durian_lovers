@extends('layouts.app')

@section('content')
<h2>Keranjang Belanja</h2>

<div class="cart-layout">
    <div class="cart-items">
        @forelse ($cart as $item)
            <div class="cart-item">
                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}">
                <div class="cart-info">
                    <h4>{{ $item['name'] }}</h4>
                    <div class="price">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                    <div class="muted">{{ $item['weight'] }}</div>
                </div>
                <form action="/keranjang/ubah-qty" method="POST" class="qty-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                    <input type="number" name="quantity" min="1" value="{{ $item['quantity'] }}">
                    <button type="submit" class="btn outline small">Update</button>
                </form>
                <form action="/keranjang/hapus" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                    <button type="submit" class="icon-button">🗑</button>
                </form>
            </div>
        @empty
            <div class="card">
                <div class="card-body">
                    Keranjang masih kosong.
                </div>
            </div>
        @endforelse
    </div>

    <div class="summary-card">
        <h3>Ringkasan Belanja</h3>
        <div class="summary-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Ongkos kirim</span>
            <span>{{ $summary['shipping'] == 0 ? 'Gratis' : 'Rp ' . number_format($summary['shipping'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row total">
            <span>Total</span>
            <span>Rp {{ number_format($summary['total'], 0, ',', '.') }}</span>
        </div>
        <a href="/checkout" class="btn primary full">Lanjut ke Checkout</a>
    </div>
</div>
@endsection
