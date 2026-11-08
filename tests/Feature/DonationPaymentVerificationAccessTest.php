<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationPaymentVerificationAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeDonation(User $user, int $amount = 10000): Donation
    {
        return Donation::create([
            'name' => $user->name,
            'email' => $user->email,
            'amount' => $amount,
            'user_id' => $user->id,
        ]);
    }

    public function test_other_user_cannot_view_donation_updates(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $donation = $this->makeDonation($owner);

        $this->actingAs($other)
            ->get(route('donation.updates', $donation))
            ->assertForbidden();
    }

    public function test_owner_can_view_own_donation_updates(): void
    {
        $owner = User::factory()->create();
        $donation = $this->makeDonation($owner);

        $this->actingAs($owner)
            ->get(route('donation.updates', $donation))
            ->assertOk();
    }

    public function test_summary_counts_only_own_donations(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->makeDonation($user, 10000);
        $this->makeDonation($user, 20000);
        $this->makeDonation($other, 50000);

        $this->actingAs($user)
            ->get(route('donation.index'))
            ->assertOk()
            ->assertViewHas('summary', fn ($s) => (float) $s['total'] === 30000.0 && $s['count'] === 2);
    }
}