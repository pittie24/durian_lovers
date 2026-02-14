<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $steps = [
            'PESANAN_DITERIMA' => 1,
            'SEDANG_DIPROSES' => 2,
            'SIAP_DIAMBIL_DIKIRIM' => 3,
            'SELESAI' => 4,
        ];

        $currentStep = $steps[$order->status] ?? 1;

        return view('customer.tracking.show', [
            'order' => $order->load('items.product'),
            'currentStep' => $currentStep,
        ]);
    }
}
