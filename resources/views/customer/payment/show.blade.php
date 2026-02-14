@extends('layouts.app')

@section('content')
<div class="payment-overlay">
    <div class="payment-card">
        <h2>Proses Pembayaran</h2>
        <p>Anda akan diarahkan ke halaman pembayaran Midtrans.</p>
        <div class="summary-row">
            <span>Total Pembayaran</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Metode pembayaran</span>
            <span>{{ $order->payment_method }}</span>
        </div>
        <div class="payment-actions">
            <a href="/checkout" class="btn outline">Batal</a>
            <form method="POST" action="/pembayaran/{{ $order->id }}/lanjut">
                @csrf
                <button type="submit" class="btn primary">Lanjutkan</button>
            </form>
        </div>
    </div>
</div>
@endsection
