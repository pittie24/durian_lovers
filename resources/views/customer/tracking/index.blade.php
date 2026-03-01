@extends('layouts.app')

@section('content')
<div class="tracking-page">
    <h2>Status Pesanan</h2>

    @php
        $labels = [
            1 => 'Pesanan Diterima',
            2 => 'Sedang Diproses',
            3 => 'Siap Diambil/Dikirim',
            4 => 'Selesai',
        ];
    @endphp

    @forelse($orders as $order)
        <div class="tracking-detail-card">
            <div class="tracking-detail-header">
                <div>
                    <strong>Order #{{ $order->order_number }}</strong>
                    <div class="tracking-date">{{ $order->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="tracking-status status-{{ strtolower(str_replace('_', '-', $order->status)) }}">
                    {{ str_replace('_', ' ', $order->status) }}
                </div>
            </div>

            @if($order->status !== 'DIBATALKAN')
                <div class="timeline">
                    @foreach ($labels as $step => $label)
                        <div class="timeline-step {{ $order->currentStep >= $step ? 'active' : '' }}">
                            <span class="dot"></span>
                            <span>{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="cancelled-note">
                    Pesanan ini dibatalkan, sehingga tahapan proses tidak ditampilkan.
                </div>
            @endif

            <div class="summary-grid">
                <div class="summary-box">
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
                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="summary-actions">
                    @if(!$order->paymentConfirmation)
                        <a href="{{ route('pembayaran.confirmation.show', $order->id) }}" class="btn btn-outline">
                            Upload Bukti Pembayaran
                        </a>
                    @endif
                </div>
            </div>

            @if($order->paymentConfirmation)
                <div class="payment-proof-section">
                    <h4>Bukti Pembayaran</h4>

                    @if($order->paymentConfirmation->isApproved())
                        <div class="status-badge success">Diverifikasi</div>
                    @elseif($order->paymentConfirmation->isPending())
                        <div class="status-badge warning">Menunggu Verifikasi</div>
                    @elseif($order->paymentConfirmation->isRejected())
                        <div class="status-badge danger">Ditolak: {{ $order->paymentConfirmation->notes }}</div>
                    @endif

                    <div class="proof-detail">
                        <div class="detail-row">
                            <span>Metode</span>
                            <span>{{ $order->paymentConfirmation->bank_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Nama Pengirim</span>
                            <span>{{ $order->paymentConfirmation->account_name }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Nominal</span>
                            <span>Rp {{ number_format($order->paymentConfirmation->transfer_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="detail-row">
                            <span>Tanggal Upload</span>
                            <span>{{ $order->paymentConfirmation->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="ordered-items">
                <h4>Produk yang dipesan</h4>
                <div class="ordered-grid">
                    @foreach ($order->items as $item)
                        <div class="ordered-item-card">
                            <div class="ordered-item-name">{{ $item->product->name }}</div>
                            <div>Qty: {{ $item->quantity }}</div>
                            <div>Rp {{ number_format($item->total, 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="tracking-empty">
            <p>Belum ada pesanan untuk ditampilkan.</p>
            <a href="/produk" class="btn btn-primary">Belanja Sekarang</a>
        </div>
    @endforelse
</div>
@endsection

<style>
.tracking-page { max-width: 960px; margin: 0 auto; }
.tracking-page h2 { font-size: 26px; font-weight: 700; color: #2c3e50; margin-bottom: 24px; }

.tracking-detail-card {
    background: white;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.tracking-detail-header {
    display: flex;
    justify-content: space-between;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 18px;
}

.tracking-date { color: #666; font-size: 13px; margin-top: 4px; }

.tracking-status { padding: 8px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.tracking-status.status-menunggu-pembayaran { background: #fff3cd; color: #856404; }
.tracking-status.status-sedang-diproses, .tracking-status.status-pesanan-diterima { background: #d1ecf1; color: #0c5460; }
.tracking-status.status-siap-diambil-dikirim { background: #d4edda; color: #155724; }
.tracking-status.status-selesai { background: #27ae60; color: white; }
.tracking-status.status-dibatalkan { background: #dc3545; color: white; }

.timeline {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    margin-bottom: 18px;
}

.cancelled-note {
    margin-bottom: 18px;
    padding: 12px 14px;
    border-radius: 10px;
    background: #fff5f5;
    color: #b02a37;
    font-size: 13px;
    font-weight: 600;
}

.timeline-step {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 13px;
}

.timeline-step.active {
    background: #e8f8ee;
    color: #1e7e46;
    font-weight: 700;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
}

.summary-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 16px;
    align-items: start;
    margin-bottom: 18px;
}

.summary-box {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 16px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.summary-row:last-child { border-bottom: none; }

.summary-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    text-align: center;
}

.btn-primary { background: #27ae60; color: white; }
.btn-outline { border: 1px solid #27ae60; color: #27ae60; background: white; }
.btn-warning { background: #ffc107; color: #333; }

.payment-proof-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 18px;
}

.payment-proof-section h4,
.ordered-items h4 {
    margin-bottom: 14px;
    color: #2c3e50;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 14px;
}

.status-badge.success { background: #d4edda; color: #155724; }
.status-badge.warning { background: #fff3cd; color: #856404; }
.status-badge.danger { background: #f8d7da; color: #721c24; }

.proof-detail {
    background: white;
    border-radius: 10px;
    padding: 14px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.detail-row:last-child { border-bottom: none; }

.ordered-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
}

.ordered-item-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 14px;
}

.ordered-item-name {
    font-weight: 700;
    margin-bottom: 6px;
    color: #2c3e50;
}

.tracking-empty {
    background: white;
    border-radius: 14px;
    padding: 40px 24px;
    text-align: center;
}

@media (max-width: 768px) {
    .tracking-detail-header,
    .summary-grid {
        grid-template-columns: 1fr;
        display: grid;
    }

    .timeline {
        grid-template-columns: 1fr;
    }
}
</style>
