@extends('layouts.admin')

@section('content')
<div class="container">
  <h1 class="page-title">Dashboard</h1>

  {{-- Summary Cards --}}
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon bg-blue">🛒</div>
      <div class="stat-meta">
        <div class="stat-label">Total Pesanan</div>
        <div class="stat-value">{{ $totalOrders }}</div>
      </div>
      <div class="stat-trend">↗</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-green">💰</div>
      <div class="stat-meta">
        <div class="stat-label">Total Pendapatan</div>
        <div class="stat-value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
      </div>
      <div class="stat-trend">↗</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-yellow">👥</div>
      <div class="stat-meta">
        <div class="stat-label">Total Pelanggan</div>
        <div class="stat-value">{{ $totalCustomers }}</div>
      </div>
      <div class="stat-trend">↗</div>
    </div>

    <div class="stat-card">
      <div class="stat-icon bg-purple">📈</div>
      <div class="stat-meta">
        <div class="stat-label">Rata-rata Pendapatan</div>
        <div class="stat-value">
          @if($totalOrders > 0)
            Rp {{ number_format($averageRevenue, 0, ',', '.') }}
          @else
            -
          @endif
        </div>
      </div>
      <div class="stat-trend">↗</div>
    </div>
  </div>

  {{-- Charts --}}
  <div class="charts-grid">
    <div class="card chart-card">
      <div class="card-body">
        <div class="card-title">Penjualan 7 Hari Terakhir</div>
        <canvas id="salesChart"></canvas>
      </div>
    </div>

    <div class="card chart-card">
      <div class="card-body">
        <div class="card-title">Produk Terlaris</div>
        <canvas id="topProductsChart"></canvas>
      </div>
    </div>
  </div>

  {{-- Recent Orders Table --}}
  <div class="card table-card mt-16">
    <div class="card-body">
      <div class="card-title">Pesanan Terbaru</div>

      <table class="table table-orders">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Pelanggan</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
          @forelse ($recentOrders as $order)
            <tr>
              <td class="mono">#{{ str_pad($order->id, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($order->id, 3, '0', STR_PAD_LEFT) }}</td>
              <td>{{ $order->user->name ?? '-' }}</td>
              <td>{{ $order->created_at->format('d/m/Y') }}</td>
              <td class="money">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
              <td>
                @php
                  $key = strtolower($order->status ?? 'pending');
                  $key = str_replace(' ', '_', $key);
                @endphp
                <span class="status-badge status-{{ $key }}">
                  {{ ucwords(str_replace('_', ' ', $key)) }}
                </span>
              </td>
            </tr>
          @empty
            <tr class="empty-row">
              <td colspan="5">
                <div class="empty-state">Belum ada pesanan masuk.</div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Data JSON untuk chart (aman, tidak bikin VSCode/Blade ribet) --}}
  <script type="application/json" id="chart-sales-labels">{!! json_encode($salesLabels ?? []) !!}</script>
  <script type="application/json" id="chart-sales-revenue">{!! json_encode($salesRevenue ?? []) !!}</script>
  <script type="application/json" id="chart-sales-orders">{!! json_encode($salesOrders ?? []) !!}</script>

  <script type="application/json" id="chart-top-labels">{!! json_encode($topProductLabels ?? []) !!}</script>
  <script type="application/json" id="chart-top-sales">{!! json_encode($topProductSales ?? []) !!}</script>

  {{-- Chart.js --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <script>
    (function () {
      const getJson = (id) => {
        const el = document.getElementById(id);
        if (!el) return [];
        try { return JSON.parse(el.textContent || '[]'); } catch (e) { return []; }
      };

      const salesLabels  = getJson('chart-sales-labels');
      const salesRevenue = getJson('chart-sales-revenue');
      const salesOrders  = getJson('chart-sales-orders');

      const topLabels = getJson('chart-top-labels');
      const topSales  = getJson('chart-top-sales');

      const salesCanvas = document.getElementById('salesChart');
      const topCanvas   = document.getElementById('topProductsChart');

      if (typeof Chart === 'undefined') return;

      // LINE CHART
      if (salesCanvas) {
        new Chart(salesCanvas, {
          type: 'line',
          data: {
            labels: salesLabels,
            datasets: [
              {
                label: 'Pendapatan',
                data: salesRevenue,
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 4,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,.15)'
              },
              {
                label: 'Jumlah Pesanan',
                data: salesOrders,
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 4,
                borderColor: '#60a5fa',
                backgroundColor: 'rgba(96,165,250,.15)'
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { position: 'bottom', labels: { boxWidth: 14, boxHeight: 14 } },
              tooltip: {
                callbacks: {
                  label: (ctx) => {
                    const label = ctx.dataset.label || '';
                    const val = ctx.raw ?? 0;
                    if (label.toLowerCase().includes('pendapatan')) {
                      return `${label}: Rp ${Number(val).toLocaleString('id-ID')}`;
                    }
                    return `${label}: ${val}`;
                  }
                }
              }
            },
            scales: {
              x: { grid: { display: false } },
              y: {
                beginAtZero: true,
                grid: { color: 'rgba(15,23,42,.06)' }
              }
            }
          }
        });
      }

      // BAR CHART
      if (topCanvas) {
        new Chart(topCanvas, {
          type: 'bar',
          data: {
            labels: topLabels,
            datasets: [
              {
                label: 'Terjual',
                data: topSales,
                borderWidth: 1,
                borderRadius: 10,
                backgroundColor: 'rgba(245,158,11,.65)',
                borderColor: '#f59e0b'
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: {
                grid: { display: false },
                ticks: { maxRotation: 20, minRotation: 20 }
              },
              y: {
                beginAtZero: true,
                grid: { color: 'rgba(15,23,42,.06)' }
              }
            }
          }
        });
      }
    })();
  </script>
</div>
@endsection
