@extends('layouts.admin')

@section('content')
@php
    use Carbon\Carbon;

    // ====== INPUT FILTER (diambil dari controller) ======
    $view = $view ?? request('view', request('range', 'daily'));
    $startDate = $startDate ?? request('start_date', Carbon::now()->subDays(7)->toDateString());
    $endDate   = $endDate ?? request('end_date', Carbon::now()->toDateString());

    // ====== STATS (dari controller) ======
    $totalRevenueVal = $totalRevenue ?? 0;
    $totalOrdersVal  = $totalOrders ?? 0;
    $avgOrderVal = ($totalOrdersVal > 0) ? ($totalRevenueVal / $totalOrdersVal) : 0;

    $totalSoldVal = 0;
    if (!empty($bestProducts)) {
        foreach ($bestProducts as $bp) $totalSoldVal += (int)($bp->sold_count ?? 0);
    }

    // ====== TREND CHART DATA (sudah dari controller agar daily/weekly/monthly bisa) ======
    $trendLabels  = $trendLabels ?? [];
    $trendRevenue = $trendRevenue ?? [];
    $trendOrders  = $trendOrders ?? [];

    // ====== BAR PRODUK TERLARIS ======
    $bestLabels = $bestLabels ?? [];
    $bestSold   = $bestSold ?? [];

    // ====== PIE KATEGORI ======
    $categoryLabels = $categoryLabels ?? [];
    $categoryValues = $categoryValues ?? [];

    // ====== REKAP DETAIL (sudah dari controller) ======
    $rows = $rows ?? [];

    $sumOrders  = $sumOrders ?? 0;
    $sumRevenue = $sumRevenue ?? 0;
    $sumAvg     = $sumAvg ?? 0;
@endphp

<div class="report-topbar">
    <div>
        <h2 class="report-h1">Laporan Penjualan</h2>
    </div>

    <a class="btn success"
       href="{{ url('/admin/laporan/export-html?view='.$view.'&start_date='.$startDate.'&end_date='.$endDate) }}">
        <i class="bi bi-download"></i> Export Laporan
    </a>
</div>

{{-- FILTER PERIODE --}}
<div class="report-filter card">
    <div class="card-body">
        <div class="filter-title">Filter Periode</div>

        <div class="filter-quick">
            <button type="button" class="chip" data-preset="today">Hari ini</button>
            <button type="button" class="chip" data-preset="7">7 Hari Terakhir</button>
            <button type="button" class="chip" data-preset="30">30 Hari Terakhir</button>
            <button type="button" class="chip" data-preset="365">1 Tahun Terakhir</button>
        </div>

        <form id="reportFilterForm" class="filter-form" method="GET" action="{{ url('/admin/laporan') }}">
            <div class="field">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </div>

            <div class="field">
                <label>Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </div>

            <div class="field">
                <label>Tampilan Grafik</label>
                <select name="view">
                    <option value="daily" {{ $view==='daily'?'selected':'' }}>Harian</option>
                    <option value="weekly" {{ $view==='weekly'?'selected':'' }}>Mingguan</option>
                    <option value="monthly" {{ $view==='monthly'?'selected':'' }}>Bulanan</option>
                </select>
            </div>

            <div class="field actions">
                <button class="btn primary" type="submit">
                    <i class="bi bi-funnel"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 4 KARTU STAT --}}
