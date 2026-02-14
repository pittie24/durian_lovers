<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class MidtransService
{
    public function createSnapToken(Order $order): string
    {
        // Placeholder implementation. Replace with real Midtrans API integration.
        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey) {
            return 'dummy-' . Str::uuid()->toString();
        }

        return 'dummy-' . Str::uuid()->toString();
    }
}
