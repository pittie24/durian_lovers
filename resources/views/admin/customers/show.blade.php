@extends('layouts.admin')

@section('content')
<h2>Detail Pelanggan</h2>

<div class="card">
    <div class="card-body">
        <h3>{{ $customer->display_name }}</h3>
        <p>{{ $customer->display_email }}</p>
        <p>{{ $customer->display_phone }}</p>
        <p>{{ $customer->address }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3>Riwayat Pesanan</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tanggal</th>
                    <th>Produk Dibeli</th>
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customer->orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            @if ($order->items->isNotEmpty())
                                <div class="order-items-list">
                                    @foreach ($order->items as $item)
                                        <div class="order-item-chip">
                                            <span class="order-item-name">
                                                {{ $item->product?->name ?? 'Produk dihapus' }}
                                                @if((int) $item->price === 0)
                                                    <span class="order-item-free">Free Item</span>
                                                @endif
                                            </span>
                                            <span class="order-item-qty">x{{ $item->quantity }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="order-items-empty">-</span>
                            @endif
                        </td>
                        <td>{{ str_replace('_', ' ', $order->status) }}</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada riwayat pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.order-items-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 240px;
}

.order-item-chip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 8px 10px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
}

.order-item-name {
    color: #1f2937;
    font-weight: 600;
}

.order-item-free {
    display: inline-flex;
    align-items: center;
    margin-left: 8px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #dcfce7;
    color: #166534;
    font-size: 11px;
    font-weight: 800;
}

.order-item-qty {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    padding: 4px 8px;
    border-radius: 999px;
    background: #fef3c7;
    color: #92400e;
    font-size: 12px;
    font-weight: 700;
}

.order-items-empty {
    color: #9ca3af;
    font-style: italic;
}
</style>
@endsection
