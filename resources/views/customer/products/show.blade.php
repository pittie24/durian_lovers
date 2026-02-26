@extends('layouts.app')

@section('content')
@php
  use Illuminate\Support\Str;

  $img = $product->image_url ?? '';
  $imgSrc = Str::startsWith($img, ['http://','https://'])
      ? $img
      : asset(ltrim($img, '/'));

  $backUrl = url('/produk');

  // route keranjang POST /keranjang/tambah
  $addToCartUrl = url('/keranjang/tambah');

  $ratingAvg   = (float) ($product->rating_avg ?? 0);
  $soldCount   = (int) ($product->sold_count ?? 0);
  $ratingCount = (int) ($product->rating_count ?? 0);
  $stock       = (int) ($product->stock ?? 0);

  $desc   = $product->description ?? $product->deskripsi ?? null;
  $weight = $product->weight ?? $product->berat ?? null;

  // reviews
  $reviews = $reviews ?? $ratings ?? collect();

  // related products
  $relatedProducts = $relatedProducts ?? collect();

  // rating avg untuk bintang (dibulatkan ke 0.5)
  $avgRounded = round($ratingAvg * 2) / 2; // contoh 4.2 -> 4.0, 4.3 -> 4.5
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
        {{-- STAR DISPLAY (AVG) --}}
        <div class="stars stars-sm" aria-label="Rating rata-rata {{ number_format($ratingAvg, 1) }} dari 5">
          @for($i=1;$i<=5;$i++)
            @php
              // penuh jika i <= avgRounded
              $full = ($i <= floor($avgRounded));
              // setengah jika belum penuh dan i == floor(avgRounded)+1 dan ada .5
              $half = (!$full && ($i == floor($avgRounded) + 1) && (fmod($avgRounded, 1.0) == 0.5));
            @endphp
            <span class="star {{ $full ? 'on' : '' }} {{ $half ? 'half' : '' }}">★</span>
          @endfor
        </div>

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
          <div class="pd-mini-value">{{ $stock }}</div>
        </div>
      </div>

      {{-- Quantity + Add to cart --}}
      <form class="pd-cart" method="POST" action="{{ $addToCartUrl }}">
        @csrf

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
          // fleksibel: bisa stars / rating
          $stars = (int) ($rev->stars ?? $rev->rating ?? 0);
          $stars = max(0, min(5, $stars));
          $date = optional($rev->created_at)->format('d/m/Y');

          $text = $rev->comment ?? $rev->ulasan ?? '';
          $text = trim((string) $text);
        @endphp

        <div class="review-row">
          <div class="review-stars">
            <div class="stars stars-md" aria-label="Rating {{ $stars }} dari 5">
              @for($i=1;$i<=5;$i++)
                <span class="star {{ $i <= $stars ? 'on' : '' }}">★</span>
              @endfor
            </div>
            <span class="review-date">{{ $date }}</span>
          </div>

          @if($text !== '')
            <div class="review-text">{{ $text }}</div>
          @else
            <div class="review-text muted">Tanpa komentar.</div>
          @endif
        </div>

        @if(!$loop->last)
          <div class="review-divider"></div>
        @endif
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

          $rpAvg = (float) ($rp->rating_avg ?? 0);
          $rpAvgRounded = round($rpAvg * 2) / 2;
        @endphp

        <a href="/produk/{{ $rp->id }}" class="card product-card">
          <div class="thumb">
            <img
              src="{{ $rpImgSrc }}"
              alt="{{ $rp->name }}"
              loading="lazy"
              onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';"
            >
            <span class="pill pill-red">{{ (int)($rp->sold_count ?? 0) }} terjual</span>
          </div>

          <div class="card-body">
            <div class="row">
              {{-- STAR DISPLAY (RELATED) --}}
              <div class="rating rating-stars" aria-label="Rating rata-rata {{ number_format($rpAvg, 1) }} dari 5">
                <div class="stars stars-sm">
                  @for($i=1;$i<=5;$i++)
                    @php
                      $full = ($i <= floor($rpAvgRounded));
                      $half = (!$full && ($i == floor($rpAvgRounded) + 1) && (fmod($rpAvgRounded, 1.0) == 0.5));
                    @endphp
                    <span class="star {{ $full ? 'on' : '' }} {{ $half ? 'half' : '' }}">★</span>
                  @endfor
                </div>
                <span class="rating-num">{{ number_format($rpAvg, 1) }}</span>
              </div>
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
@endsection