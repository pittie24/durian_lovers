@extends('layouts.admin')

@section('content')
<h2>Laporan Penjualan</h2>

<div class="grid cards stats">
    <div class="card">
        <div class="card-body">
            <h4>Total Pendapatan</h4>
            <div class="stat">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h4>Total Pesanan</h4>
            <div class="stat">{{ $totalOrders }}</div>
        </div>
    </div>
</div>

<div class="grid two-columns">
    <div class="card">
        <div class="card-body">
            <h3>Trend Harian</h3>
            <ul class="list">
                @foreach ($daily as $row)
                    <li>{{ $row->day }} - Rp {{ number_format($row->total, 0, ',', '.') }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h3>Produk Terlaris</h3>
            <ul class="list">
                @foreach ($bestProducts as $product)
                    <li>{{ $product->name }} ({{ $product->sold_count }} terjual)</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
