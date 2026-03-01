@extends('layouts.admin')

@section('content')
<div class="payment-confirmations-page">
    <h2>Konfirmasi Pembayaran</h2>

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
                        <th>ID</th>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Bank</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($confirmations as $confirmation)
                        <tr>
                            <td>#{{ $confirmation->id }}</td>
                            <td>
                                <a href="{{ route('admin.pesanan.show', $confirmation->order) }}">
                                    {{ $confirmation->order->order_number }}
                                </a>
                            </td>
                            <td>{{ $confirmation->user->name }}</td>
                            <td>{{ $confirmation->bank_name }}</td>
                            <td>Rp {{ number_format($confirmation->transfer_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($confirmation->status === 'PENDING')
                                    <span class="badge badge-warning">⏳ PENDING</span>
                                @elseif($confirmation->status === 'APPROVED')
                                    <span class="badge badge-success">✅ APPROVED</span>
                                @else
                                    <span class="badge badge-danger">❌ REJECTED</span>
                                @endif
                            </td>
                            <td>{{ $confirmation->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.payment-confirmations.show', $confirmation) }}" class="btn btn-sm btn-primary">
                                    👁 Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada konfirmasi pembayaran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($confirmations->hasPages())
                <div class="mt-4">
                    {{ $confirmations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.payment-confirmations-page h2 {
    margin-bottom: 24px;
    color: #2c3e50;
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
</style>
@endsection
