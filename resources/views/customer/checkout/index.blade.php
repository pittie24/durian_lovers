@extends('layouts.app')

@section('content')
@php
  use Illuminate\Support\Str;

  // ===== Ambil data ringkasan dari controller (paling umum) =====
  // Kamu bisa sesuaikan kalau variabelmu beda.
  // Contoh yang sering:
  // $summary['subtotal'], $summary['total'], $cart (array session)
  $subtotal = $summary['subtotal'] ?? ($subtotal ?? 0);

  // default pilihan
  $shippingMethod = old('shipping_method', 'delivery'); // delivery | pickup
  $paymentMethod  = old('payment_method', 'bank');      // bank | ewallet | va

  // ongkir: 10k kalau delivery
  $shippingCost = ($shippingMethod === 'delivery') ? 10000 : 0;
  $total = $subtotal + $shippingCost;

  // Data item (kalau kamu punya $cart dari session seperti CartController kamu)
  // Struktur cart kamu: $cart[id] = ['name','price','quantity',...]
  $cartItems = $cart ?? ($items ?? []);
@endphp

<div class="checkout-page">
  <div class="checkout-title-wrap">
    <h1 class="checkout-title">Checkout</h1>
  </div>

  <form method="POST" action="{{ url('/checkout') }}" class="checkout-grid">
    @csrf

    {{-- LEFT --}}
    <div class="checkout-left">

      {{-- METODE PENGIRIMAN (CARD) --}}
      <div class="ck-card">
        <div class="ck-card-title">Metode Pengiriman</div>

        <div class="ship-grid">
          <label class="ship-option {{ $shippingMethod==='delivery' ? 'active' : '' }}">
            <input type="radio" name="shipping_method" value="delivery" {{ $shippingMethod==='delivery' ? 'checked' : '' }}>
            <div class="ship-ico">🚚</div>
            <div class="ship-text">
              <div class="ship-name">Dikirim</div>
              <div class="ship-sub">Ongkir 10k</div>
            </div>
          </label>

          <label class="ship-option {{ $shippingMethod==='pickup' ? 'active' : '' }}">
            <input type="radio" name="shipping_method" value="pickup" {{ $shippingMethod==='pickup' ? 'checked' : '' }}>
            <div class="ship-ico">🏪</div>
            <div class="ship-text">
              <div class="ship-name">Ambil di Toko</div>
              <div class="ship-sub">Siap 5 menit</div>
            </div>
          </label>
        </div>
      </div>

      {{-- ALAMAT PENGIRIMAN --}}
      <div class="ck-card" id="addressCard">
        <div class="ck-card-title">Alamat Pengiriman</div>

        <div class="ck-field">
          <label class="ck-label">Nomor Telepon</label>
          <input class="ck-input" type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
        </div>

        <div class="ck-field">
          <label class="ck-label">Alamat Lengkap</label>
          <textarea class="ck-textarea" name="address" rows="3" placeholder="Tulis alamat lengkap...">{{ old('address') }}</textarea>
        </div>
      </div>

{{-- METODE PEMBAYARAN --}}
<div class="ck-card">
  <div class="ck-card-title">Metode Pembayaran</div>

  <div class="pay-list">
    <label class="pay-item">
      <input type="radio" name="payment_method" value="bank" {{ $paymentMethod==='bank' ? 'checked' : '' }}>
      <div class="pay-text">
        <div class="pay-title">Transfer Bank</div>
        <div class="pay-sub">BCA, BNI, Mandiri, BRI</div>
      </div>
    </label>

    <label class="pay-item">
      <input type="radio" name="payment_method" value="ewallet" {{ $paymentMethod==='ewallet' ? 'checked' : '' }}>
      <div class="pay-text">
        <div class="pay-title">E-Wallet</div>
        <div class="pay-sub">GoPay, OVO, Dana, ShopeePay</div>
      </div>
    </label>

    <label class="pay-item">
      <input type="radio" name="payment_method" value="va" {{ $paymentMethod==='va' ? 'checked' : '' }}>
      <div class="pay-text">
        <div class="pay-title">Virtual Account</div>
        <div class="pay-sub">Nomor VA otomatis</div>
      </div>
    </label>
  </div>
</div>

      {{-- Hidden buat backend biar aman --}}
      <input type="hidden" name="shipping_cost" id="shippingCostInput" value="{{ $shippingCost }}">
      <input type="hidden" name="subtotal" value="{{ $subtotal }}">
    </div>

    {{-- RIGHT: RINGKASAN PESANAN --}}
    <div class="checkout-right">
      <div class="ck-card ck-summary">
        <div class="ck-card-title">Ringkasan Pesanan</div>

        {{-- list item ringkasan (simple seperti figma) --}}
        <div class="summary-items">
          @php $itemCount = 0; @endphp

          @foreach($cartItems as $key => $it)
            @php
              // cart session kamu bentuknya array
              $name = is_array($it) ? ($it['name'] ?? 'Produk') : ($it->name ?? 'Produk');
              $qty  = is_array($it) ? (int)($it['quantity'] ?? 1) : (int)($it->quantity ?? 1);
              $price= is_array($it) ? (int)($it['price'] ?? 0) : (int)($it->price ?? 0);

              $itemCount += $qty;
            @endphp

            <div class="summary-item">
              <div class="summary-item-name">{{ $name }} <span class="muted">x{{ $qty }}</span></div>
              <div class="summary-item-price">Rp {{ number_format($price*$qty, 0, ',', '.') }}</div>
            </div>
          @endforeach
        </div>

        <div class="summary-divider"></div>

        <div class="summary-row">
          <div class="label">Subtotal <span class="muted">({{ $itemCount }} item)</span></div>
          <div class="value" id="subtotalText">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
        </div>

        <div class="summary-row" id="shippingRow">
          <div class="label">Ongkos Kirim</div>
          <div class="value" id="shippingText">Rp {{ number_format($shippingCost, 0, ',', '.') }}</div>
        </div>

        <div class="summary-total">
          <div class="label">Total</div>
          <div class="value" id="totalText">Rp {{ number_format($total, 0, ',', '.') }}</div>
        </div>

        <button type="submit" class="btn-pay">Bayar Sekarang</button>
      </div>
    </div>
  </form>
