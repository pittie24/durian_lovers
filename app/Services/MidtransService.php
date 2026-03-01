<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $order->id,
                'gross_amount' => $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone ?? '',
            ],
            'item_details' => $this->getItemDetails($order),
            'callbacks' => [
                'finish' => url('/pembayaran/' . $order->id . '/return?order_id=' . $order->id),
            ],
        ];

        try {
            \Log::info('MIDTRANS order_id = ' . $params['transaction_details']['order_id']);
            $snapToken = Snap::getSnapToken($params);
            return $snapToken;
        } catch (\Exception $e) {
            \Log::error('Midtrans Snap Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getItemDetails(Order $order): array
    {
        $items = [];
        foreach ($order->orderItems as $item) {
            $items[] = [
                'id' => $item->product_id,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'name' => $item->product->name ?? 'Product',
            ];
        }
        return $items;
    }

    public function getTransactionStatus(string $orderId): array
    {
        try {
            return Transaction::status($orderId);
        } catch (\Exception $e) {
            \Log::error('Midtrans Transaction Status Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function approveTransaction(string $orderId): array
    {
        try {
            return Transaction::approve($orderId);
        } catch (\Exception $e) {
            \Log::error('Midtrans Transaction Approve Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