<div class="report-stats-grid">
    <div class="report-stat card">
        <div class="card-body">
            <div class="stat-label">Total Pesanan</div>
            <div class="stat-value">{{ $totalOrdersVal }}</div>
        </div>
    </div>

    <div class="report-stat card">
        <div class="card-body">
            <div class="stat-label">Total Pendapatan</div>
            <div class="stat-value">Rp {{ number_format($totalRevenueVal, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="report-stat card">
        <div class="card-body">
            <div class="stat-label">Rata-rata Pesanan</div>
            <div class="stat-value">Rp {{ number_format($avgOrderVal, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="report-stat card">
        <div class="card-body">
            <div class="stat-label">Produk Terjual</div>
            <div class="stat-value">{{ $totalSoldVal }}</div>
        </div>
    </div>
</div>

{{-- CHARTS --}}
<div class="report-charts-grid">
    <div class="card">
        <div class="card-body">
            <div class="panel-title">
                Trend Penjualan ({{ $view === 'daily' ? 'Harian' : ($view === 'weekly' ? 'Mingguan' : 'Bulanan') }})
            </div>
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

{{-- PRODUK TERLARIS --}}
<div class="card report-wide">
    <div class="card-body">
        <div class="panel-title">Produk Terlaris</div>
        <div class="chart-wrap tall">
            <canvas id="bestChart"></canvas>
        </div>
    </div>
</div>

{{-- REKAP DETAIL --}}
<div class="card report-wide">
    <div class="card-body">
        <div class="panel-title">Rekap Detail</div>

        <div class="table-wrap clean">
            <table class="table report-table">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="right">Jumlah Pesanan</th>
                        <th class="right">Pendapatan</th>
                        <th class="right">Rata-rata</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($rows))
                        @foreach($rows as $r)
                            <tr>
                                <td>{{ $r['label'] }}</td>
                                <td class="right">{{ $r['orders'] }}</td>
                                <td class="right">Rp {{ number_format($r['revenue'], 0, ',', '.') }}</td>
                                <td class="right">
                                    {!! $r['avg'] === null ? '-' : ('Rp ' . number_format($r['avg'], 0, ',', '.')) !!}
                                </td>
                            </tr>
                        @endforeach

                        <tr class="total-row">
                            <td><b>TOTAL</b></td>
                            <td class="right"><b>{{ $sumOrders }}</b></td>
                            <td class="right"><b>Rp {{ number_format($sumRevenue, 0, ',', '.') }}</b></td>
                            <td class="right"><b>Rp {{ number_format($sumAvg, 0, ',', '.') }}</b></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="4" class="empty-row">Tidak ada data untuk periode ini.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // ===== TREND =====
    let trendLabels  = @json($trendLabels);
    let trendRevenue = @json($trendRevenue);
    let trendOrders  = @json($trendOrders);

    // ✅ FIX: kalau cuma 1 titik data, tambahin label dummy kiri & kanan
    // supaya tanggalnya ada di tengah dan garis/titik kelihatan.
    if (trendLabels.length === 1) {
        const onlyLabel = trendLabels[0];
        const onlyRev   = trendRevenue[0] ?? 0;
        const onlyOrd   = trendOrders[0] ?? 0;

        trendLabels  = [' ', onlyLabel, ' '];
        trendRevenue = [0, onlyRev, 0];
        trendOrders  = [0, onlyOrd, 0];
    }

    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'Pendapatan (ribu)',
                        data: trendRevenue.map(v => Math.round((v ?? 0)/1000)),
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        spanGaps: true,
                    },
                    {
                        label: 'Pesanan',
                        data: trendOrders.map(v => (v ?? 0)),
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        spanGaps: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // ===== PIE KATEGORI =====
    const categoryLabels = @json($categoryLabels);
    const categoryValues = @json($categoryValues);

    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx, {
            type: 'pie',
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
                plugins: { legend: { position: 'left' } }
            }
        });
    }

    // ===== PRODUK TERLARIS =====
    const bestLabels = @json($bestLabels);
    const bestSold   = @json($bestSold);

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

    // ===== QUICK PRESETS (AUTO SUBMIT) =====
    function toISO(d){ return d.toISOString().slice(0,10); }

    const form = document.getElementById('reportFilterForm');
    const startInput = document.querySelector('input[name="start_date"]');
    const endInput   = document.querySelector('input[name="end_date"]');

    document.querySelectorAll('[data-preset]').forEach(btn => {
        btn.addEventListener('click', () => {
            const preset = btn.getAttribute('data-preset');
            const end = new Date();
            let start = new Date();

            if (preset === 'today') {
                // start=end
            } else {
                start.setDate(end.getDate() - parseInt(preset,10) + 1);
            }

            startInput.value = toISO(start);
            endInput.value = toISO(end);

            form.submit();
        });
    });
</script>
@endsection
