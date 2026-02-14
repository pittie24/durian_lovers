@extends('layouts.admin')

@section('content')
<h2>Detail Pesanan #{{ $order->id }}</h2>

<div class="grid two-columns">
    <div class="card">
        <div class="card-body">
            <h3>Info Pelanggan</h3>
            <p>{{ $order->user->name }}</p>
            <p>{{ $order->user->email }}</p>
            <p>{{ $order->phone }}</p>
            <p>{{ $order->shipping_address }}</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>Info Pesanan</h3>
            <div class="summary-row"><span>Metode Pengiriman</span><span>{{ $order->shipping_method }}</span></div>
            <div class="summary-row"><span>Metode Pembayaran</span><span>{{ $order->payment_method }}</span></div>
            <div class="summary-row"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h3>Produk yang dipesan</h3>
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
                @foreach ($order->items as $item)
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

<form method="POST" action="/admin/pesanan/{{ $order->id }}/status" class="status-form">
    @csrf
    <label>Update Status</label>
    <div class="button-group">
        @foreach (['PESANAN_DITERIMA','SEDANG_DIPROSES','SIAP_DIAMBIL_DIKIRIM','SELESAI'] as $status)
            <button type="submit" name="status" value="{{ $status }}" class="btn outline small">{{ str_replace('_', ' ', $status) }}</button>
        @endforeach
    </div>
</form>
@endsection
