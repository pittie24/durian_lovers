@extends('layouts.app')

@section('content')
@php
  use Illuminate\Support\Str;

  $img = $product->image_url ?? '';
  $imgSrc = Str::startsWith($img, ['http://','https://'])
      ? $img
      : asset(ltrim($img, '/'));

  $backUrl = url('/produk');

  // ✅ FIX: route keranjang kamu POST /keranjang/tambah (tanpa /{id})
  $addToCartUrl = url('/keranjang/tambah');

  $ratingAvg   = $product->rating_avg ?? 0;
  $soldCount   = $product->sold_count ?? 0;
  $ratingCount = $product->rating_count ?? 0;
  $stock       = $product->stock ?? 0;

  $desc   = $product->description ?? $product->deskripsi ?? null;
  $weight = $product->weight ?? $product->berat ?? null;

  // reviews (kompatibel dengan variabel lama)
  $reviews = $reviews ?? $ratings ?? collect();

  // related products (kalau controller belum kirim, tetap aman)
  $relatedProducts = $relatedProducts ?? collect();
@endphp

<div class="product-detail-page">

  <a class="back-link" href="{{ $backUrl }}">
    <span class="back-icon">←</span>
    <span>Kembali ke Produk</span>
  </a>

  <div class="pd-grid">
    {{-- LEFT: IMAGE CARD --}}
    <div class="pd-card pd-media">
      <img
        class="pd-img"
        src="{{ $imgSrc }}"
        alt="{{ $product->name }}"
        loading="lazy"
        onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';"
      >
    </div>

    {{-- RIGHT: INFO CARD --}}
    <div class="pd-card pd-info">
      <h1 class="pd-title">{{ $product->name }}</h1>

      <div class="pd-meta">
        <span class="pd-star">★</span>
        <span class="pd-meta-text">{{ number_format($ratingAvg, 1) }}</span>

        <span class="pd-dot">•</span>
        <span class="pd-meta-muted">{{ $soldCount }} terjual</span>

        <span class="pd-dot">•</span>
        <span class="pd-meta-muted">{{ $ratingCount }} rating</span>
      </div>

      <div class="pd-price">
        Rp {{ number_format($product->price ?? 0, 0, ',', '.') }}
      </div>

      <div class="pd-section">
        <div class="pd-label">Deskripsi</div>
        <div class="pd-text">
          {{ $desc ?? 'Deskripsi produk belum tersedia.' }}
        </div>
      </div>

      <div class="pd-split">
        <div class="pd-mini">
          <div class="pd-mini-label">Berat/Ukuran</div>
          <div class="pd-mini-value">{{ $weight ?? '-' }}</div>
        </div>

        <div class="pd-mini">
          <div class="pd-mini-label">Stok Tersedia</div>
          <div class="pd-mini-value">{{ $stock }}</div> {{-- ✅ tanpa unit --}}
        </div>
      </div>

      {{-- Quantity + Add to cart --}}
      <form class="pd-cart" method="POST" action="{{ $addToCartUrl }}">
        @csrf

        {{-- ✅ FIX: kirim product_id biar CartController tahu produk mana --}}
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        <div class="pd-qty">
          <div class="pd-label">Jumlah</div>

          <div class="qty-stepper">
            <button type="button" class="qty-btn" onclick="qtyStep(-1)">−</button>
            <input id="qty" class="qty-input" type="number" name="quantity" value="1" min="1" max="{{ max(1,$stock) }}">
            <button type="button" class="qty-btn" onclick="qtyStep(1)">+</button>
          </div>
        </div>

        <button type="submit" class="pd-add" {{ $stock <= 0 ? 'disabled' : '' }}>
          <span class="pd-cart-icon">🛒</span>
          Tambah ke Keranjang
        </button>

        @if($stock <= 0)
          <div class="pd-out">Stok habis.</div>
        @endif
      </form>
    </div>
  </div>

  {{-- RATING & ULASAN --}}
  <div class="pd-reviews">
    <h2 class="pd-h2">Rating &amp; Ulasan</h2>

    <div class="pd-card pd-review-card">
      @forelse($reviews as $rev)
        @php
          $stars = (int) ($rev->rating ?? 0);
          $date = optional($rev->created_at)->format('d/m/Y');
          $text = $rev->comment ?? $rev->ulasan ?? '';
        @endphp

        <div class="review-row">
          <div class="review-stars">
            @for($i=1;$i<=5;$i++)
              <span class="{{ $i <= $stars ? 'on' : '' }}">★</span>
            @endfor
            <span class="review-date">{{ $date }}</span>
          </div>

          <div class="review-text">{{ $text }}</div>
        </div>

        <div class="review-divider"></div>
      @empty
        <div class="review-empty">Belum ada ulasan untuk produk ini.</div>
      @endforelse
    </div>
  </div>

  {{-- PRODUK TERKAIT --}}
  <div class="pd-related">
    <h2 class="pd-h2">Produk Terkait</h2>

    <div class="grid cards related-cards">
      @forelse ($relatedProducts as $rp)
        @php
          $rpImg = $rp->image_url ?? '';
          $rpImgSrc = Str::startsWith($rpImg, ['http://','https://'])
              ? $rpImg
              : asset(ltrim($rpImg, '/'));
        @endphp

        <a href="/produk/{{ $rp->id }}" class="card product-card">
          <div class="thumb">
            <img
              src="{{ $rpImgSrc }}"
              alt="{{ $rp->name }}"
              loading="lazy"
              onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';"
            >
            <span class="pill pill-red">{{ $rp->sold_count }} terjual</span>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="rating">★ {{ number_format($rp->rating_avg ?? 0, 1) }}</div>
            </div>
            <div class="product-name">{{ $rp->name }}</div>
            <div class="price">Rp {{ number_format($rp->price ?? 0, 0, ',', '.') }}</div>
          </div>
        </a>
      @empty
        <div class="empty-box">Belum ada produk terkait.</div>
      @endforelse
    </div>
  </div>

</div>

<script>
  function qtyStep(delta){
    const input = document.getElementById('qty');
    const min = parseInt(input.min || '1', 10);
    const max = parseInt(input.max || '9999', 10);
    let v = parseInt(input.value || '1', 10);
    v = v + delta;
    if (v < min) v = min;
    if (v > max) v = max;
    input.value = v;
  }
</script>

<style>
  /* Gambar detail biar tidak kebesaran */
  .pd-media { padding: 14px; }
  .pd-img{
    width: 100%;
    height: 340px;          /* ✅ lebih kecil dari 420 */
    object-fit: cover;
    border-radius: 18px;
    display: block;
  }

  /* Tombol disabled stok habis */
  .pd-add[disabled]{
    opacity: .6;
    cursor: not-allowed;
  }
  .pd-out{
    margin-top: 10px;
    font-size: 13px;
    color: #b45309;
  }

  /* Spacing Produk Terkait */
  .pd-related { margin-top: 22px; }
  .related-cards { margin-top: 10px; }
</style>
@endsection