@extends('layouts.app')

@section('content')
<h2>Status Pesanan</h2>

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
        
        {{-- Tombol Download Invoice (hanya muncul jika sudah lunas) --}}
        @php
          $paymentStatus = strtolower($order->payment?->status ?? '');
          $hasConfirmation = $order->paymentConfirmation && $order->paymentConfirmation->isApproved();
          $isPaid = in_array($paymentStatus, ['paid', 'settled', 'capture', 'settlement']) || $hasConfirmation;
        @endphp

        @if($isPaid)
            <div style="margin-top: 16px;">
                <a href="{{ route('customer.invoice.download', $order->id) }}" class="btn-invoice-download">
                    📄 Download Invoice
                </a>
            </div>
        @endif

        {{-- Tampilkan bukti pembayaran jika ada --}}
        @if($order->paymentConfirmation)
            <div class="payment-proof-section mt-4">
                <h5>📄 Bukti Pembayaran</h5>
                
                @if($order->paymentConfirmation->isApproved())
                    <div class="status-badge success">✅ Diverifikasi</div>
                @elseif($order->paymentConfirmation->isPending())
                    <div class="status-badge warning">⏳ Menunggu Verifikasi</div>
                @elseif($order->paymentConfirmation->isRejected())
                    <div class="status-badge danger">❌ Ditolak: {{ $order->paymentConfirmation->notes }}</div>
                @endif

                <div class="proof-detail">
                    <div class="detail-row">
                        <span class="label">Metode:</span>
                        <span class="value">{{ $order->paymentConfirmation->bank_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Nama Pengirim:</span>
                        <span class="value">{{ $order->paymentConfirmation->account_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Nominal:</span>
                        <span class="value">Rp {{ number_format($order->paymentConfirmation->transfer_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Tanggal Upload:</span>
                        <span class="value">{{ $order->paymentConfirmation->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                @if($order->paymentConfirmation->proof_image)
                    <div class="proof-image-container">
                        <img src="{{ Storage::url($order->paymentConfirmation->proof_image) }}" alt="Bukti Transfer" class="proof-image">
                    </div>
                @endif

                @if($order->paymentConfirmation->isRejected())
                    <div class="mt-3">
                        <a href="{{ route('pembayaran.confirmation.resubmit', $order->id) }}" class="btn btn-warning btn-sm">
                            🔄 Ajukan Ulang
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="mt-4">
                <a href="{{ route('pembayaran.confirmation.show', $order->id) }}" class="btn btn-primary">
                    📤 Upload Bukti Pembayaran
                </a>
            </div>
        @endif
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

.proof-image-container {
    text-align: center;
    margin-top: 16px;
}

.proof-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
