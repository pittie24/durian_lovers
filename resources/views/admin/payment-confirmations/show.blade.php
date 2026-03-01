@extends('layouts.admin')

@section('content')
<div class="payment-confirmation-detail">
    <h2>Detail Konfirmasi Pembayaran</h2>

    <a href="{{ route('admin.payment-confirmations.index') }}" class="btn btn-outline mb-4">
        ← Kembali
    </a>

    <div class="grid two-columns">
        {{-- Confirmation Info --}}
        <div class="card">
            <div class="card-body">
                <h4>Info Konfirmasi</h4>
                <table class="table-detail">
                    <tr>
                        <td>ID Konfirmasi</td>
                        <td>: #{{ $confirmation->id }}</td>
                    </tr>
                    <tr>
                        <td>Order</td>
                        <td>: <a href="{{ route('admin.pesanan.show', $confirmation->order) }}">{{ $confirmation->order->order_number }}</a></td>
                    </tr>
                    <tr>
                        <td>Pelanggan</td>
                        <td>: {{ $confirmation->user->name }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>: {{ $confirmation->user->email }}</td>
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
                        <td>: <strong class="text-primary">Rp {{ number_format($confirmation->transfer_amount, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tanggal Upload</td>
                        <td>: {{ $confirmation->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Order Info --}}
        <div class="card">
            <div class="card-body">
                <h4>Info Pesanan</h4>
                <table class="table-detail">
                    <tr>
                        <td>Status Order</td>
                        <td>: {{ $confirmation->order->status }}</td>
                    </tr>
                    <tr>
                        <td>Total Pembayaran</td>
                        <td>: <strong>Rp {{ number_format($confirmation->order->total, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Metode Pengiriman</td>
                        <td>: {{ $confirmation->order->shipping_method }}</td>
                    </tr>
                    <tr>
                        <td>Metode Pembayaran</td>
                        <td>: {{ $confirmation->order->payment_method }}</td>
                    </tr>
                </table>

                @if($confirmation->isApproved())
                    <div class="alert alert-success mt-3">
                        <strong>✅ Disetujui oleh:</strong> {{ $confirmation->verifiedBy?->name ?? 'N/A' }}<br>
                        <strong>Pada:</strong> {{ $confirmation->verified_at->format('d M Y, H:i') }}
                    </div>
                @elseif($confirmation->isRejected())
                    <div class="alert alert-danger mt-3">
                        <strong>❌ Ditolak oleh:</strong> {{ $confirmation->verifiedBy?->name ?? 'N/A' }}<br>
                        <strong>Pada:</strong> {{ $confirmation->verified_at->format('d M Y, H:i') }}<br>
                        <strong>Alasan:</strong> {{ $confirmation->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Proof Image --}}
    <div class="card mt-4">
        <div class="card-body">
            <h4>Bukti Transfer</h4>
            <div class="proof-image-container">
                <img src="{{ Storage::url($confirmation->proof_image) }}" alt="Bukti Transfer" style="max-width: 100%; border-radius: 8px;">
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    @if($confirmation->isPending())
        <div class="card mt-4">
            <div class="card-body">
                <h4>Aksi Verifikasi</h4>
                
                <div class="action-buttons">
                    {{-- Approve Form --}}
                    <form action="{{ route('admin.payment-confirmations.approve', $confirmation) }}" method="POST" class="inline-form">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Apakah Anda yakin ingin menyetujui konfirmasi pembayaran ini?')">
                            ✅ Setujui
                        </button>
                    </form>

                    {{-- Reject Form --}}
                    <form action="{{ route('admin.payment-confirmations.reject', $confirmation) }}" method="POST" class="inline-form" id="reject-form">
                        @csrf
                        <div class="form-group">
                            <label for="reject-notes">Alasan Penolakan:</label>
                            <textarea name="notes" id="reject-notes" class="form-control" rows="3" required placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menolak konfirmasi pembayaran ini?')">
                            ❌ Tolak
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Order Items --}}
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
                    @foreach($confirmation->order->items as $item)
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
.payment-confirmation-detail h2 {
    margin-bottom: 24px;
    color: #2c3e50;
}

.grid.two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .grid.two-columns {
        grid-template-columns: 1fr;
    }
}

.table-detail {
    width: 100%;
}

.table-detail td {
    padding: 8px 0;
}

.table-detail td:first-child {
    font-weight: 600;
    width: 150px;
    color: #666;
}

.table-detail a {
    color: #27ae60;
    text-decoration: none;
}

.text-primary {
    color: #27ae60;
}

.proof-image-container {
    text-align: center;
    padding: 20px;
}

.proof-image-container img {
    max-width: 500px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.action-buttons {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    flex-wrap: wrap;
}

.inline-form {
    display: inline-block;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-family: inherit;
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

.btn-outline {
    background: transparent;
    border: 2px solid #6c757d;
    color: #6c757d;
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.alert {
    padding: 16px;
    border-radius: 8px;
    margin-top: 16px;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
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
}

.mb-4 {
    margin-bottom: 24px;
}

.mt-3,
.mt-4 {
    margin-top: 16px;
}
</style>
@endsection
