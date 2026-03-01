<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Invoice;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        
        // Get or create invoice
        $invoice = $order->invoice;
        
        if (!$invoice) {
            // Generate invoice if not exists (for orders paid before this feature)
            $invoice = InvoiceGeneratorService::generate($order, $order->payment);
        }
        
        if (!$invoice->pdf_path) {
            abort(404, 'Invoice file not found');
        }
        
        $filePath = storage_path('app/' . $invoice->pdf_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'Invoice file not found');
        }
        
        return response()->download($filePath, $invoice->invoice_number . '.pdf');
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
        
        // Get or create invoice
        $invoice = $order->invoice;
        
        if (!$invoice) {
            // Generate invoice if not exists
            $invoice = InvoiceGeneratorService::generate($order, $order->payment);
        }
        
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
