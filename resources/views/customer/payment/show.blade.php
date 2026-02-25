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
        
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="payment-actions">
            <a href="/tracking/{{ $order->id }}" class="btn outline">Kembali</a>
            <button type="button" class="btn primary" id="pay-button">Bayar Sekarang</button>
        </div>
    </div>
</div>

@if($order->payment && $order->payment->snap_token)
    <!-- Midtrans Snap JS -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    
    <script>
        document.getElementById('pay-button').addEventListener('click', function() {
            snap.pay('{{ $order->payment->snap_token }}', {
                onSuccess: function(result) {
                    // Pembayaran berhasil
                    alert('Pembayaran berhasil!');
                    window.location.href = '/tracking/{{ $order->id }}';
                },
                onPending: function(result) {
                    // Pembayaran pending
                    alert('Menunggu pembayaran...');
                    window.location.href = '/tracking/{{ $order->id }}';
                },
                onError: function(result) {
                    // Pembayaran error
                    alert('Pembayaran gagal. Silakan coba lagi.');
                    window.location.href = '/tracking/{{ $order->id }}';
                },
                onClose: function() {
                    // User menutup popup
                    alert('Anda menutup popup pembayaran.');
                }
            });
        });
    </script>
@endif
@endsection
