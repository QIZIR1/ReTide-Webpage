<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationPaymentVerificationReceiptTest extends TestCase
{
    use RefreshDatabase;

    private function makeDonation(User $user, string $orderId): Donation
    {
        return Donation::create([
            'name' => $user->name,
            'email' => $user->email,
            'amount' => 10000,
            'user_id' => $user->id,
            'order_id' => $orderId,
        ]);
    }

    private function makePayment(string $orderId, string $status): Payment
    {
        return Payment::create([
            'order_id' => $orderId,
            'payment_type' => 'donation',
            'status' => $status,
            'gross_amount' => 10000,
        ]);
    }

    public function test_receipt_not_available_for_unpaid_donation(): void
    {
        $user = User::factory()->create();
        $donation = $this->makeDonation($user, 'DON-RC-UNPAID');
        $this->makePayment('DON-RC-UNPAID', 'pending');

        $this->actingAs($user)
            ->get(route('donation.receipt', $donation))
            ->assertNotFound();
    }

    public function test_receipt_available_for_paid_donation(): void
    {
        $user = User::factory()->create();
        $donation = $this->makeDonation($user, 'DON-RC-PAID');
        $this->makePayment('DON-RC-PAID', 'success');

        $this->actingAs($user)
            ->get(route('donation.receipt', $donation))
            ->assertOk()
            ->assertSee('RCPT-');

        $this->assertNotNull($donation->fresh()->receipt_code);
    }

    public function test_receipt_code_is_stable_across_visits(): void
    {
        $user = User::factory()->create();
        $donation = $this->makeDonation($user, 'DON-RC-STABLE');
        $this->makePayment('DON-RC-STABLE', 'success');

        $this->actingAs($user)->get(route('donation.receipt', $donation))->assertOk();
        $first = $donation->fresh()->receipt_code;

        $this->actingAs($user)->get(route('donation.receipt', $donation))->assertOk();
        $this->assertSame($first, $donation->fresh()->receipt_code);
    }

    public function test_other_user_cannot_view_receipt(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $donation = $this->makeDonation($owner, 'DON-RC-OWNER');
        $this->makePayment('DON-RC-OWNER', 'success');

        $this->actingAs($other)
            ->get(route('donation.receipt', $donation))
            ->assertForbidden();
    }
}