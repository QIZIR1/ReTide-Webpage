<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\DonationUpdate;
use App\Models\Payment;
use App\Services\SecurityAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class DonationController extends Controller
{
    public function index()
    {
        // 1. Ambil donasi dari user yang login
        $donations = Donation::where('user_id', auth()->id())->latest()->get();

        // 2. Total donasi keseluruhan
        $totalDonations = Donation::sum('amount');

        // 3. Goal & Progress
        $donationGoal = config('donation.goal');
        $progressPercentage = min(($totalDonations / $donationGoal) * 100, 100);

        // 4. Ringkasan donasi milik user yang login
        $summary = [
            'total' => $donations->sum('amount'),
            'count' => $donations->count(),
            'last' => $donations->first(),
        ];

        return view('donation', compact('donations', 'totalDonations', 'donationGoal', 'progressPercentage', 'summary'));
    }

    public function updates(Donation $donation)
    {
        // Mitigasi IDOR: hanya pemilik donasi yang boleh melihat update
        abort_if((int) $donation->user_id !== (int) auth()->id(), 403);

        $updates = DonationUpdate::where('donation_id', $donation->id)->latest()->get();

        return response()->json($updates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1000',
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
            'message' => 'nullable|string|max:500',
        ]);

        $orderId = 'DON-'.time().'-'.auth()->id();

        $donation = Donation::create([
            'name' => $request->name ?: auth()->user()->name,
            'email' => $request->email ?: auth()->user()->email,
            'amount' => $request->amount,
            'message' => $request->message,
            'user_id' => auth()->id(),
            'order_id' => $orderId,
        ]);

        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $request->amount,
            ],
            'customer_details' => [
                'first_name' => $donation->name,
                'email' => $donation->email,
            ],
            'item_details' => [
                [
                    'id' => 'donation-'.$donation->id,
                    'price' => (int) $request->amount,
                    'quantity' => 1,
                    'name' => 'Donation for ReTide',
                ],
            ],
            'callbacks' => [
                'finish' => route('donation.success'),
                'unfinish' => route('donation.index'),
                'error' => route('donation.index'),
            ],
        ];

        try {
            $snapResponse = Snap::createTransaction($params);
            $redirectUrl = $snapResponse->redirect_url;

            // Create Payment record ONLY if Snap transaction succeeded
            Payment::create([
                'order_id' => $orderId,
                'payment_type' => 'donation',
                'status' => 'pending',
                'gross_amount' => $request->amount,
                'user_id' => auth()->id(),
                'payload' => [
                    'redirect_url' => $snapResponse->redirect_url,
                    'token' => $snapResponse->token,
                    'created_at' => now()->toDateTimeString(),
                ],
            ]);

            SecurityAuditService::log('DONATION_CREATE', [
                'donation_id' => $donation->id,
                'order_id' => $orderId,
                'amount' => $request->amount,
            ]);

            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error (Donation): '.$e->getMessage());

            return back()->with('error', 'Gagal membuat pembayaran donasi. Silakan coba lagi.');
        }
    }

    public function receipt(Donation $donation)
    {
        // Hanya pemilik donasi
        abort_if((int) $donation->user_id !== (int) auth()->id(), 403);

        // Hanya donasi yang sudah lunas
        abort_unless($donation->isPaid(), 404);

        if (! $donation->receipt_code) {
            $donation->update(['receipt_code' => Donation::generateReceiptCode()]);
        }

        return view('donation.receipt', compact('donation'));
    }

    public function success()
    {
        return view('donation_success');
    }

    public function adminIndex()
    {
        $donations = Donation::with('user')->latest()->get();

        return view('admin.donations.index', compact('donations'));
    }

    public function adminDestroy(Donation $donation)
    {
        $donationId = $donation->id;
        $donationAmount = $donation->amount;
        $donationDonor = $donation->name;

        $donation->delete();

        SecurityAuditService::log('ADMIN_DONATION_DELETE', [
            'deleted_donation_id' => $donationId,
            'amount' => $donationAmount,
            'donor_name' => $donationDonor,
        ]);

        return redirect()->route('admin.donations.index')
            ->with('success', 'Donasi berhasil dihapus');
    }
}