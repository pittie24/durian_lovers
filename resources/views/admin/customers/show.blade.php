@extends('layouts.admin')

@section('content')
<h2>Detail Pelanggan</h2>

<div class="card">
    <div class="card-body">
        <h3>{{ $customer->name }}</h3>
        <p>{{ $customer->email }}</p>
        <p>{{ $customer->phone }}</p>
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
                    <th>Status</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customer->orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>{{ str_replace('_', ' ', $order->status) }}</td>
                        <td>Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
