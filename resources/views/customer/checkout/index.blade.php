@extends('layouts.app')

@section('content')
@php
  $shippingMethod = old('shipping_method', 'delivery');
  $cartItems = $cart ?? [];
  $promotion = $promotion ?? [];
  $freeItem = $promotion['free_item'] ?? null;
  $subtotal = $summary['subtotal'] ?? 0;
  $totalItems = collect($cartItems)->sum(function ($item) {
    return (int) ($item['quantity'] ?? 0);
  });
  $shippingCost = ($shippingMethod === 'delivery') ? 10000 : 0;
  $total = $subtotal + $shippingCost;
  $defaultPhone = old('phone', auth()->user()->phone ?? '');
  $defaultAddress = old('address', auth()->user()->address ?? '');
@endphp

<div class="checkout-page">
  {{-- Steps / Progress --}}
  <div class="ck-steps">
    <div class="ck-step done">
      <span class="dot">✓</span><span>Keranjang</span>
    </div>
    <div class="ck-step active">
      <span class="dot">2</span><span>Pembayaran</span>
    </div>
    <div class="ck-step">
      <span class="dot">3</span><span>Verifikasi</span>
    </div>
  </div>

  <h1 class="checkout-title">Pembayaran</h1>
  <div class="checkout-promo {{ !empty($promotion['is_awarded']) ? 'qualified' : '' }}">
    @if(!empty($promotion['is_awarded']) && $freeItem)
      Promo aktif: kamu mendapatkan free item <strong>{{ $freeItem['name'] }}</strong>.
    @else
      Belanja minimal Rp {{ number_format($promotion['threshold'] ?? 300000, 0, ',', '.') }} untuk gratis
      <strong>{{ $promotion['free_item_name'] ?? 'Pancake Durian Mini' }}</strong>.
    @endif
  </div>

  <form method="POST" action="{{ url('/pembayaran') }}" class="checkout-grid" enctype="multipart/form-data">
    @csrf

    {{-- LEFT --}}
    <div class="checkout-left">

      {{-- METODE PENGIRIMAN --}}
      <div class="ck-card">
        <div class="ck-card-title">Metode Pengiriman</div>
        <div class="ship-grid">
          <label class="ship-option {{ $shippingMethod==='delivery' ? 'active' : '' }}">
            <input type="radio" name="shipping_method" value="delivery" {{ $shippingMethod==='delivery' ? 'checked' : '' }}>
            <div class="ship-ico">🚚</div>
            <div class="ship-text">
              <div class="ship-name">Dikirim</div>
              <div class="ship-sub">Ongkir Rp 10.000</div>
            </div>
            <div class="ship-check">✓</div>
          </label>

          <label class="ship-option {{ $shippingMethod==='pickup' ? 'active' : '' }}">
            <input type="radio" name="shipping_method" value="pickup" {{ $shippingMethod==='pickup' ? 'checked' : '' }}>
            <div class="ship-ico">🏪</div>
            <div class="ship-text">
              <div class="ship-name">Ambil di Toko</div>
              <div class="ship-sub">Gratis</div>
            </div>
            <div class="ship-check">✓</div>
          </label>
        </div>
      </div>

      {{-- ALAMAT PENGIRIMAN --}}
      <div class="ck-card" id="addressCard">
        <div class="ck-card-title">Alamat Pengiriman</div>

        <div class="ck-field">
          <label class="ck-label">Nomor Telepon</label>
          <input
            class="ck-input"
            type="text"
            name="phone"
            value="{{ $defaultPhone }}"
            placeholder="08xxxxxxxxxx"
            required
            id="phoneInput"
            data-default-value="{{ $defaultPhone }}"
          >
        </div>

        <div class="ck-field">
          <label class="ck-label">Alamat Lengkap</label>
          <textarea
            class="ck-textarea"
            name="address"
            rows="3"
            placeholder="Tulis alamat lengkap..."
            required
            id="addressInput"
            data-default-value="{{ $defaultAddress }}"
          >{{ $defaultAddress }}</textarea>
        </div>
      </div>

      {{-- METODE PEMBAYARAN --}}
      <div class="ck-card">
        <div class="ck-card-title">Metode Pembayaran</div>
        <p class="payment-info-text">Silakan transfer ke salah satu rekening berikut:</p>

        <div class="bank-item">
          <div class="bank-icon">🏦</div>
          <div class="bank-info">
            <div class="bank-name">BRI</div>
            <div class="bank-number">341901058068539</div>
            <div class="bank-holder">a.n Durian Lovers</div>
          </div>
          <button type="button" class="btn-copy" data-copy="341901058068539">📋 Copy</button>
        </div>

        <div class="bank-item">
          <div class="bank-icon">📱</div>
          <div class="bank-info">
            <div class="bank-name">DANA</div>
            <div class="bank-number">081352953905</div>
            <div class="bank-holder">a.n Durian Lovers</div>
          </div>
          <button type="button" class="btn-copy" data-copy="081352953905">📋 Copy</button>
        </div>

        <div class="ck-field mt-3">
          <label class="ck-label">Pilih Metode Pembayaran</label>
          <select name="payment_method" class="ck-input" required>
            <option value="">-- Pilih --</option>
            <option value="BRI">Transfer BRI</option>
            <option value="DANA">DANA</option>
          </select>
        </div>
      </div>

      {{-- UPLOAD BUKTI PEMBAYARAN --}}
      <div class="ck-card">
        <div class="ck-card-title">📷 Upload Bukti Pembayaran</div>

        <div class="ck-field">
          <label class="ck-label">Nama Pengirim</label>
          <input class="ck-input" type="text" name="account_name" value="{{ auth()->user()->name }}" required>
        </div>

        <div class="ck-field">
          <label class="ck-label">Nominal Transfer (Rp)</label>
          <input class="ck-input" type="number" name="transfer_amount" value="{{ old('transfer_amount', $total) }}" required id="transferAmountInput">
        </div>

        <div class="ck-field">
          <label class="ck-label">Upload Bukti Transfer</label>
          <input class="ck-input" type="file" name="proof_image" accept="image/*" required id="proofInput">
          <small class="ck-hint">Format: JPG, PNG. Maksimal 2MB</small>

          {{-- TANPA PREVIEW: hanya nama file + tombol hapus --}}
          <div class="proof-file-info" id="proofFileInfo" style="display:none;">
            <span class="proof-filename" id="proofFileName">-</span>
            <button type="button" class="proof-remove" id="proofRemoveBtn">Hapus</button>
          </div>
        </div>

        <div class="alert-info">
          <strong>📌 Penting:</strong>
          <ul class="mb-0 mt-1">
            <li>Pastikan nominal transfer sesuai dengan total pembayaran.</li>
            <li>Gunakan nama Anda saat melakukan transfer.</li>
            <li>Bukti akan diverifikasi oleh admin dalam 1-24 jam.</li>
          </ul>
        </div>
      </div>

    </div>

    {{-- RIGHT --}}
    <div class="checkout-right">
      <div class="order-summary">
        <h3 class="summary-title">Ringkasan Pesanan</h3>

        <div class="summary-items">
          @foreach($cartItems as $id => $item)
            <div class="summary-item">
              <div class="item-info">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-qty">{{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
              </div>
              <div class="item-total">Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</div>
            </div>
          @endforeach

          @if($freeItem)
            <div class="summary-item free-item-row">
              <div class="item-info">
                <div class="item-name">{{ $freeItem['name'] }}</div>
                <div class="item-qty">1 x Rp 0 <span class="free-item-tag">Free Item</span></div>
              </div>
              <div class="item-total free">Gratis</div>
            </div>
          @endif
        </div>

        <div class="summary-divider"></div>

        <div class="summary-row">
          <span>Total Item</span>
          <span>
            {{ $totalItems }}
            <span id="subtotalAmount" data-subtotal="{{ (int) $subtotal }}" style="display:none;"></span>
          </span>
        </div>

        <div class="summary-row">
          <span>Ongkos Kirim</span>
          <span id="shippingCost">Rp {{ number_format($shippingCost, 0, ',', '.') }}</span>
        </div>

        <div class="summary-divider"></div>

        <div class="summary-total">
          <span>Total Pembayaran</span>
          <span id="totalAmount">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>

        <button type="submit" class="btn-checkout" id="payBtn">
          ✅ Buat Pesanan & Bayar
        </button>

        <div class="summary-safe">
          🔒 Data pembayaran kamu aman. Admin akan verifikasi bukti transfer.
        </div>
      </div>
    </div>

  </form>
