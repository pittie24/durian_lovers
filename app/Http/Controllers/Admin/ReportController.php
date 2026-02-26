<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $view  = $request->get('view', $request->get('range', 'daily')); // daily|weekly|monthly
        $range = $view;

        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->toDateString());
        $endDate   = $request->get('end_date', Carbon::now()->toDateString());

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        // ===== SUMMARY =====
        $totalRevenue = (float) Order::whereBetween('created_at', [$start, $end])->sum('total');
        $totalOrders  = (int)   Order::whereBetween('created_at', [$start, $end])->count();

        // ===== PRODUK TERLARIS (ikut filter tanggal) =====
        $bestProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw('p.id, p.name, SUM(oi.quantity) as sold_count')
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        $bestLabels = $bestProducts->pluck('name')->values();
        $bestSold   = $bestProducts->pluck('sold_count')->map(fn($v) => (int)$v)->values();

        // ===== TREND + REKAP (ikut view) =====
        $groupKeyExpr = $this->groupKeyExpr($view);
        $orderExpr    = $this->orderExpr($view);

        $trendRows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("$groupKeyExpr as grp")
            ->selectRaw("SUM(total) as revenue")
            ->selectRaw("COUNT(*) as orders_count")
            ->groupBy('grp')
            ->orderByRaw($orderExpr)
            ->get();

        $trendLabels = [];
        $trendRevenue = [];
        $trendOrders = [];

        $rows = [];
        $sumOrders = 0;
        $sumRevenue = 0;

        foreach ($trendRows as $r) {
            $label = $this->formatLabel($view, $r->grp);

            $rev = (float) $r->revenue;
            $ord = (int) $r->orders_count;
            $avg = $ord > 0 ? ($rev / $ord) : null;

            $trendLabels[]  = $label;
            $trendRevenue[] = $rev;
            $trendOrders[]  = $ord;

            $rows[] = [
                'label'  => $label,
                'orders' => $ord,
                'revenue'=> $rev,
                'avg'    => $avg,
            ];

            $sumOrders  += $ord;
            $sumRevenue += $rev;
        }

        $sumAvg = $sumOrders > 0 ? $sumRevenue / $sumOrders : 0;

        // ===== PIE KATEGORI (ikut filter tanggal) =====
        $categoryRows = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw("COALESCE(p.category, 'Lainnya') as category")
            ->selectRaw("SUM(oi.total) as revenue")
            ->groupBy('category')
            ->orderByDesc('revenue')
            ->get();

        $categoryLabels = $categoryRows->pluck('category')->values();
        $categoryValues = $categoryRows->pluck('revenue')->map(fn ($v) => (float) $v)->values();

        return view('admin.reports.index', compact(
            'range',
            'view',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalOrders',
            'bestProducts',
            'bestLabels',
            'bestSold',
            'trendLabels',
            'trendRevenue',
            'trendOrders',
            'rows',
            'sumOrders',
            'sumRevenue',
            'sumAvg',
            'categoryLabels',
            'categoryValues'
        ));
    }

    public function exportCsv(Request $request)
    {
        $view  = $request->get('view', $request->get('range', 'daily'));
        $range = $view;

        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->toDateString());
        $endDate   = $request->get('end_date', Carbon::now()->toDateString());

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        $nowId = now()->timezone('Asia/Jakarta');

        $rangeLabel = match ($range) {
            'weekly'  => 'Mingguan',
            'monthly' => 'Bulanan',
            default   => 'Harian',
        };

        $totalRevenue = (float) Order::whereBetween('created_at', [$start, $end])->sum('total');
        $totalOrders  = (int)   Order::whereBetween('created_at', [$start, $end])->count();
        $avgOrder     = ($totalOrders > 0) ? ($totalRevenue / $totalOrders) : 0;

        $totalSoldVal = (int) DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->sum('oi.quantity');

        $groupKeyExpr = $this->groupKeyExpr($range);
        $orderExpr    = $this->orderExpr($range);

        $rows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("$groupKeyExpr as grp")
            ->selectRaw("COUNT(*) as jumlah_pesanan")
            ->selectRaw("SUM(total) as pendapatan")
            ->groupBy('grp')
            ->orderByRaw($orderExpr)
            ->get();

        $filename = "laporan-{$range}-{$startDate}_sd_{$endDate}-" . $nowId->format('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rangeLabel, $nowId, $startDate, $endDate, $totalOrders, $totalRevenue, $avgOrder, $totalSoldVal, $rows, $range) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Laporan Penjualan Durian Lovers']);
            fputcsv($out, ['Periode', $rangeLabel]);
            fputcsv($out, ['Rentang', "{$startDate} s/d {$endDate}"]);
            fputcsv($out, ['Tanggal Export', $nowId->format('d/m/Y H:i')]);
            fputcsv($out, []);

            fputcsv($out, ['Total Pesanan', $totalOrders]);
            fputcsv($out, ['Total Pendapatan', $totalRevenue]);
            fputcsv($out, ['Rata-rata Pesanan', $avgOrder]);
            fputcsv($out, ['Total Produk Terjual', $totalSoldVal]);
            fputcsv($out, []);

            fputcsv($out, ['Periode', 'Jumlah Pesanan', 'Pendapatan']);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $this->formatLabel($range, $r->grp),
                    (int) $r->jumlah_pesanan,
                    (float) $r->pendapatan,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * ✅ EXPORT HTML (tampilan impian)
     * route: /admin/laporan/export-html
     */
    public function exportHtml(Request $request)
    {
        $view  = $request->get('view', $request->get('range', 'daily'));
        $range = $view;

        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->toDateString());
        $endDate   = $request->get('end_date', Carbon::now()->toDateString());

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        $nowId = now()->timezone('Asia/Jakarta');

        $rangeLabel = match ($range) {
            'weekly'  => 'Mingguan',
            'monthly' => 'Bulanan',
            default   => 'Harian',
        };

        // ===== SUMMARY =====
        $totalRevenue = (float) Order::whereBetween('created_at', [$start, $end])->sum('total');
        $totalOrders  = (int)   Order::whereBetween('created_at', [$start, $end])->count();
        $avgOrder     = ($totalOrders > 0) ? ($totalRevenue / $totalOrders) : 0;

        $totalSoldVal = (int) DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->sum('oi.quantity');

        // ===== DETAIL PENJUALAN (ikut range) =====
        $groupKeyExpr = $this->groupKeyExpr($range);
        $orderExpr    = $this->orderExpr($range);

        $detailRows = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("$groupKeyExpr as grp")
            ->selectRaw("COUNT(*) as jumlah_pesanan")
            ->selectRaw("SUM(total) as pendapatan")
            ->groupBy('grp')
            ->orderByRaw($orderExpr)
            ->get()
            ->map(function ($r) use ($range) {
                $label = $this->formatLabel($range, $r->grp);
                $rev = (float) $r->pendapatan;
                $ord = (int) $r->jumlah_pesanan;

                return (object) [
                    'label' => $label,
                    'orders' => $ord,
                    'revenue' => $rev,
                    'avg' => $ord > 0 ? ($rev / $ord) : 0,
                ];
            });

        $detailTotalOrders  = (int) $detailRows->sum('orders');
        $detailTotalRevenue = (float) $detailRows->sum('revenue');
        $detailTotalAvg     = $detailTotalOrders > 0 ? $detailTotalRevenue / $detailTotalOrders : 0;

        // ===== PRODUK TERLARIS (FIX undefined & RpNaN) =====
        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('products as p', 'p.id', '=', 'oi.product_id')
            ->whereBetween('o.created_at', [$start, $end])
            ->selectRaw('p.id, p.name, SUM(oi.quantity) as qty_sold, SUM(oi.total) as revenue')
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('qty_sold')
            ->take(5)
            ->get();

        return view('admin.reports.export_html', compact(
            'rangeLabel',
            'startDate',
            'endDate',
            'nowId',
            'totalOrders',
            'totalRevenue',
            'avgOrder',
            'totalSoldVal',
            'detailRows',
            'detailTotalOrders',
            'detailTotalRevenue',
            'detailTotalAvg',
            'topProducts'
        ));
    }

    // group key khusus SQL
    private function groupKeyExpr(string $range): string
    {
        return match ($range) {
            'weekly'  => "DATE_SUB(DATE(created_at), INTERVAL WEEKDAY(created_at) DAY)",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m-01')",
            default   => "DATE(created_at)",
        };
    }

    // urutan group
    private function orderExpr(string $range): string
    {
        return "grp ASC";
    }

    // label tampilan
    private function formatLabel(string $range, string $grp): string
    {
        $d = Carbon::parse($grp);

        return match ($range) {
            'weekly'  => $d->translatedFormat('d M Y'),
            'monthly' => $d->translatedFormat('M Y'),
            default   => $d->translatedFormat('d M Y'),
        };
    }
}