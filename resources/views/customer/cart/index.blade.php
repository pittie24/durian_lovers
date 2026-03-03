@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;

    $continueUrl = url('/produk');
    $checkoutUrl = url('/pembayaran');

    $subtotal = $summary['subtotal'] ?? 0;
    $total = $summary['total'] ?? 0;
    $totalItems = collect($cart ?? [])->sum(function ($item) {
        return (int) ($item['quantity'] ?? 0);
    });
@endphp

<div class="cart-page container">

    {{-- Header --}}
    <div class="cart-header">
        <div class="cart-header-title">
            <h1 class="cart-title">Keranjang Belanja</h1>
            <div class="cart-subtitle">Periksa pesananmu sebelum lanjut ke pembayaran.</div>
        </div>
    </div>

    <div class="cart-layout">

        {{-- LEFT --}}
        <div class="cart-left">

            @forelse($cart as $item)
                @php
                    $img = $item['image_url'] ?? '';
                    $imgSrc = Str::startsWith($img, ['http://', 'https://'])
                        ? $img
                        : asset(ltrim($img, '/'));

                    $qty = (int) ($item['quantity'] ?? 1);
                    $price = (int) ($item['price'] ?? 0);
                    $lineTotal = $price * $qty;
                    $isMinQty = $qty <= 1;
                @endphp

                <div class="cart-item">

                    <div class="cart-thumb">
                        <img src="{{ $imgSrc }}"
                             alt="{{ $item['name'] }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';">
                    </div>

                    <div class="cart-info">
                        <div class="cart-name">{{ $item['name'] }}</div>

                        @if(!empty($item['weight']))
                            <div class="cart-meta">{{ $item['weight'] }}</div>
                        @endif

                        <div class="cart-price">Rp {{ number_format($price, 0, ',', '.') }}</div>

                        <div class="cart-qty">
                            <form method="POST" action="/keranjang/ubah-qty">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                <input type="hidden" name="quantity" value="{{ max(1, $qty - 1) }}">
                                <button class="qty-btn" type="submit" {{ $isMinQty ? 'disabled' : '' }} aria-label="Kurangi jumlah">
                                    -
                                </button>
                            </form>

                            <div class="qty-pill" aria-label="Jumlah">{{ $qty }}</div>

                            <form method="POST" action="/keranjang/ubah-qty">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                <input type="hidden" name="quantity" value="{{ $qty + 1 }}">
                                <button class="qty-btn" type="submit" aria-label="Tambah jumlah">
                                    +
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <form method="POST" action="/keranjang/hapus" class="remove-form">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                            <button class="remove-btn" type="submit" aria-label="Hapus item">
                                <span class="remove-text">Hapus</span>
                            </button>
                        </form>

                        <div class="cart-line-total">
                            Rp {{ number_format($lineTotal, 0, ',', '.') }}
                        </div>
                    </div>

                </div>

            @empty
                <div class="cart-empty">
                    <div class="empty-title">Keranjang kamu masih kosong</div>
                    <div class="empty-desc">Yuk, pilih durian favoritmu biar bisa langsung checkout.</div>
                    <a class="empty-cta" href="{{ $continueUrl }}">Mulai Belanja</a>
                </div>
            @endforelse

        </div>

        <div class="cart-right">
            <div class="summary-card cart-summary">

                <div class="summary-head">
                    <div class="summary-title">Ringkasan Belanja</div>
                </div>

                <div class="summary-row">
                    <div class="label">Total Item</div>
                    <div class="value">{{ $totalItems }}</div>
                </div>

                <div class="summary-divider"></div>

                <div class="summary-row total">
                    <div class="label">Total Belanja</div>
                    <div class="value total-value">Rp {{ number_format($total, 0, ',', '.') }}</div>
                </div>

                <a href="{{ $checkoutUrl }}" class="btn-checkout {{ ($total <= 0) ? 'disabled' : '' }}">
                    Bayar Sekarang
                </a>

                <a href="{{ $continueUrl }}" class="btn-secondary">
                    Lanjut Belanja
                </a>

            </div>
        </div>

    </div>
</div>
<style>
    .cart-page .cart-header {
        display: block;
        position: static;
        margin-bottom: 18px;
    }

    .cart-page .cart-header-title {
        position: static;
        left: auto;
        transform: none;
        width: 100%;
        max-width: 100%;
        text-align: left;
    }
</style>
@endsection
