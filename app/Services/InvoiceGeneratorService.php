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
    public static function generate(Order $order, Payment $payment): Invoice
    {
        // Generate invoice number
        $invoiceNumber = Invoice::generateInvoiceNumber();
        
        // Generate PDF content
        $pdf = Pdf::loadView('invoices.pdf', [
            'order' => $order,
            'payment' => $payment,
            'invoiceNumber' => $invoiceNumber,
            'issuedDate' => now()->format('d F Y'),
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        
        // Generate filename
        $filename = $invoiceNumber . '.pdf';
        $path = 'invoices/' . now()->format('Y/m') . '/' . $filename;
        
        // Ensure directory exists
        Storage::makeDirectory(dirname($path));
        
        // Save PDF to storage
        Storage::put($path, $pdf->output());
        
        // Create invoice record
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'invoice_number' => $invoiceNumber,
            'pdf_path' => $path,
            'issued_at' => now(),
        ]);
        
        return $invoice;
    }
}
