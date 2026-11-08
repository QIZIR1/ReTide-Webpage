<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Midtrans\Notification;
use Tests\TestCase;

class DonationPaymentVerificationWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => 'test-server-key']);
    }

    private function fakeNotification(string $orderId, string $status): void
    {
        $statusCode = '200';
        $grossAmount = '100000.00';
        $serverKey = config('midtrans.server_key');

        $mock = \Mockery::mock(Notification::class);
        $mock->transaction_status = $status;
        $mock->payment_type = 'bank_transfer';
        $mock->order_id = $orderId;
        $mock->fraud_status = 'accept';
        $mock->transaction_id = 'tx-'.$orderId;
        $mock->status_code = $statusCode;
        $mock->gross_amount = $grossAmount;
        $mock->signature_key = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $this->app->instance(Notification::class, $mock);
    }

    private function makePayment(string $orderId, string $status): Payment
    {
        return Payment::create([
            'order_id' => $orderId,
            'payment_type' => 'donation',
            'status' => $status,
            'gross_amount' => 100000,
        ]);
    }

    public function test_duplicate_notification_is_ignored(): void
    {
        $payment = $this->makePayment('DON-DUP-01', 'pending');
        $this->fakeNotification('DON-DUP-01', 'settlement');

        $this->postJson(route('payment.notification'), [])
            ->assertOk()
            ->assertJson(['message' => 'Notification handled']);

        $updatedAt = $payment->fresh()->updated_at;

        $this->travel(10)->minutes();

        $this->postJson(route('payment.notification'), [])
            ->assertOk()
            ->assertJson(['message' => 'already processed']);

        $fresh = $payment->fresh();
        $this->assertSame('success', $fresh->status);
        $this->assertTrue($fresh->updated_at->equalTo($updatedAt));
    }

    public function test_success_payment_cannot_go_back_to_pending(): void
    {
        $this->makePayment('DON-FINAL-01', 'success');
        $this->fakeNotification('DON-FINAL-01', 'pending');

        $this->postJson(route('payment.notification'), [])
            ->assertOk()
            ->assertJson(['message' => 'ignored: final status']);

        $this->assertDatabaseHas('payments', [
            'order_id' => 'DON-FINAL-01',
            'status' => 'success',
        ]);
    }

    public function test_pending_payment_can_still_expire(): void
    {
        $this->makePayment('DON-EXP-01', 'pending');
        $this->fakeNotification('DON-EXP-01', 'expire');

        $this->postJson(route('payment.notification'), [])
            ->assertOk()
            ->assertJson(['message' => 'Notification handled']);

        $this->assertDatabaseHas('payments', [
            'order_id' => 'DON-EXP-01',
            'status' => 'expired',
        ]);
    }
}