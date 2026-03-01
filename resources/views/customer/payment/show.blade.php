@extends('layouts.app')

@section('content')
<div class="payment-overlay">
    <div class="payment-card">
        <h2>Proses Pembayaran</h2>
        <p>Silakan upload bukti transfer Anda.</p>
        <div class="summary-row">
            <span>Total Pembayaran</span>
            <span class="text-primary font-weight-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span>Metode pembayaran</span>
            <span>{{ $order->payment_method }}</span>
        </div>
        <div class="summary-row">
            <span>Status</span>
            <span>{{ $order->status }}</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="payment-actions">
            <a href="/tracking/{{ $order->id }}" class="btn outline">Kembali</a>
            <a href="{{ route('pembayaran.confirmation.show', $order->id) }}" class="btn primary">
                📤 Upload Bukti Pembayaran
            </a>
        </div>
    </div>
</div>

<style>
.text-primary { color: #27ae60; }
.font-weight-bold { font-weight: 700; }
</style>
@endsection
