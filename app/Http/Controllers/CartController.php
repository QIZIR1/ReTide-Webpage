<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceProduct;
use App\Models\Payment;
use App\Services\MarketplaceOrderService;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class CartController extends Controller
{
    /**
     * Add a product to the cart using the session.
     */
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $product = MarketplaceProduct::findOrFail($productId);

        // Get existing cart from session or initialize empty array
        $cart = session()->get('cart', []);

        $newQuantity = ($cart[$productId]['quantity'] ?? 0) + 1;

        // Prevent adding more items than the available stock
        if ($newQuantity > $product->stock) {
            return $this->stockExceededResponse($product);
        }

        // Check if product is already in cart
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            $cart[$productId] = [
                'name' => $product->name,
                'quantity' => 1,
                'price' => $product->price,
                'image' => $product->image_path,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => $product->name.' added to cart!',
            'quantity' => $newQuantity,
            'stock' => $product->stock,
        ]);
    }

    /**
     * Change the quantity of a product already in the cart (used by the +/- buttons).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session()->get('cart', []);
        $productId = (int) $validated['product_id'];
        // Form/AJAX input arrives as a string; cast so JSON returns a number
        $quantity = (int) $validated['quantity'];

        if (! isset($cart[$productId])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is not in the cart.',
            ], 404);
        }

        $product = MarketplaceProduct::findOrFail($productId);

        if ($quantity > $product->stock) {
            return $this->stockExceededResponse($product);
        }

        $cart[$productId]['quantity'] = $quantity;
        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated.',
            'quantity' => $quantity,
            'stock' => $product->stock,
        ]);
    }

    /**
     * Remove a product from the cart.
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $cart = session()->get('cart', []);
        unset($cart[(int) $validated['product_id']]);

        $this->saveCart($cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from cart.',
        ]);
    }

    /**
     * Remove every product from the cart.
     */
    public function clear()
    {
        $this->saveCart([]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared.',
        ]);
    }

    public function sync(Request $request)
    {
        $request->validate([
            'cart' => ['array'],
            'cart.*.id' => ['required', 'integer'],
            'cart.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $frontendCart = $request->input('cart', []);
        $cart = [];

        foreach ($frontendCart as $item) {
            $product = MarketplaceProduct::findOrFail($item['id']);

            if ((int) $item['quantity'] > $product->stock) {
                return $this->stockExceededResponse($product);
            }

            $cart[$item['id']] = [
                'name' => $product->name,
                'quantity' => (int) $item['quantity'],
                'price' => $product->price,
                'image' => $product->image_path,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart synced successfully!',
        ]);
    }

    /**
     * Apply a promo coupon / discount voucher to the current cart.
     */
    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:50'],
        ]);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return $this->couponResponse($request, 'error', 'Cart is empty!', 422);
        }

        $code = strtoupper(trim($validated['coupon_code']));
        $subtotal = $this->calculateSubtotal($cart);
        $error = $this->validateCoupon($code, $subtotal);

        if ($error !== null) {
            session()->forget('coupon');

            return $this->couponResponse($request, 'error', $error, 422);
        }

        session()->put('coupon', $code);

        return $this->couponResponse($request, 'success', "Coupon {$code} applied!");
    }

    /**
     * Remove the applied coupon from the cart.
     */
    public function removeCoupon(Request $request)
    {
        session()->forget('coupon');

        return $this->couponResponse($request, 'success', 'Coupon removed.');
    }

    /**
     * Show the checkout page and prepare a Midtrans Snap token.
     */
    public function checkout()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('marketplace.index')
                ->with('error', 'Cart is empty!');
        }

        // Re-check stock: it may have changed since the items were added
        if ($stockError = $this->validateCartStock($cart)) {
            return redirect()
                ->route('marketplace.index')
                ->with('error', $stockError);
        }

        [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $coupon,
            'total' => $totalAmount,
            'item_details' => $itemDetails,
        ] = $this->buildOrderSummary($cart);

        // Configure Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = 'MKT-'.time().'-'.auth()->id();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('marketplace.success'),
                'unfinish' => route('marketplace.index'),
                'error' => route('marketplace.index'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            // Demo mode fallback when Midtrans key is not set
            $snapToken = 'DEMO-SNAP-'.strtoupper(uniqid());
        }

        $payment = Payment::create([
            'order_id' => $orderId,
            'payment_type' => 'marketplace',
            'status' => 'pending',
            'gross_amount' => $totalAmount,
            'user_id' => auth()->id(),
            'items' => MarketplaceOrderService::itemsFromCart($cart),
        ]);

        $payment->snap_token = $snapToken;
        $payment->save();

        return view('checkout', compact('cart', 'subtotal', 'discount', 'coupon', 'totalAmount', 'payment', 'snapToken'));
    }

    /**
     * Process checkout and redirect to Midtrans.
     */
    public function process(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()
                ->route('marketplace.index')
                ->with('error', 'Cart is empty!');
        }

        if ($stockError = $this->validateCartStock($cart)) {
            return response()->json([
                'status' => 'error',
                'message' => $stockError,
            ], 422);
        }

        [
            'total' => $totalAmount,
            'item_details' => $itemDetails,
        ] = $this->buildOrderSummary($cart);

        // Set Midtrans configuration
        // Set your Merchant Server Key
        Config::$serverKey = config('midtrans.server_key');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        Config::$isProduction = false;
        // Set sanitization on (default)
        Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        Config::$is3ds = true;

        // Config::$serverKey = config("midtrans.server_key");
        // Config::$clientKey = config("midtrans.client_key");
        // Config::$isProduction = config("midtrans.is_production");
        // Config::$isSanitized = config("midtrans.is_sanitized");
        // Config::$is3ds = config("midtrans.is_3ds");

        $orderId = 'MKT-'.time().'-'.auth()->id();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'item_details' => $itemDetails,
            'callbacks' => [
                'finish' => route('marketplace.success'),
                'unfinish' => route('marketplace.index'),
                'error' => route('marketplace.index'),
            ],
        ];

        $transaction = Payment::create([
            'order_id' => $orderId,
            'payment_type' => 'marketplace',
            'status' => 'pending',
            'gross_amount' => $totalAmount,
            'user_id' => auth()->id(),
            'items' => MarketplaceOrderService::itemsFromCart($cart),
            // "payload" => [
            //     "redirect_url" => $snapResponse->redirect_url,
            //     "token" => $snapToken,
            //     "created_at" => now()->toDateTimeString(),
            // ],
        ]);

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            // Demo mode fallback when Midtrans key is not set
            $snapToken = 'DEMO-SNAP-'.strtoupper(uniqid());
        }
        $transaction->snap_token = $snapToken;
        $transaction->save();

        return response()->json([
            'status' => 'success',
            'snap_token' => $snapToken,
        ]);

        // try {
        //     $snapResponse = \Midtrans\Snap::createTransaction($params);
        //     $redirectUrl = $snapResponse->redirect_url;

        //     // Create Payment record ONLY if Snap transaction succeeded
        //     Payment::create([
        //         "order_id" => $orderId,
        //         "payment_type" => "marketplace",
        //         "status" => "pending",
        //         "gross_amount" => $totalAmount,
        //         "user_id" => auth()->id(),
        //         "payload" => [
        //             "redirect_url" => $snapResponse->redirect_url,
        //             "token" => $snapResponse->token,
        //             "created_at" => now()->toDateTimeString(),
        //         ],
        //     ]);

        //     // Clear the cart session after creating transaction
        //     session()->forget("cart");

        //     return redirect()->away($redirectUrl);
        // } catch (\Exception $e) {
        //     \Log::error(
        //         "Midtrans Snap Error (Marketplace): " . $e->getMessage(),
        //     );
        //     return back()->with(
        //         "error",
        //         "Failed to create payment: " . $e->getMessage(),
        //     );
        // }
    }

    /**
     * Persist the cart; an empty cart also drops any applied coupon.
     */
    private function saveCart(array $cart): void
    {
        if (empty($cart)) {
            session()->forget(['cart', 'coupon']);

            return;
        }

        session()->put('cart', $cart);
    }

    /**
     * JSON response used when a requested quantity exceeds the available stock.
     */
    private function stockExceededResponse(MarketplaceProduct $product)
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->stockErrorMessage($product),
            'stock' => $product->stock,
        ], 422);
    }

    private function stockErrorMessage(MarketplaceProduct $product): string
    {
        return $product->stock > 0
            ? "Only {$product->stock} item(s) of {$product->name} left in stock."
            : "{$product->name} is out of stock.";
    }

    /**
     * Verify every cart line against the current stock in the database.
     * Returns an error message, or null when the whole cart is available.
     */
    private function validateCartStock(array $cart): ?string
    {
        $products = MarketplaceProduct::whereIn('id', array_keys($cart))->get()->keyBy('id');

        foreach ($cart as $id => $details) {
            $product = $products->get($id);

            if ($product === null) {
                return "{$details['name']} is no longer available.";
            }

            if ($details['quantity'] > $product->stock) {
                return $this->stockErrorMessage($product);
            }
        }

        return null;
    }

    /**
     * Calculate the cart subtotal before any discount.
     */
    private function calculateSubtotal(array $cart): float
    {
        $subtotal = 0;

        foreach ($cart as $details) {
            $subtotal += $details['price'] * $details['quantity'];
        }

        return $subtotal;
    }

    /**
     * Validate a coupon code against the cart subtotal.
     * Returns an error message, or null when the coupon is valid.
     */
    private function validateCoupon(string $code, float $subtotal): ?string
    {
        $coupon = config("coupons.{$code}");

        if (! is_array($coupon)) {
            return 'Invalid coupon code.';
        }

        if (! empty($coupon['expires_at']) && now()->startOfDay()->gt($coupon['expires_at'])) {
            return 'This coupon has expired.';
        }

        $minSubtotal = $coupon['min_subtotal'] ?? 0;
        if ($subtotal < $minSubtotal) {
            return 'Minimum purchase for this coupon is Rp '.number_format($minSubtotal, 0, ',', '.').'.';
        }

        return null;
    }

    /**
     * Calculate the discount amount (percent or fixed) for a coupon.
     * The discount never exceeds the subtotal.
     */
    private function calculateDiscount(string $code, float $subtotal): float
    {
        $coupon = config("coupons.{$code}");

        if ($coupon['type'] === 'percent') {
            $discount = $subtotal * ($coupon['value'] / 100);

            if (! empty($coupon['max_discount'])) {
                $discount = min($discount, $coupon['max_discount']);
            }
        } else {
            $discount = $coupon['value'];
        }

        return (float) min(round($discount), $subtotal);
    }

    /**
     * Build subtotal, discount, final total and Midtrans item details for the cart.
     * The coupon is re-validated server side so a stale session coupon is dropped.
     */
    private function buildOrderSummary(array $cart): array
    {
        $subtotal = $this->calculateSubtotal($cart);
        $itemDetails = [];

        foreach ($cart as $id => $details) {
            $itemDetails[] = [
                'id' => (string) $id,
                'price' => $details['price'],
                'quantity' => $details['quantity'],
                'name' => substr($details['name'], 0, 50),
            ];
        }

        $coupon = session()->get('coupon');
        $discount = 0;

        if ($coupon !== null && $this->validateCoupon($coupon, $subtotal) !== null) {
            session()->forget('coupon');
            $coupon = null;
        }

        if ($coupon !== null) {
            $discount = $this->calculateDiscount($coupon, $subtotal);

            // Midtrans requires item_details to sum up to gross_amount,
            // so the discount is sent as a negative line item.
            $itemDetails[] = [
                'id' => 'DISCOUNT-'.$coupon,
                'price' => -$discount,
                'quantity' => 1,
                'name' => 'Coupon '.$coupon,
            ];
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $coupon,
            'total' => $subtotal - $discount,
            'item_details' => $itemDetails,
        ];
    }

    /**
     * Respond to coupon actions as JSON (AJAX) or redirect back to checkout.
     */
    private function couponResponse(Request $request, string $status, string $message, int $code = 200)
    {
        if ($request->expectsJson()) {
            $cart = session()->get('cart', []);
            $summary = empty($cart) ? null : $this->buildOrderSummary($cart);

            return response()->json([
                'status' => $status,
                'message' => $message,
                'coupon' => $summary['coupon'] ?? null,
                'subtotal' => $summary['subtotal'] ?? 0,
                'discount' => $summary['discount'] ?? 0,
                'total' => $summary['total'] ?? 0,
            ], $code);
        }

        return redirect()->route('cart.checkout')->with($status, $message);
    }
}