</div>

<style>
  /* ====== layout utama ====== */
  .checkout-page{ max-width: 1100px; margin: 0 auto; padding: 24px 18px 40px; }
  .checkout-title-wrap{ margin: 6px 0 14px; }
  .checkout-title{ font-size: 28px; font-weight: 800; margin: 0; }
  .checkout-grid{ display: grid; grid-template-columns: 1.35fr .85fr; gap: 18px; align-items: start; }

  .ck-card{
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 10px 26px rgba(0,0,0,.06);
    padding: 16px;
    margin-bottom: 14px;
  }
  .ck-card-title{ font-size: 16px; font-weight: 800; margin-bottom: 12px; }

  /* ====== Metode pengiriman (card option) ====== */
  .ship-grid{ display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .ship-option{
    border: 1px solid #ececec;
    border-radius: 14px;
    padding: 12px 12px;
    display: grid;
    grid-template-columns: 36px 1fr;
    gap: 10px;
    cursor: pointer;
    transition: .15s ease;
    background: #fff;
  }
  .ship-option input{ display:none; }
  .ship-option .ship-ico{ font-size: 20px; display:flex; align-items:center; justify-content:center; }
  .ship-name{ font-weight: 800; font-size: 13px; }
  .ship-sub{ font-size: 12px; color: #6b7280; margin-top: 2px; }
  .ship-option.active{
    background: #fff7e6;
    border-color: #d79b2e;
    box-shadow: 0 10px 20px rgba(215,155,46,.15);
  }

  /* ====== field ====== */
  .ck-field{ margin-top: 10px; }
  .ck-label{ display:block; font-size: 12px; color:#374151; margin-bottom: 6px; font-weight: 700; }
  .ck-input, .ck-textarea{
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 12px;
    outline: none;
    font-size: 13px;
    background: #fff;
  }
  .ck-textarea{ resize: vertical; }

  /* ====== payment rows ====== */
  .pay-row{
    display:flex;
    gap: 10px;
    padding: 10px 10px;
    border-radius: 12px;
    border: 1px solid #eee;
    margin-top: 10px;
    cursor:pointer;
    align-items:flex-start;
  }
  .pay-row input{ margin-top: 3px; }
  .pay-name{ font-weight: 800; font-size: 13px; }
  .pay-sub{ font-size: 12px; color:#6b7280; margin-top: 2px; }

  /* ====== summary kanan biar gak berdempetan ====== */
  .ck-summary .ck-card-title{ margin-bottom: 10px; }
  .summary-items{ display:flex; flex-direction:column; gap: 8px; }
  .summary-item{ display:flex; justify-content:space-between; gap: 12px; font-size: 12.5px; }
  .summary-item-name{ color:#111827; }
  .summary-item-price{ color:#111827; font-weight:700; }
  .muted{ color:#6b7280; font-weight:600; }
  .summary-divider{ height:1px; background:#f0f0f0; margin: 12px 0; }
  .summary-row{ display:flex; justify-content:space-between; align-items:center; font-size: 12.5px; padding: 6px 0; }
  .summary-row .value{ font-weight: 800; }
  .summary-total{
    display:flex; justify-content:space-between; align-items:center;
    margin-top: 10px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
    font-size: 13px;
  }
  .summary-total .value{ font-weight: 900; color:#c27a00; }

  .btn-pay{
    width:100%;
    border:0;
    margin-top: 12px;
    background:#c27a00;
    color:#fff;
    padding: 12px 14px;
    border-radius: 12px;
    font-weight: 800;
    cursor:pointer;
  }
  .btn-pay:hover{ filter: brightness(.97); }

  @media (max-width: 980px){
    .checkout-grid{ grid-template-columns: 1fr; }
  }
</style>

<script>
  (function(){
    const shipRadios = document.querySelectorAll('input[name="shipping_method"]');
    const shipOptions = document.querySelectorAll('.ship-option');
    const shippingCostInput = document.getElementById('shippingCostInput');
    const shippingRow = document.getElementById('shippingRow');
    const shippingText = document.getElementById('shippingText');
    const totalText = document.getElementById('totalText');
    const subtotal = {{ (int)$subtotal }};
    const addressCard = document.getElementById('addressCard');

    function rupiah(n){
      return 'Rp ' + (n || 0).toLocaleString('id-ID');
    }

    function setActive(){
      let method = 'delivery';
      shipRadios.forEach(r => { if (r.checked) method = r.value; });

      shipOptions.forEach(opt => opt.classList.remove('active'));
      document.querySelectorAll('.ship-option input').forEach(input => {
        if(input.checked) input.closest('.ship-option').classList.add('active');
      });

      const shipCost = (method === 'delivery') ? 10000 : 0;

      shippingCostInput.value = shipCost;
      shippingText.textContent = rupiah(shipCost);
      totalText.textContent = rupiah(subtotal + shipCost);

      // kalau pickup, sembunyikan alamat (sesuai figma)
      if(method === 'pickup'){
        addressCard.style.display = 'none';
      }else{
        addressCard.style.display = '';
      }
    }

    shipRadios.forEach(r => r.addEventListener('change', setActive));
    setActive();
  })();
</script>
@endsection