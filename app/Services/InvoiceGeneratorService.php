<?php 

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceGeneratorService
{
    /**
     * Generate invoice for an order
     */
    public static function generate(Order $order, Payment $payment, ?Invoice $existingInvoice = null): Invoice
    {
        // Generate or reuse invoice number
        $invoiceNumber = $existingInvoice?->invoice_number ?: Invoice::generateInvoiceNumber();
        $issuedAt = $payment->paid_at ?? $payment->updated_at ?? $payment->created_at ?? now();

        $viewData = [
            'order' => $order,
            'payment' => $payment,
            'invoiceNumber' => $invoiceNumber,
            'issuedDate' => $issuedAt->format('d F Y'),
            'issuedTime' => $issuedAt->format('H:i'),
        ];

        if (class_exists(Pdf::class)) {
            $document = Pdf::loadView('invoices.pdf', $viewData);
            $document->setPaper('a4', 'portrait');
            $contents = $document->output();
        } else {
            $contents = self::buildFallbackPdf($order, $payment, $invoiceNumber, $viewData['issuedDate'], $viewData['issuedTime']);
        }

        // Generate filename
        $filename = $invoiceNumber . '.pdf';
        $path = 'invoices/' . now()->format('Y/m') . '/' . $filename;
        
        // Ensure directory exists
        Storage::makeDirectory(dirname($path));

        if ($existingInvoice?->pdf_path && Storage::exists($existingInvoice->pdf_path)) {
            Storage::delete($existingInvoice->pdf_path);
        }
        
        // Save document to storage
        Storage::put($path, $contents);

        $payload = [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'invoice_number' => $invoiceNumber,
            'pdf_path' => $path,
            'issued_at' => $issuedAt,
        ];

        if ($existingInvoice) {
            $existingInvoice->update($payload);

            return $existingInvoice->fresh();
        }

        return Invoice::create($payload);
    }

    private static function buildFallbackPdf(Order $order, Payment $payment, string $invoiceNumber, string $issuedDate, string $issuedTime): string
    {
        $content = '';
        $items = $order->items()->with('product')->get();

        $gold = [0.89, 0.64, 0.00];
        $goldSoft = [1.00, 0.96, 0.84];
        $goldLine = [0.96, 0.84, 0.49];
        $textDark = [0.15, 0.15, 0.18];
        $textMute = [0.42, 0.47, 0.52];

        $content .= self::pdfFillColor($gold[0], $gold[1], $gold[2]);
        $content .= self::pdfRect(36, 36, 523, 92, true);
        $content .= self::pdfTextAt('INVOICE', 50, 60, 24, 'F2', 1, 1, 1);
        $content .= self::pdfTextAt('Durian Lovers', 50, 92, 18, 'F2', 1, 0.99, 0.95);
        $content .= self::pdfTextAt('Fresh Durian Store & Delivery', 50, 114, 10, 'F1', 1, 0.98, 0.92);
        $content .= self::pdfTextAt('Jl. Kedamaian Selatan Blok F', 318, 62, 10, 'F1', 1, 0.98, 0.92);
        $content .= self::pdfTextAt('WhatsApp: 085179920483', 318, 80, 10, 'F1', 1, 0.98, 0.92);
        $content .= self::pdfTextAt('Email: admin@durianlovers.com', 318, 98, 10, 'F1', 1, 0.98, 0.92);

        $content .= self::pdfFillColor($goldSoft[0], $goldSoft[1], $goldSoft[2]);
        $content .= self::pdfStrokeColor($goldLine[0], $goldLine[1], $goldLine[2]);
        $content .= self::pdfRect(36, 142, 252, 132, true, true);
        $content .= self::pdfRect(307, 142, 252, 132, true, true);

        $content .= self::pdfTextAt('Informasi Invoice', 50, 160, 13, 'F2', 0.40, 0.26, 0.00);
        $content .= self::pdfTextAt('Data Pelanggan', 321, 160, 13, 'F2', 0.40, 0.26, 0.00);

        $leftInfo = [
            ['Nomor Invoice', self::truncatePdfText($invoiceNumber, 22)],
            ['Tanggal', $issuedDate],
            ['Waktu', $issuedTime . ' WITA'],
            ['Nomor Order', self::truncatePdfText($order->order_number, 18)],
        ];

        $y = 186;
        foreach ($leftInfo as [$label, $value]) {
            $content .= self::pdfTextAt($label, 50, $y, 10, 'F2', $textMute[0], $textMute[1], $textMute[2]);
            $content .= self::pdfTextAt(': ' . $value, 132, $y, 10, 'F1', $textDark[0], $textDark[1], $textDark[2]);
            $y += 20;
        }

        $customerLines = array_merge(
            [$order->customer_display_name, $order->customer_display_email],
            self::wrapPdfLines('No. Telp: ' . $order->customer_display_phone, 30)
        );
        $y = 186;
        foreach ($customerLines as $line) {
            $content .= self::pdfTextAt($line, 321, $y, 10, 'F1', $textDark[0], $textDark[1], $textDark[2]);
            $y += 17;
            if ($y > 256) {
                break;
            }
        }

        $tableTop = 292;
        $tableLeft = 36;
        $tableWidth = 523;
        $headerHeight = 30;
        $rowHeight = 26;
        $col1 = 235;
        $col2 = 60;
        $col3 = 105;
        $col4 = 123;

        $content .= self::pdfFillColor($gold[0], $gold[1], $gold[2]);
        $content .= self::pdfRect($tableLeft, $tableTop, $tableWidth, $headerHeight, true);
        $content .= self::pdfTextCenteredInBox('Produk', $tableLeft, $tableTop, $col1, $headerHeight, 10, 'F2', 1, 1, 1);
        $content .= self::pdfTextCenteredInBox('Qty', $tableLeft + $col1, $tableTop, $col2, $headerHeight, 10, 'F2', 1, 1, 1);
        $content .= self::pdfTextCenteredInBox('Harga Satuan', $tableLeft + $col1 + $col2, $tableTop, $col3, $headerHeight, 10, 'F2', 1, 1, 1);
        $content .= self::pdfTextCenteredInBox('Total', $tableLeft + $col1 + $col2 + $col3, $tableTop, $col4, $headerHeight, 10, 'F2', 1, 1, 1);

        $rowTop = $tableTop + $headerHeight;
        $tableBottom = $rowTop;
        foreach ($items as $index => $item) {
            $fill = $index % 2 === 0 ? [1, 1, 1] : [1.00, 0.98, 0.92];
            $content .= self::pdfFillColor($fill[0], $fill[1], $fill[2]);
            $content .= self::pdfStrokeColor(0.93, 0.88, 0.72);
            $content .= self::pdfRect($tableLeft, $rowTop, $tableWidth, $rowHeight, true, true);

            $productName = self::truncatePdfText((string) ($item->product->name ?? 'Produk'), 34);
            $content .= self::pdfTextCenteredInBox($productName, $tableLeft + 6, $rowTop, $col1 - 12, $rowHeight, 9, 'F1', $textDark[0], $textDark[1], $textDark[2]);
            $content .= self::pdfTextCenteredInBox((string) $item->quantity, $tableLeft + $col1, $rowTop, $col2, $rowHeight, 10, 'F1', $textDark[0], $textDark[1], $textDark[2]);
            $content .= self::pdfTextCenteredInBox('Rp ' . number_format($item->price, 0, ',', '.'), $tableLeft + $col1 + $col2 + 4, $rowTop, $col3 - 8, $rowHeight, 9, 'F1', $textDark[0], $textDark[1], $textDark[2]);
            $content .= self::pdfTextCenteredInBox('Rp ' . number_format($item->total, 0, ',', '.'), $tableLeft + $col1 + $col2 + $col3 + 4, $rowTop, $col4 - 8, $rowHeight, 9, 'F2', $textDark[0], $textDark[1], $textDark[2]);

            $rowTop += $rowHeight;
            $tableBottom = $rowTop;
            if ($rowTop > 500) {
                break;
            }
        }

        $content .= self::pdfStrokeColor(0.90, 0.78, 0.42);
        $content .= self::pdfLine($tableLeft + $col1, $tableTop, $tableLeft + $col1, $tableBottom);
        $content .= self::pdfLine($tableLeft + $col1 + $col2, $tableTop, $tableLeft + $col1 + $col2, $tableBottom);
        $content .= self::pdfLine($tableLeft + $col1 + $col2 + $col3, $tableTop, $tableLeft + $col1 + $col2 + $col3, $tableBottom);

        $totalsTop = max(520, $rowTop + 20);
        $totalsLeft = 305;
        $totalsWidth = 254;

        $content .= self::pdfFillColor($goldSoft[0], $goldSoft[1], $goldSoft[2]);
        $content .= self::pdfStrokeColor($goldLine[0], $goldLine[1], $goldLine[2]);
        $content .= self::pdfRect($totalsLeft, $totalsTop, $totalsWidth, 94, true, true);
        $content .= self::pdfTextAt('Ringkasan Total', $totalsLeft + 14, $totalsTop + 18, 12, 'F2', 0.40, 0.26, 0.00);
        $content .= self::pdfTextAt('Subtotal', $totalsLeft + 14, $totalsTop + 44, 10, 'F1', $textMute[0], $textMute[1], $textMute[2]);
        $content .= self::pdfTextRight('Rp ' . number_format($order->subtotal, 0, ',', '.'), $totalsLeft + $totalsWidth - 14, $totalsTop + 44, 10, 'F2', $textDark[0], $textDark[1], $textDark[2]);
        $content .= self::pdfTextAt('Ongkos Kirim', $totalsLeft + 14, $totalsTop + 66, 10, 'F1', $textMute[0], $textMute[1], $textMute[2]);
        $content .= self::pdfTextRight('Rp ' . number_format($order->shipping_cost, 0, ',', '.'), $totalsLeft + $totalsWidth - 14, $totalsTop + 66, 10, 'F2', $textDark[0], $textDark[1], $textDark[2]);

        $content .= self::pdfFillColor($gold[0], $gold[1], $gold[2]);
        $content .= self::pdfRect($totalsLeft, $totalsTop + 94, $totalsWidth, 32, true);
        $content .= self::pdfTextAt('TOTAL', $totalsLeft + 14, $totalsTop + 114, 11, 'F2', 1, 1, 1);
        $content .= self::pdfTextRight('Rp ' . number_format($order->total, 0, ',', '.'), $totalsLeft + $totalsWidth - 14, $totalsTop + 114, 11, 'F2', 1, 1, 1);

        $payTop = $totalsTop + 144;
        $content .= self::pdfFillColor(1.00, 0.98, 0.92);
        $content .= self::pdfStrokeColor($goldLine[0], $goldLine[1], $goldLine[2]);
        $content .= self::pdfRect(36, $payTop, 523, 88, true, true);
        $content .= self::pdfTextAt('Informasi Pembayaran', 50, $payTop + 18, 12, 'F2', 0.40, 0.26, 0.00);
        $content .= self::pdfTextAt('Metode: ' . ($order->payment_method ?? '-'), 50, $payTop + 42, 10, 'F1', $textDark[0], $textDark[1], $textDark[2]);
        $content .= self::pdfTextAt('Via: ' . ($payment->payment_method ?? $order->payment_method ?? '-'), 50, $payTop + 62, 10, 'F1', $textDark[0], $textDark[1], $textDark[2]);
        $content .= self::pdfFillColor($gold[0], $gold[1], $gold[2]);
        $content .= self::pdfRect(400, $payTop + 28, 120, 28, true);
        $content .= self::pdfTextCenteredInBox('LUNAS', 400, $payTop + 28, 120, 28, 11, 'F2', 1, 1, 1);

        $footerTop = min($payTop + 110, 760);
        $content .= self::pdfTextAt('Terima kasih telah berbelanja di Durian Lovers!', 145, $footerTop, 13, 'F2', 0.55, 0.36, 0.00);

        return self::assemblePdf($content);
    }

    private static function normalizePdfText(string $text): string
    {
        $normalized = preg_replace('/[^\P{C}\t]/u', '', $text) ?? $text;
        $converted = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $normalized);

        return $converted !== false ? $converted : $normalized;
    }

    private static function escapePdfText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }

    private static function assemblePdf(string $content): string
    {
        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >> endobj';
        $objects[] = '4 0 obj << /Length ' . strlen($content) . " >> stream\n" . $content . "\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';
        $objects[] = '6 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    private static function pdfTextAt(
        string $text,
        float $x,
        float $top,
        int $fontSize = 12,
        string $font = 'F1',
        float $r = 0.2,
        float $g = 0.2,
        float $b = 0.2
    ): string {
        $y = 842 - $top - $fontSize;
        $safeText = self::escapePdfText(self::normalizePdfText($text));

        return "BT\n/{$font} {$fontSize} Tf\n{$r} {$g} {$b} rg\n1 0 0 1 {$x} {$y} Tm\n({$safeText}) Tj\nET\n";
    }

    private static function pdfTextRight(
        string $text,
        float $rightX,
        float $top,
        int $fontSize = 12,
        string $font = 'F1',
        float $r = 0.2,
        float $g = 0.2,
        float $b = 0.2
    ): string {
        $width = self::estimatePdfTextWidth($text, $fontSize);
        $x = max(36, $rightX - $width);

        return self::pdfTextAt($text, $x, $top, $fontSize, $font, $r, $g, $b);
    }

    private static function pdfTextCenteredInBox(
        string $text,
        float $left,
        float $top,
        float $width,
        float $height,
        int $fontSize = 12,
        string $font = 'F1',
        float $r = 0.2,
        float $g = 0.2,
        float $b = 0.2
    ): string {
        $textWidth = self::estimatePdfTextWidth($text, $fontSize);
        $x = $left + max(0, ($width - $textWidth) / 2);
        $verticalOffset = max(0, ($height - $fontSize) / 2) - 1;
        $y = $top + $verticalOffset;

        return self::pdfTextAt($text, $x, $y, $fontSize, $font, $r, $g, $b);
    }

    private static function pdfRect(
        float $x,
        float $top,
        float $width,
        float $height,
        bool $fill = false,
        bool $stroke = false
    ): string {
        $y = 842 - $top - $height;
        $operator = $fill && $stroke ? 'B' : ($fill ? 'f' : 'S');

        return "{$x} {$y} {$width} {$height} re {$operator}\n";
    }

    private static function pdfLine(float $x1, float $top1, float $x2, float $top2): string
    {
        $y1 = 842 - $top1;
        $y2 = 842 - $top2;

        return "{$x1} {$y1} m {$x2} {$y2} l S\n";
    }

    private static function pdfFillColor(float $r, float $g, float $b): string
    {
        return "{$r} {$g} {$b} rg\n";
    }

    private static function pdfStrokeColor(float $r, float $g, float $b): string
    {
        return "{$r} {$g} {$b} RG\n";
    }

    private static function wrapPdfLines(string $text, int $width): array
    {
        $segments = preg_split("/\r\n|\r|\n/", wordwrap($text, $width, "\n", true)) ?: [''];

        return array_values(array_filter($segments, static fn ($line) => $line !== ''));
    }

    private static function truncatePdfText(string $text, int $limit): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, max(0, $limit - 3)) . '...';
    }

    private static function estimatePdfTextWidth(string $text, int $fontSize): float
    {
        $normalized = self::normalizePdfText($text);
        $length = strlen($normalized);
        $wideChars = preg_match_all('/[A-Z0-9Rp.,]/', $normalized, $matches);

        $spaceChars = substr_count($normalized, ' ');

        return max(8, ($length * $fontSize * 0.47) + ($wideChars * $fontSize * 0.08) - ($spaceChars * $fontSize * 0.05));
    }
}
