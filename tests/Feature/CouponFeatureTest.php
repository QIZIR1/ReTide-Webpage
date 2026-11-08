<?php

namespace Tests\Feature;

use App\Models\MarketplaceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Fitur 1: Kupon Promo / Voucher Diskon.
 */
class CouponFeatureTest extends TestCase
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

    private function cartWithProduct(int $price, int $quantity): MarketplaceProduct
    {
        $product = MarketplaceProduct::create([
            'name' => 'Eco Bag',
            'description' => 'Organic cotton bag',
            'price' => $price,
            'stock' => 10,
        ]);

        session(['cart' => [
            $product->id => ['name' => 'Eco Bag', 'quantity' => $quantity, 'price' => $price, 'image' => 'x.jpg'],
        ]]);

        return $product;
    }

    public function test_apply_valid_discount_coupon_reduces_cart_total(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $mockSnap = Mockery::mock('alias:\Midtrans\Snap');
        $mockSnap->shouldReceive('getSnapToken')->andReturn('dummy-snap-token');

        // Cart subtotal: 2 x 75.000 = 150.000
        $this->cartWithProduct(75000, 2);

        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'percent10'])
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'coupon' => 'PERCENT10',
                'subtotal' => 150000,
                'discount' => 15000,
                'total' => 135000,
            ]);

        // Checkout must charge the discounted total
        $this->actingAs($user)
            ->get(route('cart.checkout'))
            ->assertStatus(200)
            ->assertViewHas('totalAmount', 135000);

        $this->assertDatabaseHas('payments', [
            'user_id' => $user->id,
            'gross_amount' => 135000.00,
        ]);
    }

    public function test_invalid_expired_or_below_minimum_coupon_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Subtotal 50.000
        $this->cartWithProduct(50000, 1);

        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'NOTEXIST'])
            ->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'Invalid coupon code.']);

        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'EXPIRED'])
            ->assertStatus(422)
            ->assertJson(['message' => 'This coupon has expired.']);

        // FIXED20K needs a minimum purchase of 100.000
        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['coupon_code' => 'FIXED20K'])
            ->assertStatus(422)
            ->assertJson(['discount' => 0, 'total' => 50000]);

        $this->assertNull(session('coupon'));
    }

    public function test_user_can_remove_applied_coupon(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->cartWithProduct(75000, 2);
        session(['coupon' => 'FIXED20K']);

        $this->actingAs($user)
            ->deleteJson(route('cart.coupon.remove'))
            ->assertStatus(200)
            ->assertJson(['coupon' => null, 'discount' => 0, 'total' => 150000]);

        $this->assertNull(session('coupon'));
    }
}
