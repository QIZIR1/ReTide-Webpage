<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MarketplaceFeatureEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['coupons' => [
            'PERCENT10' => ['type' => 'percent', 'value' => 10, 'min_subtotal' => 0, 'max_discount' => null, 'expires_at' => null],
            'FIXED20K' => ['type' => 'fixed', 'value' => 20000, 'min_subtotal' => 100000, 'max_discount' => null, 'expires_at' => null],
            'EXPIRED' => ['type' => 'percent', 'value' => 50, 'min_subtotal' => 0, 'max_discount' => null, 'expires_at' => '2000-01-01'],
        ]]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function cartWithSubtotal150k(): array
    {
        return [
            1 => [
                'name' => 'Eco Bag',
                'quantity' => 2,
                'price' => 75000,
                'image' => 'https://res.cloudinary.com/ecobag.jpg',
            ],
        ];
    }

    public function test_apply_valid_discount_coupon_reduces_cart_total(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $mockSnap = Mockery::mock('alias:\Midtrans\Snap');
        $mockSnap->shouldReceive('getSnapToken')->andReturn('dummy-snap-token');

        session(['cart' => $this->cartWithSubtotal150k()]);

        // Apply percent coupon (lowercase input must be accepted)
        $response = $this->actingAs($user)->postJson(route('cart.coupon.apply'), [
            'coupon_code' => 'percent10',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'coupon' => 'PERCENT10',
            'subtotal' => 150000,
            'discount' => 15000,
            'total' => 135000,
        ]);
        $this->assertEquals('PERCENT10', session('coupon'));

        // Checkout must charge the discounted total
        $checkout = $this->actingAs($user)->get(route('cart.checkout'));
        $checkout->assertStatus(200);
        $checkout->assertViewHas('totalAmount', 135000);
        $checkout->assertViewHas('discount', 15000);

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'gross_amount' => 135000.00,
        ]);

        // Fixed-amount coupon replaces the previous one
        $response = $this->actingAs($user)->postJson(route('cart.coupon.apply'), [
            'coupon_code' => 'FIXED20K',
        ]);
        $response->assertStatus(200);
        $response->assertJson(['discount' => 20000, 'total' => 130000]);
    }

    public function test_invalid_expired_or_below_minimum_coupon_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        session(['cart' => [
            1 => ['name' => 'Eco Straw', 'quantity' => 1, 'price' => 50000, 'image' => 'x.jpg'],
        ]]);

        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'NOTEXIST'])
            ->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'Invalid coupon code.']);

        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'EXPIRED'])
            ->assertStatus(422)
            ->assertJson(['message' => 'This coupon has expired.']);

        // Subtotal 50.000 is below FIXED20K minimum of 100.000
        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'FIXED20K'])
            ->assertStatus(422)
            ->assertJson(['discount' => 0, 'total' => 50000]);

        $this->assertNull(session('coupon'));
    }

    public function test_user_can_remove_applied_coupon(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        session(['cart' => $this->cartWithSubtotal150k(), 'coupon' => 'PERCENT10']);

        $this->actingAs($user)
            ->deleteJson(route('cart.coupon.remove'))
            ->assertStatus(200)
            ->assertJson(['coupon' => null, 'discount' => 0, 'total' => 150000]);

        $this->assertNull(session('coupon'));
    }
}
