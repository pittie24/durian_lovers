@extends('layouts.app')

@section('content')
@php
  use Illuminate\Support\Str;
@endphp

<div class="history-wrap">
  <div class="history-title">
    <span class="history-icon">🕒</span>
    <h1>Riwayat Pesanan</h1>
  </div>

  @forelse($orders as $order)
    @php
      $orderCode = $order->order_code ?? ('ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT));
      $dateText  = optional($order->created_at)->translatedFormat('d F Y');

      $statusRaw = strtolower(trim($order->status ?? ''));

      $statusClass =
        Str::contains($statusRaw, 'selesai') ? 'done' :
        (Str::contains($statusRaw, 'ship') || Str::contains($statusRaw, 'kirim') ? 'shipped' :
        (Str::contains($statusRaw, 'proses') ? 'process' :
        (Str::contains($statusRaw, 'batal') ? 'cancel' : 'default')));

      $statusLabel = $order->status ?? 'Status';

      $pickupLabel  = $order->delivery_method ?? $order->metode_pengambilan ?? 'Ambil di Toko';
      $paymentLabel = $order->payment_method ?? $order->metode_pembayaran ?? 'Virtual Account';

      $total = $order->total ?? $order->total_price ?? 0;

      // supaya gampang dipakai berulang
      $items = ($order->items ?? []);
    @endphp

    <div class="order-card">

      {{-- HEADER --}}
      <div class="order-head">
        <div class="order-head-left">
          <div class="order-id">Order ID: <strong>{{ $orderCode }}</strong></div>
          <div class="order-date">{{ $dateText }}</div>
        </div>

        <div class="order-status status-{{ $statusClass }}">
          {{ $statusLabel }}
        </div>
      </div>

      {{-- BODY (items) --}}
      <div class="order-body">
        @foreach($items as $item)
          @php
            $p = $item->product ?? null;

            $img = $p->image_url ?? '';
            $imgSrc = Str::startsWith($img, ['http://','https://'])
                ? $img
                : asset(ltrim($img, '/'));

            $qty = $item->quantity ?? 1;
            $price = $item->price ?? ($p->price ?? 0);

            // ID order item untuk route rating
            // (jika field-nya berbeda, sesuaikan: $item->id biasanya sudah benar)
            $orderItemId = $item->id ?? null;
          @endphp

          <div class="order-item">
            <div class="order-thumb">
              <img
                src="{{ $imgSrc }}"
                alt="{{ $p->name ?? 'Produk' }}"
                loading="lazy"
                onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.jpg') }}';"
              >
            </div>

            <div class="order-item-info">
              <div class="order-item-name">{{ $p->name ?? 'Produk' }}</div>

              <div class="order-item-meta">
                <span>Jumlah: {{ $qty }}</span>
                <span class="dot">•</span>
                <span>Rp {{ number_format($price,0,',','.') }}</span>
              </div>

              {{-- ✅ Tombol rating per item (hanya kalau status selesai) --}}
              @if(Str::contains($statusRaw, 'selesai') && $orderItemId)
                <div style="margin-top:10px;">
                  <a class="btn-rating" href="{{ route('rating.create', $orderItemId) }}">
                    ⭐ Ubah Rating
                  </a>
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>

      {{-- FOOTER --}}
      <div class="order-foot">
        <div class="order-foot-left">
          <div class="foot-row">
            <div class="foot-label">Metode:</div>
            <div class="foot-value">{{ $pickupLabel }}</div>
          </div>
          <div class="foot-row">
            <div class="foot-label">Pembayaran:</div>
            <div class="foot-value">{{ $paymentLabel }}</div>
          </div>
        </div>

        <div class="order-foot-right">
          <div class="total-box">
            <div class="total-label">Total Pembayaran</div>
            <div class="total-value">Rp {{ number_format($total,0,',','.') }}</div>
          </div>
        </div>
      </div>

    </div>
  @empty
    <div class="order-empty">
      Belum ada pesanan.
    </div>
  @endforelse
</div>
@endsection