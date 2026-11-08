<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\MarketplaceOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class PaymentWebhookController extends Controller
{
    public function handleNotification(Request $request)
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        try {
            $notif = app(Notification::class);
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Error: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid notification payload',
            ], 400);
        }

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $orderId = $notif->order_id;
        $fraud = $notif->fraud_status;

        // Verify Midtrans Signature
        $statusCode = $notif->status_code;
        $grossAmount = $notif->gross_amount;
        $signatureKey = $notif->signature_key;
        $serverKey = config('midtrans.server_key');

        $computedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $formattedGross = is_numeric($grossAmount) && strpos((string) $grossAmount, '.') === false
            ? number_format((float) $grossAmount, 2, '.', '')
            : $grossAmount;
        $computedSignatureFormatted = hash('sha512', $orderId.$statusCode.$formattedGross.$serverKey);

        if ($signatureKey !== $computedSignature && $signatureKey !== $computedSignatureFormatted) {
            Log::warning('Midtrans Webhook: Signature verification failed for Order ID: '.$orderId);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature key',
            ], 403);
        }

        $payment = Payment::where('order_id', $orderId)->first();

        if (! $payment) {
            Log::warning('Payment record not found for Order ID: '.$orderId);

            return response()->json([
                'status' => 'error',
                'message' => 'Payment record not found',
            ], 404);
        }

        // Update payment record
        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $payment->status = 'challenge';
                } else {
                    $payment->status = 'success';
                }
            }
        } elseif ($transaction == 'settlement') {
            $payment->status = 'success';
        } elseif ($transaction == 'pending') {
            $payment->status = 'pending';
        } elseif ($transaction == 'deny') {
            $payment->status = 'denied';
        } elseif ($transaction == 'expire') {
            $payment->status = 'expired';
        } elseif ($transaction == 'cancel') {
            $payment->status = 'cancelled';
        }

        $payment->transaction_id = $notif->transaction_id;
        $payment->payment_type = $type;
        $payment->payload = $request->all();
        $payment->save();

        // Paid marketplace order: decrease stock & increase sold count (runs once)
        if ($payment->status === 'success' && ! empty($payment->items)) {
            MarketplaceOrderService::fulfill($payment);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification handled',
        ]);
    }
}
