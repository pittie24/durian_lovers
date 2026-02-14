@extends('layouts.app')

@section('content')
<h2>Checkout</h2>

<div class="checkout-layout">
    <form class="checkout-form" method="POST" action="/checkout">
        @csrf
        <div class="card">
            <div class="card-body">
                <h3>Metode Pengiriman</h3>
                <label><input type="radio" name="shipping_method" value="Dikirim" checked> Dikirim</label>
                <label><input type="radio" name="shipping_method" value="Ambil di Toko"> Ambil di Toko</label>

                <h3>Alamat Pengiriman</h3>
                <label>Nomor Telepon</label>
                <input type="text" name="phone" value="{{ auth()->user()->phone }}" required>
                <label>Alamat Lengkap</label>
                <textarea name="address" rows="3" required>{{ auth()->user()->address }}</textarea>

                <h3>Metode Pembayaran</h3>
                <label><input type="radio" name="payment_method" value="Transfer Bank" checked> Transfer Bank</label>
                <label><input type="radio" name="payment_method" value="E-Wallet"> E-Wallet</label>
                <label><input type="radio" name="payment_method" value="Virtual Account"> Virtual Account</label>
            </div>
        </div>
        <button type="submit" class="btn primary full">Bayar Sekarang</button>
    </form>

    <div class="summary-card">
        <h3>Ringkasan Pesanan</h3>
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
    </div>
</div>
@endsection
