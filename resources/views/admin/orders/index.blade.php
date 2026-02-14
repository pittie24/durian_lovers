@extends('layouts.admin')

@section('content')
<div class="orders-header">
    <h2 class="page-title">Manajemen Pesanan</h2>

    <a href="{{ url()->current() }}?status={{ $status }}" class="btn btn-refresh">
        <span class="icon">⟳</span> Refresh
    </a>
</div>

@php
  $tabs = [
    'SEMUA' => 'Semua',
    'PESANAN_DITERIMA' => 'Pesanan Diterima',
    'SEDANG_DIPROSES' => 'Sedang Diproses',
    'SIAP_DIAMBIL_DIKIRIM' => 'Siap Diambil/Dikirim',
    'SELESAI' => 'Selesai',
  ];
@endphp

<div class="tabs tabs-pill">
    @foreach ($tabs as $key => $label)
        <a href="/admin/pesanan?status={{ $key }}"
           class="tab {{ $status === $key ? 'active' : '' }}">
            {{ $label }} <span class="tab-count">({{ $counts[$key] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<div class="card card-table">
    <div class="card-body">
        <table class="table table-orders">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th class="th-actions">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="mono">#{{ $order->id }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td class="money">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>{{ $order->payment_method }}</td>
                        <td>
                            <span class="status-badge status-{{ strtolower($order->status) }}">
                                {{ str_replace('_', ' ', $order->status) }}
                            </span>
                        </td>
                        <td class="td-actions">
                            <a href="/admin/pesanan/{{ $order->id }}" class="btn outline small">Detail</a>
                        </td>
                    </tr>
               @empty
<tr class="empty-row">
  <td colspan="7">
    <div class="empty-state">
      Tidak ada pesanan
    </div>
  </td>
</tr>
@endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