</div>

{{-- Toast --}}
<div class="toast" id="toast" aria-live="polite" aria-atomic="true"></div>

<style>
  .checkout-promo{
    margin: 0 0 18px;
    padding: 12px 14px;
    border-radius: 14px;
    background: #fff7e6;
    border: 1px solid rgba(245,158,11,.18);
    color: #8a5a00;
    font-size: 13px;
    font-weight: 800;
  }

  .checkout-promo.qualified{
    background: #ecfdf3;
    border-color: rgba(22,163,74,.18);
    color: #166534;
  }

  /* Page */
  .checkout-page{ max-width: 1100px; margin: 0 auto; }
  .checkout-title{ font-size: 28px; font-weight: 800; color: #1f2937; margin: 8px 0 18px; letter-spacing: -.02em; }

  /* Steps */
  .ck-steps{
    display:flex;
    gap: 10px;
    align-items:center;
    margin: 8px 0 10px;
    flex-wrap: wrap;
  }
  .ck-step{
    display:flex;
    align-items:center;
    gap:10px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.75);
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 10px 22px rgba(0,0,0,.05);
    font-weight: 800;
    font-size: 13px;
    color: rgba(31,41,55,.72);
  }
  .ck-step .dot{
    width: 22px;
    height: 22px;
    border-radius: 999px;
    display:flex;
    align-items:center;
    justify-content:center;
    background: rgba(0,0,0,.06);
    color: rgba(31,41,55,.85);
    font-size: 12px;
  }
  .ck-step.active{
    background: rgba(244,180,0,.16);
    border-color: rgba(244,180,0,.25);
    color:#6b4b00;
  }
  .ck-step.active .dot{ background: #f4b400; color:#fff; }
  .ck-step.done{
    background: rgba(34,197,94,.14);
    border-color: rgba(34,197,94,.18);
    color:#14532d;
  }
  .ck-step.done .dot{ background: #22c55e; color:#fff; }

  /* Layout */
  .checkout-grid{ display:grid; grid-template-columns: 1fr 380px; gap: 24px; }
  @media (max-width: 900px){ .checkout-grid{ grid-template-columns: 1fr; } }

  /* Cards */
  .ck-card{
    background:#fff;
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 16px;
    box-shadow: 0 14px 30px rgba(0,0,0,.06);
    border: 1px solid rgba(0,0,0,.04);
  }
  .ck-card-title{ font-size: 16px; font-weight: 900; color:#111827; margin-bottom: 14px; }

  /* Shipping options */
  .ship-grid{ display:grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  @media (max-width: 520px){ .ship-grid{ grid-template-columns: 1fr; } }

  .ship-option{
    display:flex; align-items:center; gap: 12px;
    padding: 14px;
    border: 2px solid #f1f5f9;
    border-radius: 12px;
    cursor:pointer;
    transition: transform .15s ease, box-shadow .15s ease, border-color .2s ease, background .2s ease;
    position:relative;
    background:#fff;
  }
  .ship-option:hover{
    transform: translateY(-3px);
    box-shadow: 0 16px 30px rgba(0,0,0,.08);
  }
  .ship-option.active{
    border-color: rgba(34,197,94,.55);
    background: rgba(34,197,94,.08);
  }
  .ship-option input{ position:absolute; opacity:0; pointer-events:none; }
  .ship-ico{ font-size: 26px; }
  .ship-name{ font-weight: 900; color:#111827; }
  .ship-sub{ font-size: 12px; color: rgba(17,24,39,.65); font-weight: 700; }
  .ship-check{
    margin-left:auto;
    width: 22px; height: 22px;
    border-radius: 999px;
    display:flex; align-items:center; justify-content:center;
    background: rgba(0,0,0,.06);
    color: transparent;
    font-weight: 900;
    transition: .2s ease;
  }
  .ship-option.active .ship-check{
    background: #22c55e;
    color:#fff;
  }

  /* Fields */
  .ck-field{ margin-bottom: 14px; }
  .ck-label{ display:block; font-weight: 800; color:#111827; margin-bottom: 6px; font-size: 13px; }
  .ck-input, .ck-textarea{
    width:100%;
    padding: 12px;
    border: 1px solid rgba(231,220,200,.9);
    border-radius: 12px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: box-shadow .15s ease, border-color .15s ease, transform .15s ease;
    background:#fff;
  }
  .ck-input:focus, .ck-textarea:focus{
    border-color: rgba(244,180,0,.65);
    box-shadow: 0 0 0 4px rgba(244,180,0,.16);
  }
  .ck-hint{ display:block; font-size: 12px; color: rgba(17,24,39,.55); margin-top: 6px; font-weight: 700; }

  /* Bank items */
  .payment-info-text{ color: rgba(17,24,39,.6); font-size: 13px; margin-bottom: 12px; font-weight: 700; }
  .bank-item{
    display:flex; align-items:center; gap: 14px;
    padding: 14px;
    background: rgba(248,250,252,.8);
    border: 1px solid rgba(0,0,0,.04);
    border-radius: 12px;
    margin-bottom: 10px;
    transition: transform .15s ease, box-shadow .15s ease;
  }
  .bank-item:hover{
    transform: translateY(-2px);
    box-shadow: 0 14px 26px rgba(0,0,0,.06);
  }
  .bank-icon{ font-size: 28px; }
  .bank-info{ flex:1; }
  .bank-name{ font-weight: 900; color:#111827; }
  .bank-number{ font-size: 15px; font-weight: 900; color: #16a34a; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  .bank-holder{ font-size: 12px; color: rgba(17,24,39,.6); font-weight: 700; }

  .btn-copy{
    padding: 10px 12px;
    background: rgba(244,180,0,.18);
    color: #6b4b00;
    border: 1px solid rgba(244,180,0,.25);
    border-radius: 12px;
    cursor:pointer;
    font-weight: 900;
    transition: transform .12s ease, filter .2s ease;
    white-space: nowrap;
  }
  .btn-copy:hover{ filter: brightness(.98); }
  .btn-copy:active{ transform: scale(.98); }
  .btn-copy.copied{
    background: rgba(34,197,94,.14);
    border-color: rgba(34,197,94,.2);
    color:#14532d;
  }

  /* TANPA PREVIEW (hanya nama file + tombol hapus) */
  .proof-file-info{
    margin-top: 10px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 12px;
    background: rgba(248,250,252,.8);
    border: 1px solid rgba(0,0,0,.06);
    font-size: 13px;
    font-weight: 800;
  }
  .proof-filename{
    font-size: 12px;
    font-weight: 800;
    color: rgba(17,24,39,.75);
    overflow:hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .proof-remove{
    border: 0;
    background: rgba(239,68,68,.12);
    color:#991b1b;
    font-weight: 900;
    border-radius: 12px;
    padding: 8px 10px;
    cursor:pointer;
  }
  .proof-remove:active{ transform: scale(.98); }

  /* Alert info */
  .alert-info{
    background: rgba(59,130,246,.10);
    border: 1px solid rgba(59,130,246,.18);
    color: #0b3a63;
    padding: 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
  }
  .alert-info ul{ margin: 10px 0 0 18px; padding: 0; }
  .alert-info li{ margin-bottom: 6px; }

  /* Summary */
  .order-summary{
    background:#fff;
    border-radius: 16px;
    padding: 18px;
    box-shadow: 0 16px 34px rgba(0,0,0,.08);
    position: sticky;
    top: 20px;
    border: 1px solid rgba(0,0,0,.04);
  }
  .summary-title{ font-size: 16px; font-weight: 900; color:#111827; margin-bottom: 14px; }
  .summary-item{ display:flex; justify-content:space-between; gap: 12px; margin-bottom: 10px; }
  .free-item-row{
    padding: 10px 12px;
    border-radius: 12px;
    background: #f0fdf4;
    border: 1px solid rgba(22,163,74,.12);
  }
  .item-name{ font-weight: 900; color:#111827; }
  .item-qty{ font-size: 12px; color: rgba(17,24,39,.6); font-weight: 700; margin-top: 2px; }
  .item-total{ font-weight: 900; color:#16a34a; white-space: nowrap; }
  .item-total.free{ color:#15803d; }
  .free-item-tag{
    display: inline-flex;
    margin-left: 6px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #dcfce7;
    color: #166534;
    font-size: 11px;
    font-weight: 800;
  }

  .summary-divider{ height: 1px; background: rgba(0,0,0,.06); margin: 14px 0; }
  .summary-row{ display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 14px; font-weight: 800; color: rgba(17,24,39,.8); }

  .summary-total{
    display:flex; justify-content:space-between;
    font-size: 16px; font-weight: 900; color:#111827; margin-top: 10px;
  }
  .summary-total span:last-child{ color:#16a34a; }

  .btn-checkout{
    width: 100%;
    padding: 14px;
    background: #16a34a;
    color:#fff;
    border: 0;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 900;
    cursor:pointer;
    margin-top: 14px;
    transition: transform .12s ease, box-shadow .2s ease, filter .2s ease;
  }
  .btn-checkout:hover{
    box-shadow: 0 18px 40px rgba(22,163,74,.22);
    filter: brightness(.99);
  }
  .btn-checkout:active{ transform: scale(.99); }
  .btn-checkout.is-loading{ opacity:.75; pointer-events:none; }

  .summary-safe{
    margin-top: 12px;
    font-size: 12px;
    font-weight: 800;
    color: rgba(17,24,39,.60);
    text-align:center;
  }

  /* Smooth collapse for address */
  #addressCard{
    transform-origin: top;
    transition: opacity .2s ease, transform .2s ease, height .2s ease, margin .2s ease;
  }
  #addressCard.is-hidden{
    opacity: 0;
    transform: translateY(-6px);
    height: 0;
    overflow: hidden;
    margin: 0;
    padding-top: 0;
    padding-bottom: 0;
    border: 0;
  }

  /* Toast top */
  .toast{
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-10px);
    background: #111827;
    color:#fff;
    padding: 12px 18px;
    border-radius: 14px;
    box-shadow: 0 18px 40px rgba(0,0,0,.25);
    opacity: 0;
    transition: .25s ease;
    z-index: 9999;
    font-weight: 900;
    font-size: 13px;
  }
  .toast.show{
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }

  /* helpers */
  .mb-0{ margin-bottom:0; }
  .mt-1{ margin-top:8px; }
  .mt-3{ margin-top:16px; }
</style>

<script>
  function formatRupiah(number){
    try {
      return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
    } catch(e){
      return 'Rp ' + (number || 0);
    }
  }

  // Toast
  let toastTimer = null;
  function showToast(msg){
    const el = document.getElementById('toast');
    if(!el) return;
    el.textContent = msg;
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2200);
  }

  // Copy handlers (no more alert)
  async function copyText(text){
    await navigator.clipboard.writeText(text);
  }

  function bindCopyButtons(){
    document.querySelectorAll('.btn-copy[data-copy]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const text = btn.getAttribute('data-copy');
        try{
          await copyText(text);
          btn.classList.add('copied');
          const old = btn.textContent;
          btn.textContent = '✅ Copied';
          showToast('Nomor berhasil disalin');
          setTimeout(() => {
            btn.textContent = old;
            btn.classList.remove('copied');
          }, 1400);
        }catch(e){
          showToast('Gagal menyalin. Silakan copy manual.');
        }
      });
    });
  }

  // Update active state for shipping options
  function updateShippingActiveState() {
    document.querySelectorAll('.ship-option').forEach(option => {
      const radio = option.querySelector('input[type="radio"]');
      option.classList.toggle('active', !!(radio && radio.checked));
    });
  }

  // Toggle address field + update summary amounts
  function initShippingToggle() {
    const radios = document.querySelectorAll('input[name="shipping_method"]');
    const addressCard = document.getElementById('addressCard');
    const addressInput = document.getElementById('addressInput');
    const phoneInput = document.getElementById('phoneInput');
    const transferAmountInput = document.getElementById('transferAmountInput');
    const shippingCostEl = document.getElementById('shippingCost');
    const totalAmountEl = document.getElementById('totalAmount');
    const subtotalEl = document.getElementById('subtotalAmount');
    const subtotal = Number(subtotalEl?.dataset?.subtotal || 0);

    function applyShippingState(method) {
      const isPickup = method === 'pickup';
      const shipping = isPickup ? 0 : 10000;
      const total = subtotal + shipping;

      const defaultPhone = phoneInput ? (phoneInput.dataset.defaultValue || '') : '';
      const defaultAddress = addressInput ? (addressInput.dataset.defaultValue || '') : '';

      if (addressCard) {
        addressCard.classList.toggle('is-hidden', isPickup);
      }

      if (phoneInput) {
        phoneInput.required = !isPickup;
        if (isPickup) phoneInput.value = '';
        else if (!phoneInput.value.trim()) phoneInput.value = defaultPhone;
      }

      if (addressInput) {
        addressInput.required = !isPickup;
        if (isPickup) addressInput.value = '';
        else if (!addressInput.value.trim()) addressInput.value = defaultAddress;
      }

      if (shippingCostEl) shippingCostEl.textContent = formatRupiah(shipping);
      if (totalAmountEl) totalAmountEl.textContent = formatRupiah(total);
      if (transferAmountInput) transferAmountInput.value = total;

      showToast(isPickup ? 'Pengiriman: Ambil di Toko' : 'Pengiriman: Dikirim');
    }

    updateShippingActiveState();
    radios.forEach(radio => {
      radio.addEventListener('change', function() {
        updateShippingActiveState();
        applyShippingState(this.value);
      });
    });

    const checked = document.querySelector('input[name="shipping_method"]:checked');
    if (checked) applyShippingState(checked.value);
  }

  // TANPA PREVIEW: hanya nama file + hapus
  function initProofPreview(){
    const input = document.getElementById('proofInput');
    const wrap = document.getElementById('proofFileInfo');
    const nameEl = document.getElementById('proofFileName');
    const removeBtn = document.getElementById('proofRemoveBtn');

    if(!input || !wrap || !nameEl || !removeBtn) return;

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if(!file){
        wrap.style.display = 'none';
        return;
      }

      nameEl.textContent = file.name;
      wrap.style.display = 'flex';
      showToast('File dipilih: ' + file.name);
    });

    removeBtn.addEventListener('click', () => {
      input.value = '';
      wrap.style.display = 'none';
      nameEl.textContent = '-';
      showToast('File dihapus');
    });
  }

  // Button loading state
  function initPayLoading(){
    const form = document.querySelector('form.checkout-grid');
    const btn = document.getElementById('payBtn');
    if(!form || !btn) return;

    form.addEventListener('submit', () => {
      btn.classList.add('is-loading');
      btn.textContent = 'Memproses...';
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    bindCopyButtons();
    initShippingToggle();
    initProofPreview();
    initPayLoading();
  });
</script>
@endsection
