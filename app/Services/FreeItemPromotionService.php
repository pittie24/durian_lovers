<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class FreeItemPromotionService
{
    public const THRESHOLD = 300000;
    public const FREE_PRODUCT_NAME = 'Pancake Durian Mini';
    public const FREE_QUANTITY = 1;

    public function evaluate(array $cart): array
    {
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += ((int) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        }

        $freeProduct = Product::query()
            ->where('name', self::FREE_PRODUCT_NAME)
            ->first();

        $qualifies = $subtotal >= self::THRESHOLD;
        $isAvailable = $freeProduct && $freeProduct->stock >= self::FREE_QUANTITY;
        $isAwarded = $qualifies && $isAvailable;

        return [
            'threshold' => self::THRESHOLD,
            'subtotal' => $subtotal,
            'remaining' => max(0, self::THRESHOLD - $subtotal),
            'qualifies' => $qualifies,
            'is_available' => $isAvailable,
            'is_awarded' => $isAwarded,
            'free_product' => $freeProduct,
            'free_item_name' => $freeProduct?->name ?? self::FREE_PRODUCT_NAME,
            'free_item' => $isAwarded ? [
                'id' => $freeProduct->id,
                'name' => $freeProduct->name,
                'price' => 0,
                'quantity' => self::FREE_QUANTITY,
                'image_url' => $freeProduct->image_url,
                'weight' => $freeProduct->weight,
                'is_free_item' => true,
            ] : null,
        ];
    }

    public function attachToOrder(Order $order, array $promotion): void
    {
        if (!($promotion['is_awarded'] ?? false) || empty($promotion['free_product'])) {
            return;
        }

        /** @var Product $freeProduct */
        $freeProduct = $promotion['free_product'];

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $freeProduct->id,
            'quantity' => self::FREE_QUANTITY,
            'price' => 0,
            'total' => 0,
        ]);

        $freeProduct->decrement('stock', self::FREE_QUANTITY);
    }
}
