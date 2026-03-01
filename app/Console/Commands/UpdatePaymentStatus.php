<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use App\Services\InvoiceGeneratorService;
use Illuminate\Console\Command;

class UpdatePaymentStatus extends Command
{
    protected $signature = 'payment:update {order_id} {status=PAID}';
    protected $description = 'Update payment status manually for testing';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $status = strtoupper($this->argument('status'));
        
        $order = Order::find($orderId);
        
        if (!$order) {
            $this->error("Order #{$orderId} not found!");
            return 1;
        }
        
        $payment = $order->payment;
        
        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => 'midtrans',
                'status' => $status,
                'payment_method' => 'Transfer Bank',
            ]);
            $this->info("Payment record created for Order #{$orderId}");
        }
        
        $payment->update([
            'status' => $status,
            'payment_method' => $payment->payment_method ?? 'Transfer Bank',
        ]);
        
        // Opsi B: Langsung SELESAI setelah bayar
        $order->update([
            'status' => 'SELESAI',
        ]);
        
        $this->info("Payment status updated to: {$status}");
        
        // Generate invoice if paid
        if (in_array($status, ['PAID', 'SETTLED'])) {
            if (!$order->invoice) {
                try {
                    InvoiceGeneratorService::generate($order, $payment);
                    $this->info("Invoice generated successfully!");
                } catch (\Throwable $e) {
                    $this->error("Failed to generate invoice: " . $e->getMessage());
                }
            } else {
                $this->info("Invoice already exists.");
            }
        }
        
        return 0;
    }
}
