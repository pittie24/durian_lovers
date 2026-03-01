@extends('layouts.app')

@section('content')
@php
  $shippingMethod = old('shipping_method', 'delivery');
  $cartItems = $cart ?? [];
  $subtotal = $summary['subtotal'] ?? 0;
  $shippingCost = ($shippingMethod === 'delivery') ? 10000 : 0;
  $total = $subtotal + $shippingCost;
  $defaultPhone = old('phone', auth()->user()->phone ?? '');
  $defaultAddress = old('address', auth()->user()->address ?? '');
@endphp

<div class="checkout-page">
  <h1 class="checkout-title">Pembayaran</h1>

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
          </label>

          <label class="ship-option {{ $shippingMethod==='pickup' ? 'active' : '' }}">
            <input type="radio" name="shipping_method" value="pickup" {{ $shippingMethod==='pickup' ? 'checked' : '' }}>
            <div class="ship-ico">🏪</div>
            <div class="ship-text">
              <div class="ship-name">Ambil di Toko</div>
              <div class="ship-sub">Gratis</div>
            </div>
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
          <button type="button" class="btn-copy" onclick="copyToClipboard('341901058068539')">📋 Copy</button>
        </div>

        <div class="bank-item">
          <div class="bank-icon">📱</div>
          <div class="bank-info">
            <div class="bank-name">DANA</div>
            <div class="bank-number">081352953905</div>
            <div class="bank-holder">a.n Durian Lovers</div>
          </div>
          <button type="button" class="btn-copy" onclick="copyToClipboard('081352953905')">📋 Copy</button>
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
          <input class="ck-input" type="number" name="transfer_amount" value="{{ old('transfer_amount', $total) }}" required>
        </div>
        <div class="ck-field">
          <label class="ck-label">Upload Bukti Transfer</label>
          <input class="ck-input" type="file" name="proof_image" accept="image/*" required>
          <small class="ck-hint">Format: JPG, PNG. Maksimal 2MB</small>
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
        </div>

        <div class="summary-divider"></div>

        <div class="summary-row">
          <span>Subtotal</span>
          <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
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

        <button type="submit" class="btn-checkout">
          ✅ Buat Pesanan & Bayar
        </button>
      </div>
    </div>
  </form>
</div>

