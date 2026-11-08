<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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

        // Tentukan status baru dari notifikasi (belum disimpan)
        $newStatus = $payment->status;

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                $newStatus = ($fraud == 'challenge') ? 'challenge' : 'success';
            }
        } elseif ($transaction == 'settlement') {
            $newStatus = 'success';
        } elseif ($transaction == 'pending') {
            $newStatus = 'pending';
        } elseif ($transaction == 'deny') {
            $newStatus = 'denied';
        } elseif ($transaction == 'expire') {
            $newStatus = 'expired';
        } elseif ($transaction == 'cancel') {
            $newStatus = 'cancelled';
        }

        // Transaksi + kunci baris: aman dari notifikasi yang datang bersamaan
        return DB::transaction(function () use ($payment, $newStatus, $notif, $type, $request) {
            $payment = Payment::whereKey($payment->getKey())->lockForUpdate()->first();

            // Guard 1 (idempotency): notifikasi yang sama persis (status & transaction_id sama) diabaikan
            if ($payment->status === $newStatus && $payment->transaction_id === $notif->transaction_id) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'already processed',
                ], 200);
            }

            // Guard 2 (status final): 'success' tidak boleh berubah lagi
            if ($payment->status === 'success') {
                Log::warning('Midtrans Webhook: ignored change from final status', [
                    'order_id' => $payment->order_id,
                    'to' => $newStatus,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'ignored: final status',
                ], 200);
            }

            $payment->status = $newStatus;
            $payment->transaction_id = $notif->transaction_id;
            $payment->payment_type = $type;
            $payment->payload = $request->all();
            $payment->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification handled',
            ]);
        });
    }
}