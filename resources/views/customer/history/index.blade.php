@extends('layouts.app')

@section('content')
<h2>Riwayat Pesanan</h2>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Status</th>
                    <th>Metode Pembayaran</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            @foreach ($order->items as $item)
                                <div>{{ $item->product->name }} (x{{ $item->quantity }})</div>
                            @endforeach
                        </td>
                        <td><span class="badge">{{ str_replace('_', ' ', $order->status) }}</span></td>
                        <td>{{ $order->payment_method }}</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>
                            <a href="/tracking/{{ $order->id }}" class="btn outline small">Tracking</a>
                            @if ($order->status === 'SELESAI')
                                @foreach ($order->items as $item)
                                    <a href="/rating/{{ $item->id }}" class="btn primary small">Beri Rating</a>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada pesanan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
