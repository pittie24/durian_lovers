<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function create(OrderItem $orderItem)
    {
        if ($orderItem->order->user_id !== Auth::id() || $orderItem->order->status !== 'SELESAI') {
            abort(403);
        }

        $existing = Rating::where('order_item_id', $orderItem->id)->first();

        return view('customer.rating.create', [
            'orderItem' => $orderItem->load('product'),
            'existing' => $existing,
        ]);
    }

    public function store(Request $request, OrderItem $orderItem)
    {
        if ($orderItem->order->user_id !== Auth::id() || $orderItem->order->status !== 'SELESAI') {
            abort(403);
        }

        $data = $request->validate([
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $rating = Rating::updateOrCreate(
            ['order_item_id' => $orderItem->id],
            [
                'user_id' => Auth::id(),
                'product_id' => $orderItem->product_id,
                'stars' => $data['stars'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        $product = $orderItem->product;
        $avg = Rating::where('product_id', $product->id)->avg('stars');
        $count = Rating::where('product_id', $product->id)->count();
        $product->update([
            'rating_avg' => $avg,
            'rating_count' => $count,
        ]);

        return redirect('/riwayat')->with('success', 'Rating berhasil disimpan.');
    }
}
