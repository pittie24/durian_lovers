<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $range = $request->get('range', 'daily');

        $totalRevenue = Order::sum('total');
        $totalOrders  = Order::count();
        $bestProducts = Product::orderByDesc('sold_count')->take(5)->get();

        $groupFormat = $this->groupFormat($range);

        $daily = Order::query()
            ->selectRaw("$groupFormat as day")
            ->selectRaw("SUM(total) as total")
            ->selectRaw("COUNT(*) as orders_count")
            ->groupBy('day')
            ->orderBy('day', 'desc')
            ->take($this->takeLimit($range))
            ->get()
            ->reverse();

        return view('admin.reports.index', compact(
            'range',
            'totalRevenue',
            'totalOrders',
            'bestProducts',
            'daily'
        ));
    }

    public function exportCsv(Request $request)
    {
        $range = $request->get('range', 'daily');
        $nowId = now()->timezone('Asia/Jakarta');

        $rangeLabel = match ($range) {
            'weekly'  => 'Mingguan',
            'monthly' => 'Bulanan',
            default   => 'Harian',
        };

        // ===== SUMMARY =====
        $totalRevenue = (float) Order::sum('total');
        $totalOrders  = (int) Order::count();
        $avgOrder     = ($totalOrders > 0) ? ($totalRevenue / $totalOrders) : 0;

        // Total produk terjual (sementara: total sold_count dari 5 produk terlaris)
        $bestProducts = Product::orderByDesc('sold_count')->take(5)->get();
        $totalSoldVal = 0;
        foreach ($bestProducts as $bp) {
            $totalSoldVal += (int) ($bp->sold_count ?? 0);
        }

        // ===== DETAIL TABLE =====
        $groupFormat = $this->groupFormat($range);

        $rows = Order::query()
            ->selectRaw("$groupFormat as periode")
            ->selectRaw("COUNT(*) as jumlah_pesanan")
            ->selectRaw("SUM(total) as pendapatan")
            ->groupBy('periode')
            ->orderBy('periode', 'asc')
            ->get();

        $filename = "laporan-{$range}-" . $nowId->format('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($rangeLabel, $nowId, $totalOrders, $totalRevenue, $avgOrder, $totalSoldVal, $rows) {
            $out = fopen('php://output', 'w');

            // BOM supaya Excel/Office kebaca (UTF-8)
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ===== HEADER =====
            fputcsv($out, ['Laporan Penjualan Durian Lovers']);
            fputcsv($out, ['Periode', $rangeLabel]);
            fputcsv($out, ['Tanggal Export', $nowId->format('d/m/Y H:i')]);
            fputcsv($out, []);

            // ===== SUMMARY =====
            fputcsv($out, ['Total Pesanan', $totalOrders]);
            fputcsv($out, ['Total Pendapatan', $totalRevenue]);
            fputcsv($out, ['Rata-rata Pesanan', $avgOrder]);
            fputcsv($out, ['Total Produk Terjual', $totalSoldVal]);
            fputcsv($out, []);

            // ===== DETAIL TABLE =====
            fputcsv($out, ['Tanggal', 'Jumlah Pesanan', 'Pendapatan (Ribu)']);

            foreach ($rows as $r) {
                $pendapatan = (float) $r->pendapatan;
                $pendapatanRibu = $pendapatan / 1000;

                fputcsv($out, [
                    $r->periode,
                    (int) $r->jumlah_pesanan,
                    $pendapatanRibu,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function groupFormat(string $range): string
    {
        return match ($range) {
            'weekly'  => "YEARWEEK(created_at, 1)",
            'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
            default   => "DATE(created_at)",
        };
    }

    private function takeLimit(string $range): int
    {
        return match ($range) {
            'weekly'  => 8,
            'monthly' => 12,
            default   => 7,
        };
    }
}