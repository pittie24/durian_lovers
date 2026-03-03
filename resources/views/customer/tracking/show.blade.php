@extends('layouts.app')

@section('content')
<h2>Status Pesanan</h2>

@php
    $statusLabels = [
        'PESANAN_DITERIMA' => 'Pesanan Diterima',
        'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
        'SEDANG_DIPROSES' => 'Pesanan Dikemas',
        'SIAP_DIAMBIL_DIKIRIM' => 'Siap Dikirim/Siap Diambil',
        'SELESAI' => 'Selesai',
        'DIBATALKAN' => 'Dibatalkan',
    ];
@endphp

@if($order->status !== 'DIBATALKAN')
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
@else
    <div class="status-badge danger" style="margin-bottom: 16px;">Pesanan dibatalkan, tahapan proses tidak ditampilkan.</div>
@endif

<div class="card">
    <div class="card-body">
        <h4>Status pesanan: {{ $statusLabels[$order->status] ?? str_replace('_', ' ', $order->status) }}</h4>
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

        <div class="payment-proof-section mt-4">
            <h5>Status Pembayaran</h5>

            @if($order->paymentConfirmation?->isApproved())
                <div class="status-badge success">Diverifikasi</div>
            @elseif($order->paymentConfirmation?->isPending())
                <div class="status-badge warning">Menunggu Verifikasi</div>
            @elseif($order->paymentConfirmation?->isRejected())
                <div class="status-badge danger">Ditolak: {{ $order->paymentConfirmation->notes }}</div>
            @else
                <div class="status-badge warning">Menunggu Konfirmasi</div>
            @endif

            <div class="proof-detail">
                <div class="detail-row">
                    <span class="label">Informasi:</span>
                    <span class="value">Bukti transfer hanya ditampilkan di halaman pesanan admin.</span>
                </div>
            </div>
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

<style>
h2 {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 20px;
}

.card {
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,.08);
}

.payment-proof-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.payment-proof-section h5 {
    margin-bottom: 16px;
    color: #2c3e50;
}

.status-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 16px;
}

.status-badge.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-badge.warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-badge.danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.proof-detail {
    background: white;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    font-weight: 600;
    color: #666;
}

.detail-row .value {
    color: #2c3e50;
    text-align: right;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #27ae60;
    color: white;
}

.btn-primary:hover {
    background: #219a52;
}

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-sm {
    padding: 8px 16px;
    font-size: 13px;
}

.mt-4 {
    margin-top: 16px;
}
</style>
