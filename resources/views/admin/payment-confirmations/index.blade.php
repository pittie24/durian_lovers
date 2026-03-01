@extends('layouts.admin')

@section('content')
<div class="payment-confirmations-page">
    <div class="page-head">
        <h2>Pesanan</h2>
        <a href="{{ route('admin.payment-confirmations.manual.create') }}" class="btn btn-cash">
            Buat Pesanan Cash
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>ID Konfirmasi</th>
                        <th>Pelanggan</th>
                        <th>Bank</th>
                        <th>Nominal</th>
                        <th>Status Bayar</th>
                        <th>Status Pesanan</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $confirmation = $order->paymentConfirmation;
                            $payment = $order->payment;
                            $isCashOrder = strtoupper((string) ($payment?->payment_method ?? $order->payment_method)) === 'CASH';
                            $orderStatusLabel = match($order->status) {
                                'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
                                'SEDANG_DIPROSES' => 'Dikemas',
                                'SIAP_DIAMBIL_DIKIRIM' => 'Dikirim',
                                'SELESAI' => 'Selesai',
                                'DIBATALKAN' => 'Dibatalkan',
                                default => str_replace('_', ' ', $order->status),
                            };
                        @endphp
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $confirmation ? '#' . $confirmation->id : '-' }}</td>
                            <td>{{ $order->customer_display_name }}</td>
                            <td>{{ $isCashOrder ? 'Cash' : ($confirmation?->bank_name ?? ($order->payment_method ?? '-')) }}</td>
                            <td>Rp {{ number_format($confirmation?->transfer_amount ?? $order->total, 0, ',', '.') }}</td>
                            <td>
                                @if($isCashOrder)
                                    <span class="badge badge-cash">Cash</span>
                                @elseif(!$confirmation)
                                    <span class="badge badge-secondary">-</span>
                                @elseif($confirmation->status === 'PENDING')
                                    <span class="badge badge-secondary">-</span>
                                @elseif($confirmation->status === 'APPROVED')
                                    <span class="badge badge-success">Diterima</span>
                                @else
                                    <span class="badge badge-danger">Ditolak</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-order status-{{ strtolower(str_replace('_', '-', $order->status)) }}">
                                    {{ $orderStatusLabel }}
                                </span>
                            </td>
                            <td>{{ ($confirmation?->created_at ?? $order->created_at)->format('d M Y') }}</td>
                            <td>
                                @if($confirmation)
                                    <a href="{{ route('admin.payment-confirmations.show', $confirmation) }}" class="btn btn-sm btn-primary">
                                        Detail
                                    </a>
                                @elseif($isCashOrder)
                                    <a href="{{ route('admin.payment-confirmations.order.show', $order) }}" class="btn btn-sm btn-primary">
                                        Detail
                                    </a>
                                @else
                                    <span class="text-muted">Menunggu upload pelanggan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada pesanan untuk ditampilkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($orders->hasPages())
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.payment-confirmations-page h2 {
    margin: 0;
    color: #2c3e50;
}

.page-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #2c3e50;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.badge-secondary {
    background: #e2e3e5;
    color: #41464b;
}

.badge-cash {
    background: #fef3c7;
    color: #92400e;
}

.badge-order {
    font-weight: 700;
}

.badge-order.status-menunggu-pembayaran {
    background: #fff7ed;
    color: #c2410c;
}

.badge-order.status-sedang-diproses {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-order.status-siap-diambil-dikirim {
    background: #ccfbf1;
    color: #0f766e;
}

.badge-order.status-selesai {
    background: #dcfce7;
    color: #166534;
}

.badge-order.status-dibatalkan {
    background: #fee2e2;
    color: #991b1b;
}

.btn {
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    font-size: 13px;
    cursor: pointer;
    border: none;
}

.btn-sm {
    padding: 4px 10px;
    font-size: 12px;
}

.btn-primary {
    background: #27ae60;
    color: white;
}

.btn-primary:hover {
    background: #219a52;
}

.btn-cash {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.alert {
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.text-muted {
    color: #6c757d;
    font-size: 12px;
}
</style>
@endsection
