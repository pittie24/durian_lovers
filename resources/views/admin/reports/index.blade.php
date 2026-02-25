@extends('layouts.admin')

@section('content')
@php
    // ====== FILTER PERIODE (UI) ======
    $range = request('range', 'daily'); // daily | weekly | monthly

    // ====== STATS ======
    $totalRevenueVal = $totalRevenue ?? 0;
    $totalOrdersVal  = $totalOrders ?? 0;

    // rata-rata pesanan (avg order value)
    $avgOrderVal = ($totalOrdersVal > 0) ? ($totalRevenueVal / $totalOrdersVal) : 0;

    // produk terjual (sementara: jumlah sold_count dari bestProducts)
    $totalSoldVal = 0;
    if (!empty($bestProducts)) {
        foreach ($bestProducts as $bp) {
            $totalSoldVal += (int) ($bp->sold_count ?? 0);
        }
    }

    // ====== DATA CHART TREND ======
    $trendLabels = [];
    $trendRevenue = [];
    if (!empty($daily)) {
        foreach ($daily as $row) {
            $trendLabels[] = $row->day;
            $trendRevenue[] = (float) $row->total;
        }
    }

    // ====== DATA BAR PRODUK TERLARIS ======
    $bestLabels = [];
    $bestSold = [];
    if (!empty($bestProducts)) {
        foreach ($bestProducts as $p) {
            $bestLabels[] = $p->name;
            $bestSold[] = (int) ($p->sold_count ?? 0);
        }
    }

    // ====== DATA KATEGORI (sementara) ======
    $categoryLabels = $categoryLabels ?? [];
    $categoryValues = $categoryValues ?? [];
@endphp

<div class="report-header">
    <div>
        <h2 class="report-title">Laporan Penjualan</h2>

        <div class="report-tabs">
            <a class="tab {{ $range === 'daily' ? 'active' : '' }}" href="{{ url('/admin/laporan?range=daily') }}">
                <i class="bi bi-calendar-event"></i> Harian
            </a>
            <a class="tab {{ $range === 'weekly' ? 'active' : '' }}" href="{{ url('/admin/laporan?range=weekly') }}">
                <i class="bi bi-calendar-week"></i> Mingguan
            </a>
            <a class="tab {{ $range === 'monthly' ? 'active' : '' }}" href="{{ url('/admin/laporan?range=monthly') }}">
                <i class="bi bi-calendar3"></i> Bulanan
            </a>
        </div>
    </div>

    <div class="report-actions">
<a class="btn success" href="{{ url('/admin/laporan/export?range=' . request('range','daily')) }}">
    <i class="bi bi-download"></i> Export CSV
</a>
    </div>
</div>

{{-- 4 KARTU STAT --}}
<div class="grid cards report-stats">
    <div class="card stat-card">
        <div class="card-body">
            <div class="stat-label">Total Pesanan</div>
            <div class="stat-value">{{ $totalOrdersVal }}</div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body">
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value">Rp {{ number_format($totalRevenueVal, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body">
            <div class="stat-label">Rata-rata Pesanan</div>
            <div class="stat-value">Rp {{ number_format($avgOrderVal, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="card stat-card">
        <div class="card-body">
            <div class="stat-label">Produk Terjual</div>
            <div class="stat-value">{{ $totalSoldVal }}</div>
        </div>
    </div>
</div>

{{-- ROW: TREND + KATEGORI --}}
<div class="grid two-columns report-panels">
    <div class="card">
        <div class="card-body">
            <div class="panel-title">Trend Penjualan</div>
            <div class="chart-wrap">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="panel-title">Penjualan per Kategori</div>
            <div class="chart-wrap">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- PRODUK TERLARIS (BAR) --}}
<div class="card report-wide">
    <div class="card-body">
        <div class="panel-title">Produk Terlaris</div>
        <div class="chart-wrap tall">
            <canvas id="bestChart"></canvas>
        </div>
    </div>
</div>

{{-- REKAP DETAIL (TABEL) --}}
<div class="card report-wide">
    <div class="card-body">
        <div class="panel-title">Rekap Detail</div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th style="text-align:right;">Jumlah Pesanan</th>
                        <th style="text-align:right;">Pendapatan</th>
                        <th style="text-align:right;">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($daily) && $range === 'daily')
                        @foreach($daily as $row)
                            @php
                                $ordersCount = $row->orders_count ?? 0; // kalau belum ada, tampil "-"
                                $rev = (float) ($row->total ?? 0);
                                $avg = ($ordersCount > 0) ? ($rev / $ordersCount) : 0;
                            @endphp
                            <tr>
                                <td>{{ $row->day }}</td>
                                <td style="text-align:right;">{{ $ordersCount ? $ordersCount : '-' }}</td>
                                <td style="text-align:right;">Rp {{ number_format($rev, 0, ',', '.') }}</td>
                                <td style="text-align:right;">{{ $ordersCount ? ('Rp ' . number_format($avg, 0, ',', '.')) : '-' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="empty-row">
                                Tidak ada data untuk periode <b>{{ $range }}</b>.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Trend Chart (Line)
    const trendLabels = @json($trendLabels);
    const trendRevenue = @json($trendRevenue);

    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pendapatan',
                    data: trendRevenue,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { ticks: { callback: (v) => 'Rp ' + v.toLocaleString('id-ID') } }
                }
            }
        });
    }

    // Category Chart (Bar)
    const categoryLabels = @json($categoryLabels);
    const categoryValues = @json($categoryValues);

    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Pendapatan',
                    data: categoryValues,
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { ticks: { callback: (v) => 'Rp ' + v.toLocaleString('id-ID') } }
                }
            }
        });
    }

    // Best Products Chart (Bar)
    const bestLabels = @json($bestLabels);
    const bestSold = @json($bestSold);

    const bestCtx = document.getElementById('bestChart');
    if (bestCtx) {
        new Chart(bestCtx, {
            type: 'bar',
            data: {
                labels: bestLabels,
                datasets: [{
                    label: 'Jumlah Terjual',
                    data: bestSold,
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
</script>
@endsection