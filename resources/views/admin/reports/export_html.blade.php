<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Laporan Penjualan Durian Lovers</title>
  <style>
    body{font-family: Arial, sans-serif; background:#fff; margin:0; padding:0; color:#111}
    .wrap{width:92%; margin:24px auto 40px;}
    .title{font-weight:900; text-align:center; font-size:22px; color:#a85c00; letter-spacing:.5px;}
    .box-info{background:#fff3c8; padding:14px 16px; border-radius:6px; margin:14px 0 18px;}
    .box-info p{margin:6px 0; font-size:12px;}
    .cards{display:grid; grid-template-columns: repeat(4,1fr); gap:14px; margin-bottom:18px;}
    .card{border:2px solid #e3a400; border-radius:6px; padding:12px; text-align:center;}
    .card .label{font-size:12px; font-weight:800; color:#a85c00; margin-bottom:6px;}
    .card .value{font-size:18px; font-weight:900;}

    h3{margin:18px 0 8px; font-size:16px;}
    table{width:100%; border-collapse:collapse; font-size:12px;}
    thead th{background:#e3a400; color:#fff; text-align:left; padding:10px;}
    tbody td{border-bottom:1px solid #eee; padding:10px;}
    .right{text-align:right;}
    .total-row td{background:#fff3c8; font-weight:900;}

    .footer{margin-top:22px; text-align:center; font-size:11px; color:#666;}
    @media print{
      .wrap{width:100%; margin:0; padding:0 10px;}
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="title">🍈 LAPORAN PENJUALAN DURIAN LOVERS</div>

    <div class="box-info">
      <p><b>Periode Laporan:</b> {{ $rangeLabel }}</p>
      <p><b>Tanggal:</b> {{ $startDate }} - {{ $endDate }}</p>
      <p><b>Tanggal Export:</b> {{ $nowId->format('d/m/Y, H:i:s') }}</p>
    </div>

    <div class="cards">
      <div class="card">
        <div class="label">Total Pesanan</div>
        <div class="value">{{ $totalOrders }}</div>
      </div>
      <div class="card">
        <div class="label">Total Pendapatan</div>
        <div class="value">Rp {{ number_format($totalRevenue,0,',','.') }}</div>
      </div>
      <div class="card">
        <div class="label">Rata-rata Pesanan</div>
        <div class="value">Rp {{ number_format($avgOrder,0,',','.') }}</div>
      </div>
      <div class="card">
        <div class="label">Produk Terjual</div>
        <div class="value">{{ $totalSoldVal }}</div>
      </div>
    </div>

    <h3>Detail Penjualan {{ $rangeLabel }}</h3>
    <table>
      <thead>
        <tr>
          <th style="width:40%">Periode</th>
          <th class="right" style="width:20%">Jumlah Pesanan</th>
          <th class="right" style="width:20%">Pendapatan</th>
          <th class="right" style="width:20%">Rata-rata per Pesanan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailRows as $r)
          <tr>
            <td>{{ $r->label }}</td>
            <td class="right">{{ $r->orders }}</td>
            <td class="right">Rp {{ number_format($r->revenue,0,',','.') }}</td>
            <td class="right">Rp {{ number_format($r->avg,0,',','.') }}</td>
          </tr>
        @endforeach

        <tr class="total-row">
          <td>TOTAL</td>
          <td class="right">{{ $detailTotalOrders }}</td>
          <td class="right">Rp {{ number_format($detailTotalRevenue,0,',','.') }}</td>
          <td class="right">Rp {{ number_format($detailTotalAvg,0,',','.') }}</td>
        </tr>
      </tbody>
    </table>

    <h3>Produk Terlaris</h3>
    <table>
      <thead>
        <tr>
          <th>Produk</th>
          <th class="right">Jumlah Terjual</th>
          <th class="right">Total Pendapatan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($topProducts as $p)
          <tr>
            <td>{{ $p->name }}</td>
            <td class="right">{{ (int)$p->qty_sold }}</td>
            <td class="right">Rp {{ number_format((float)$p->revenue,0,',','.') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" style="text-align:center;color:#777;padding:14px;">Tidak ada data produk pada periode ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="footer">
      © {{ date('Y') }} Durian Lovers. All rights reserved.
    </div>
  </div>
</body>
</html>