@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Str;

    $continueUrl = url('/produk');
    $checkoutUrl = url('/pembayaran');

    $subtotal = $summary['subtotal'] ?? 0;
    $total    = $summary['total'] ?? 0;
@endphp

<div class="cart-page">
  <div class="cart-header">
    <a class="cart-back" href="{{ $continueUrl }}">← Lanjut Belanja</a>
    <h1 class="cart-title">🛒 Keranjang Belanja</h1>
  </div>

  <div class="cart-grid">

    {{-- LEFT SIDE --}}
    <div class="cart-left">
      @forelse($cart as $item)
        @php
            $img = $item['image_url'] ?? '';
            $imgSrc = Str::startsWith($img, ['http://','https://'])
                ? $img
                : asset(ltrim($img, '/'));

            $qty = $item['quantity'];
            $price = $item['price'];
            $lineTotal = $price * $qty;
        @endphp

        <div class="cart-item-card">

          <div class="cart-item-img">
            <img src="{{ $imgSrc }}"
                 alt="{{ $item['name'] }}"
                 onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';">
          </div>

          <div class="cart-item-info">
            <div class="cart-item-name">{{ $item['name'] }}</div>

            @if(!empty($item['weight']))
              <div class="cart-item-meta">{{ $item['weight'] }}</div>
            @endif

            <div class="cart-item-price">
              Rp {{ number_format($price, 0, ',', '.') }}
            </div>

            <div class="cart-item-qtyrow">

              {{-- minus --}}
              <form method="POST" action="/keranjang/ubah-qty">
                @csrf
                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                <input type="hidden" name="quantity" value="{{ max(1, $qty-1) }}">
                <button class="qty-btn">−</button>
              </form>

              <div class="qty-pill">{{ $qty }}</div>

              {{-- plus --}}
              <form method="POST" action="/keranjang/ubah-qty">
                @csrf
                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                <input type="hidden" name="quantity" value="{{ $qty+1 }}">
                <button class="qty-btn">+</button>
              </form>

            </div>
          </div>

          <div class="cart-item-right">

            <form method="POST" action="/keranjang/hapus">
              @csrf
              <input type="hidden" name="product_id" value="{{ $item['id'] }}">
              <button class="remove-btn">🗑</button>
            </form>

            <div class="cart-item-total">
              Rp {{ number_format($lineTotal, 0, ',', '.') }}
            </div>

          </div>
        </div>

      @empty
        <div class="cart-empty">
          Keranjang kamu masih kosong.
          <a href="{{ $continueUrl }}">Belanja dulu →</a>
        </div>
      @endforelse
    </div>

    {{-- RIGHT SIDE --}}
    <div class="cart-right">
      <div class="cart-summary-card">

        <div class="summary-title">Ringkasan Belanja</div>

        <div class="summary-row">
          <div class="label">Subtotal</div>
          <div class="value">
            Rp {{ number_format($subtotal, 0, ',', '.') }}
          </div>
        </div>

        {{-- Ongkos kirim sengaja DIHILANGKAN sesuai permintaan --}}

        <div class="summary-total">
          <div class="label">Total</div>
          <div class="value">
            Rp {{ number_format($total, 0, ',', '.') }}
          </div>
        </div>

        <a href="{{ $checkoutUrl }}" class="btn-checkout">
          💳 Bayar Sekarang
        </a>

        <a href="{{ url('/status-pesanan') }}" class="btn-continue">
          Lihat Status Pesanan
        </a>

      </div>
    </div>

  </div>
</div>
@endsection