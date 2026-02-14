@extends('layouts.app')

@section('content')
<h2>Tracking Pesanan</h2>

<div class="timeline">
    @php
        $labels = [
            1 => 'Pesanan Diterima',
            2 => 'Sedang Diproses',
            3 => 'Siap Diambil/Dikirim',
            4 => 'Selesai',
        ];
    @endphp
    @foreach ($labels as $step => $label)
        <div class="timeline-step {{ $currentStep >= $step ? 'active' : '' }}">
            <span class="dot"></span>
            <span>{{ $label }}</span>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body">
        <h4>Status pesanan: {{ str_replace('_', ' ', $order->status) }}</h4>
        <div class="summary-row">
            <span>Metode pengiriman</span>
            <span>{{ $order->shipping_method }}</span>
        </div>
        <div class="summary-row">
            <span>Metode pembayaran</span>
            <span>{{ $order->payment_method }}</span>
        </div>
        <div class="summary-row">
            <span>Total</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<section class="section-heading">
    <h3>Produk yang dipesan</h3>
</section>

<div class="grid cards">
    @foreach ($order->items as $item)
        <div class="card product-card">
            <div class="card-body">
                <h4>{{ $item->product->name }}</h4>
                <div>Qty: {{ $item->quantity }}</div>
                <div>Rp {{ number_format($item->total, 0, ',', '.') }}</div>
            </div>
        </div>
    @endforeach
</div>
@endsection
