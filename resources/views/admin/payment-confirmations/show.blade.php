@extends('layouts.admin')

@section('content')
@php
    $payment = $order->payment;
    $isCashOrder = strtoupper((string) ($payment?->payment_method ?? $order->payment_method)) === 'CASH';
    $orderStatusLabels = [
        'MENUNGGU_PEMBAYARAN' => 'Menunggu Pembayaran',
        'SEDANG_DIPROSES' => 'Pesanan Dikemas',
        'SIAP_DIAMBIL_DIKIRIM' => 'Pesanan Dikirim',
        'SELESAI' => 'Selesai',
        'DIBATALKAN' => 'Dibatalkan',
    ];
    $currentOrderLabel = $orderStatusLabels[$order->status] ?? str_replace('_', ' ', $order->status);
    $isLocked = in_array($order->status, ['SELESAI', 'DIBATALKAN'], true);
    $canManageStatus = !$isLocked && (
        $isCashOrder
        || ($payment?->status === 'PAID')
        || ($confirmation && $confirmation->isApproved())
    );
    $statusRoute = $confirmation
        ? route('admin.payment-confirmations.order-status', $confirmation)
        : route('admin.payment-confirmations.order.status', $order);
@endphp

<div class="order-detail-page">
    <div class="page-head">
        <div>
            <h2>Detail Pesanan</h2>
            @if(!$isCashOrder)
                <p>Kelola verifikasi pembayaran dan progres pengiriman dalam satu halaman.</p>
            @endif
        </div>
    </div>

    <div class="grid two-columns">
        <div class="card hero-card">
            <div class="card-body">
                <div class="eyebrow">Ringkasan</div>
                <div class="order-number">{{ $order->order_number }}</div>

                <div class="badge-row">
                    <span class="status-pill status-label neutral">Status Pesanan</span>
                    <span class="status-pill order-status status-{{ strtolower(str_replace('_', '-', $order->status)) }}">
                        {{ $currentOrderLabel }}
                    </span>

                    <span class="status-pill status-label neutral">Status Bayar</span>
                    @if($isCashOrder)
                        <span class="status-pill payment-status cash">Cash</span>
                    @elseif($confirmation && $confirmation->isApproved())
                        <span class="status-pill payment-status approved">Diterima</span>
                    @elseif($confirmation && $confirmation->isRejected())
                        <span class="status-pill payment-status rejected">Ditolak</span>
                    @else
                        <span class="status-pill payment-status neutral">-</span>
                    @endif
                </div>

                <div class="mini-grid">
                    <div class="mini-item">
                        <span class="mini-label">Pelanggan</span>
                        <strong>{{ $order->customer_display_name }}</strong>
                        <small>{{ $order->customer_display_email }}</small>
                        <small>{{ $order->customer_display_phone }}</small>
                    </div>
                    <div class="mini-item">
                        <span class="mini-label">Total Pembayaran</span>
                        <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                        <small>{{ $order->shipping_method === 'pickup' ? 'Ambil di Toko' : 'Dikirim' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4>Info Pembayaran</h4>
                <table class="table-detail">
                    <tr>
                        <td>Jenis Transaksi</td>
                        <td>: {{ $isCashOrder ? 'Pesanan Cash Manual' : 'Konfirmasi Transfer' }}</td>
                    </tr>
                    @if($confirmation)
                        <tr>
                            <td>ID Konfirmasi</td>
                            <td>: #{{ $confirmation->id }}</td>
                        </tr>
                        <tr>
                            <td>Bank Pengirim</td>
                            <td>: {{ $confirmation->bank_name }}</td>
                        </tr>
                        <tr>
                            <td>Nama Pengirim</td>
                            <td>: {{ $confirmation->account_name }}</td>
                        </tr>
                        <tr>
                            <td>Nominal Transfer</td>
                            <td>: <strong class="text-accent">Rp {{ number_format($confirmation->transfer_amount, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Tanggal Upload</td>
                            <td>: {{ $confirmation->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>Dicatat Oleh</td>
                            <td>: Admin</td>
                        </tr>
                        <tr>
                            <td>Tanggal Input</td>
                            <td>: {{ ($payment?->created_at ?? $order->created_at)->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Nominal Bayar</td>
                            <td>: <strong class="text-accent">Rp {{ number_format($order->total, 0, ',', '.') }}</strong></td>
                        </tr>
                    @endif
                    <tr>
                        <td>Metode Bayar</td>
                        <td>: {{ $payment?->payment_method ?? $order->payment_method }}</td>
                    </tr>
                    <tr>
                        <td>Status Payment</td>
                        <td>: {{ $payment?->status ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Waktu Pembayaran</td>
                        <td>: {{ ($payment?->paid_at ?? $payment?->created_at)?->format('d M Y, H:i') ?? '-' }}</td>
                    </tr>
                </table>

                @if($confirmation && $confirmation->isApproved())
                    <div class="alert alert-success mt-3">
                        <strong>Disetujui oleh:</strong> {{ $confirmation->verifiedBy?->name ?? 'N/A' }}<br>
                        <strong>Pada:</strong> {{ $confirmation->verified_at?->format('d M Y, H:i') ?? '-' }}
                    </div>
                @elseif($confirmation && $confirmation->isRejected())
                    <div class="alert alert-danger mt-3">
                        <strong>Ditolak oleh:</strong> {{ $confirmation->verifiedBy?->name ?? 'N/A' }}<br>
                        <strong>Pada:</strong> {{ $confirmation->verified_at?->format('d M Y, H:i') ?? '-' }}<br>
                        <strong>Alasan:</strong> {{ $confirmation->notes }}
                    </div>
                @elseif($isCashOrder)
                    <div class="alert alert-cash mt-3">
                        Pembayaran cash langsung ditandai lunas saat pesanan dibuat.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($confirmation && $confirmation->proof_image)
        <div class="card mt-4">
            <div class="card-body">
                <div class="section-head">
                    <h4>Bukti Transfer</h4>
                    <span class="section-note">Preview diperkecil agar lebih nyaman dilihat.</span>
                </div>
                <div class="proof-image-container">
                    <img src="{{ route('admin.payment-confirmations.proof-image', $confirmation) }}" alt="Bukti Transfer" class="proof-image">
                </div>
            </div>
        </div>
    @endif

    @if($confirmation && $confirmation->isPending())
        <div class="card mt-4">
            <div class="card-body">
                <h4>Verifikasi Pembayaran</h4>

                <div class="action-buttons">
                    <form action="{{ route('admin.payment-confirmations.approve', $confirmation) }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyetujui konfirmasi pembayaran ini?')">
                            Setujui Pembayaran
                        </button>
                    </form>

                    <form action="{{ route('admin.payment-confirmations.reject', $confirmation) }}" method="POST" class="reject-form">
                        @csrf
                        <div class="form-group">
                            <label for="reject-notes">Alasan Penolakan</label>
                            <textarea name="notes" id="reject-notes" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak konfirmasi pembayaran ini?')">
                            Tolak Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if(!$isCashOrder)
        <div class="card mt-4">
            <div class="card-body">
                <div class="section-head">
                    <h4>Status Pesanan</h4>
                    <span class="section-note">
                        @if($isLocked)
                            Status sudah terkunci.
                        @elseif(!$canManageStatus)
                            Selesaikan pembayaran terlebih dahulu sebelum memproses pesanan.
                        @else
                            Pilih progres pesanan berikutnya.
                        @endif
                    </span>
                </div>

                <div class="status-actions">
                    <form action="{{ $statusRoute }}" method="POST" class="status-form">
                        @csrf
                        <input type="hidden" name="status" value="SEDANG_DIPROSES">
                        <button type="submit" class="status-btn pack {{ $order->status === 'SEDANG_DIPROSES' ? 'is-active' : '' }}" {{ !$canManageStatus ? 'disabled' : '' }}>
                            <span class="status-btn-title">Pesanan Dikemas</span>
                            <span class="status-btn-sub">Siapkan dan proses pesanan</span>
                        </button>
                    </form>

                    <form action="{{ $statusRoute }}" method="POST" class="status-form">
                        @csrf
                        <input type="hidden" name="status" value="SIAP_DIAMBIL_DIKIRIM">
                        <button type="submit" class="status-btn ship {{ $order->status === 'SIAP_DIAMBIL_DIKIRIM' ? 'is-active' : '' }}" {{ !$canManageStatus ? 'disabled' : '' }}>
                            <span class="status-btn-title">Pesanan Dikirim</span>
                            <span class="status-btn-sub">Tandai pesanan siap kirim</span>
                        </button>
                    </form>

                    <form action="{{ $statusRoute }}" method="POST" class="status-form">
                        @csrf
                        <input type="hidden" name="status" value="SELESAI">
                        <button type="submit" class="status-btn done {{ $order->status === 'SELESAI' ? 'is-active' : '' }}" {{ !$canManageStatus ? 'disabled' : '' }}>
                            <span class="status-btn-title">Selesai</span>
                            <span class="status-btn-sub">Tutup proses pesanan</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card mt-4">
        <div class="card-body">
            <h4>Produk yang Dipesan</h4>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.order-detail-page h2 {
    margin: 0;
    color: #1f2937;
}

.page-head {
    margin-bottom: 24px;
}

.page-head p {
    margin: 6px 0 0;
    color: #6b7280;
}

.grid.two-columns {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 20px;
}

.hero-card {
    background: linear-gradient(135deg, #fff7ed 0%, #fefce8 100%);
    border: 1px solid #fde7c7;
}

.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
}

.card-body {
    padding: 22px;
}

.eyebrow {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9a3412;
    font-weight: 700;
}

.order-number {
    margin-top: 8px;
    font-size: 28px;
    font-weight: 800;
    color: #111827;
}

.badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 800;
}

.order-status.status-menunggu-pembayaran { background: #fef3c7; color: #92400e; }
.order-status.status-sedang-diproses { background: #dbeafe; color: #1d4ed8; }
.order-status.status-siap-diambil-dikirim { background: #d1fae5; color: #047857; }
.order-status.status-selesai { background: #dcfce7; color: #166534; }
.order-status.status-dibatalkan { background: #fee2e2; color: #991b1b; }

.payment-status.approved { background: #dcfce7; color: #166534; }
.payment-status.rejected { background: #fee2e2; color: #991b1b; }
.payment-status.neutral { background: #f3f4f6; color: #4b5563; }
.payment-status.cash { background: #fef3c7; color: #92400e; }
.status-label.neutral { background: #f3f4f6; color: #4b5563; }

.mini-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-top: 18px;
}

.mini-item {
    background: rgba(255, 255, 255, 0.84);
    border: 1px solid rgba(255, 255, 255, 0.92);
    border-radius: 14px;
    padding: 14px;
}

.mini-item strong,
.mini-item small {
    display: block;
}

.mini-item small {
    margin-top: 4px;
    color: #6b7280;
}

.mini-label {
    display: block;
    margin-bottom: 6px;
    color: #9ca3af;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.table-detail {
    width: 100%;
}

.table-detail td {
    padding: 8px 0;
    vertical-align: top;
}

.table-detail td:first-child {
    font-weight: 600;
    width: 160px;
    color: #6b7280;
}

.text-accent {
    color: #b45309;
}

.section-head {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: center;
    margin-bottom: 14px;
}

.section-note {
    color: #6b7280;
    font-size: 12px;
}

.proof-image-container {
    text-align: center;
    padding: 8px 0 4px;
}

.proof-image {
    width: 100%;
    max-width: 320px;
    max-height: 420px;
    object-fit: contain;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    background: #fff;
}

.action-buttons {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 16px;
    align-items: start;
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 700;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-family: inherit;
}

.status-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.status-form {
    margin: 0;
}

.status-btn {
    width: 100%;
    padding: 16px;
    border: 0;
    border-radius: 16px;
    text-align: left;
    cursor: pointer;
    color: white;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
    transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
}

.status-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.16);
}

.status-btn.pack { background: linear-gradient(135deg, #2563eb, #1d4ed8); }
.status-btn.ship { background: linear-gradient(135deg, #0f766e, #0d9488); }
.status-btn.done { background: linear-gradient(135deg, #15803d, #16a34a); }

.status-btn.is-active {
    outline: 3px solid rgba(255, 255, 255, 0.38);
    outline-offset: -4px;
}

.status-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
}

.status-btn-title {
    display: block;
    font-size: 15px;
    font-weight: 800;
}

.status-btn-sub {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    opacity: 0.92;
}

.btn {
    padding: 11px 18px;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-block;
}

.btn-success {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.alert {
    padding: 14px 16px;
    border-radius: 12px;
    margin-top: 16px;
}

.alert-success {
    background: #dcfce7;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.alert-danger {
    background: #fee2e2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.alert-cash {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #92400e;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.table th {
    background: #f8fafc;
    font-weight: 700;
    color: #1f2937;
}

.mt-3,
.mt-4 {
    margin-top: 16px;
}

@media (max-width: 900px) {
    .grid.two-columns,
    .action-buttons,
    .status-actions,
    .mini-grid {
        grid-template-columns: 1fr;
    }

    .page-head,
    .section-head {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endsection
