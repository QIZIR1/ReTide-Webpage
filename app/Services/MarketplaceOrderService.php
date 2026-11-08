<?php

namespace App\Services;

use App\Models\MarketplaceProduct;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceOrderService
{
    /**
     * Build the item snapshot stored on a Payment from the session cart.
     */
    public static function itemsFromCart(array $cart): array
    {
        $items = [];

        foreach ($cart as $productId => $details) {
            $items[] = [
                'product_id' => (int) $productId,
                'name' => $details['name'],
                'price' => (float) $details['price'],
                'quantity' => (int) $details['quantity'],
            ];
        }

        return $items;
    }

    /**
     * Apply a paid order: decrease product stock and increase sold_count.
     *
     * Runs at most once per payment (guarded by fulfilled_at inside a locked
     * transaction), so repeated webhooks or page refreshes do not double count.
     * Returns true when the order was fulfilled by this call.
     */
    public static function fulfill(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->getKey())->lockForUpdate()->first();

            if ($locked === null || $locked->fulfilled_at !== null || empty($locked->items)) {
                return false;
            }

            foreach ($locked->items as $item) {
                $product = MarketplaceProduct::whereKey($item['product_id'])->lockForUpdate()->first();

                if ($product === null) {
                    Log::warning('Marketplace fulfill: product not found', [
                        'order_id' => $locked->order_id,
                        'product_id' => $item['product_id'],
                    ]);

                    continue;
                }

                $quantity = (int) $item['quantity'];

                if ($quantity > $product->stock) {
                    // Already paid, so the sale is recorded; stock cannot go below zero.
                    Log::warning('Marketplace fulfill: oversold product', [
                        'order_id' => $locked->order_id,
                        'product_id' => $product->id,
                        'stock' => $product->stock,
                        'quantity' => $quantity,
                    ]);
                }

                $product->stock = max(0, $product->stock - $quantity);
                $product->sold_count += $quantity;
                $product->save();
            }

            $locked->fulfilled_at = now();
            $locked->save();

            $payment->setRawAttributes($locked->getAttributes(), true);

            return true;
        });
    }
}