<style>
.checkout-page { max-width: 1100px; margin: 0 auto; }
.checkout-title { font-size: 28px; font-weight: 700; color: #2c3e50; margin-bottom: 24px; }
.checkout-grid { display: grid; grid-template-columns: 1fr 380px; gap: 24px; }
@media (max-width: 768px) { .checkout-grid { grid-template-columns: 1fr; } }

.ck-card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.ck-card-title { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 16px; }
.payment-info-text { color: #666; font-size: 14px; margin-bottom: 16px; }

.ship-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ship-option { display: flex; align-items: center; gap: 12px; padding: 16px; border: 2px solid #e9ecef; border-radius: 8px; cursor: pointer; transition: all 0.2s; position: relative; }
.ship-option.active { border-color: #27ae60; background: #f0fff4; }
.ship-option input { position: absolute; opacity: 0; pointer-events: none; }
.ship-option label { cursor: pointer; width: 100%; }
.ship-ico { font-size: 28px; }
.ship-name { font-weight: 600; color: #2c3e50; }
.ship-sub { font-size: 12px; color: #666; }

.ck-field { margin-bottom: 16px; }
.ck-label { display: block; font-weight: 600; color: #2c3e50; margin-bottom: 6px; font-size: 14px; }
.ck-input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
.ck-textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; font-family: inherit; resize: vertical; }
.ck-hint { display: block; font-size: 12px; color: #666; margin-top: 4px; }

.bank-item { display: flex; align-items: center; gap: 16px; padding: 16px; background: #f8f9fa; border-radius: 8px; margin-bottom: 12px; }
.bank-icon { font-size: 32px; }
.bank-info { flex: 1; }
.bank-name { font-weight: 700; color: #2c3e50; }
.bank-number { font-size: 16px; font-weight: 600; color: #27ae60; font-family: monospace; }
.bank-holder { font-size: 13px; color: #666; }
.btn-copy { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-copy:hover { background: #219a52; }

.alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 16px; border-radius: 8px; font-size: 13px; }
.alert-info ul { margin: 8px 0 0 20px; padding: 0; }
.alert-info li { margin-bottom: 4px; }
.mb-0 { margin-bottom: 0; }
.mt-1 { margin-top: 8px; }
.mt-3 { margin-top: 16px; }

.order-summary { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: sticky; top: 20px; }
.summary-title { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 16px; }
.summary-item { display: flex; justify-content: space-between; margin-bottom: 12px; }
.item-name { font-weight: 600; color: #2c3e50; }
.item-qty { font-size: 13px; color: #666; }
.item-total { font-weight: 600; color: #27ae60; }
.summary-divider { height: 1px; background: #e9ecef; margin: 16px 0; }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
.summary-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 700; color: #2c3e50; margin-top: 16px; }
.summary-total span:last-child { color: #27ae60; }

.btn-checkout { width: 100%; padding: 16px; background: #27ae60; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 20px; }
.btn-checkout:hover { background: #219a52; }
</style>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Nomor berhasil dicopy: ' + text);
    }, function(err) {
        console.error('Gagal copy: ', err);
    });
}

// Update active state for shipping options
function updateShippingActiveState() {
    const options = document.querySelectorAll('.ship-option');
    options.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio.checked) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
}

// Toggle address field based on shipping method
function initShippingToggle() {
    const radios = document.querySelectorAll('input[name="shipping_method"]');
    const addressCard = document.getElementById('addressCard');
    const addressInput = document.getElementById('addressInput');
    const phoneInput = document.getElementById('phoneInput');
    const transferAmountInput = document.querySelector('input[name="transfer_amount"]');
    const shippingCostEl = document.getElementById('shippingCost');
    const totalAmountEl = document.getElementById('totalAmount');
    const subtotal = {{ $subtotal }};

    function applyShippingState(method) {
        const isPickup = method === 'pickup';
        const total = isPickup ? subtotal : subtotal + 10000;
        const defaultPhone = phoneInput ? (phoneInput.dataset.defaultValue || '') : '';
        const defaultAddress = addressInput ? (addressInput.dataset.defaultValue || '') : '';

        if (addressCard) {
            addressCard.style.display = isPickup ? 'none' : 'block';
        }

        if (phoneInput) {
            phoneInput.required = !isPickup;
            if (isPickup) {
                phoneInput.value = '';
            } else if (!phoneInput.value.trim()) {
                phoneInput.value = defaultPhone;
            }
        }

        if (addressInput) {
            addressInput.required = !isPickup;
            if (isPickup) {
                addressInput.value = '';
            } else if (!addressInput.value.trim()) {
                addressInput.value = defaultAddress;
            }
        }

        if (shippingCostEl) {
            shippingCostEl.textContent = isPickup ? 'Rp 0' : 'Rp 10.000';
        }

        if (totalAmountEl) {
            totalAmountEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        if (transferAmountInput) {
            transferAmountInput.value = total;
        }
    }
    
    // Initial active state
    updateShippingActiveState();
    
    radios.forEach(radio => {
        // Update on change
        radio.addEventListener('change', function() {
            updateShippingActiveState();
            applyShippingState(this.value);
        });
        
        // Also update on click (for faster response)
        radio.addEventListener('click', function() {
            updateShippingActiveState();
        });
    });
    
    // Trigger initial state for pickup
    const checked = document.querySelector('input[name="shipping_method"]:checked');
    if (checked) {
        applyShippingState(checked.value);
    }
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShippingToggle);
} else {
    initShippingToggle();
}
</script>
@endsection
