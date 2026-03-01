<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Download invoice PDF
     */
    public function download(Order $order)
    {
        // Ensure only order owner can download
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
        
        // Ensure payment is PAID/SETTLED/CAPTURE
        $paymentStatus = strtolower($order->payment?->status ?? '');
        if (!in_array($paymentStatus, ['paid', 'settled', 'capture', 'settlement'])) {
            abort(403, 'Invoice not available yet');
        }
        
        // Always refresh invoice so downloaded file follows the latest styling.
        $invoice = InvoiceGeneratorService::generate($order, $order->payment, $order->invoice);
        
        if (!$invoice->pdf_path) {
            abort(404, 'Invoice file not found');
        }
        
        $filePath = storage_path('app/' . $invoice->pdf_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Invoice file not found');
        }

        $downloadName = $invoice->invoice_number . '.' . pathinfo($invoice->pdf_path, PATHINFO_EXTENSION);

        return response()->download($filePath, $downloadName);
    }
    
    /**
     * Preview invoice in browser
     */
    public function preview(Order $order)
    {
        // Ensure only order owner can preview
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
        
        // Ensure payment is PAID/SETTLED/CAPTURE
        $paymentStatus = strtolower($order->payment?->status ?? '');
        if (!in_array($paymentStatus, ['paid', 'settled', 'capture', 'settlement'])) {
            abort(403, 'Invoice not available yet');
        }
        
        // Always refresh invoice so preview follows the latest styling.
        $invoice = InvoiceGeneratorService::generate($order, $order->payment, $order->invoice);
        
        if (!$invoice->pdf_path) {
            abort(404, 'Invoice file not found');
        }
        
        $filePath = storage_path('app/' . $invoice->pdf_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Invoice file not found');
        }
        
        return response()->file($filePath);
    }
}
