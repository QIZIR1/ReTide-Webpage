<?php

namespace Tests\Feature;

use App\Models\MarketplaceProduct;
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

    private function createProduct(array $overrides = []): MarketplaceProduct
    {
        return MarketplaceProduct::create(array_merge([
            'name' => 'Eco Bag',
            'description' => 'Organic cotton bag',
            'price' => 75000,
            'stock' => 10,
            'image_path' => 'https://res.cloudinary.com/ecobag.jpg',
        ], $overrides));
    }

    private function cartWithSubtotal150k(): array
    {
        $product = $this->createProduct();

        return [
            $product->id => [
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

    public function test_cannot_add_product_to_cart_exceeding_available_stock(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = $this->createProduct(['name' => 'Eco Cup', 'stock' => 2]);

        // Adding up to the available stock is allowed
        $this->actingAs($user)->postJson(route('cart.add'), ['product_id' => $product->id])->assertStatus(200);
        $this->actingAs($user)->postJson(route('cart.add'), ['product_id' => $product->id])->assertStatus(200);

        // Third add exceeds stock of 2
        $this->actingAs($user)
            ->postJson(route('cart.add'), ['product_id' => $product->id])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Only 2 item(s) of Eco Cup left in stock.',
                'stock' => 2,
            ]);

        $this->assertEquals(2, session('cart')[$product->id]['quantity']);

        // Increasing quantity via the +/- endpoint beyond stock is rejected too
        $this->actingAs($user)
            ->postJson(route('cart.update'), ['product_id' => $product->id, 'quantity' => 3])
            ->assertStatus(422);

        // Lowering quantity within stock is fine
        $this->actingAs($user)
            ->postJson(route('cart.update'), ['product_id' => $product->id, 'quantity' => 1])
            ->assertStatus(200)
            ->assertJson(['quantity' => 1]);

        // Syncing a manipulated quantity from the frontend is rejected
        $this->actingAs($user)
            ->postJson(route('cart.sync'), ['cart' => [['id' => $product->id, 'quantity' => 99]]])
            ->assertStatus(422)
            ->assertJson(['status' => 'error']);

        $this->assertEquals(1, session('cart')[$product->id]['quantity']);
    }

    public function test_out_of_stock_product_cannot_be_added_and_checkout_rechecks_stock(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $soldOut = $this->createProduct(['name' => 'Eco Straw', 'stock' => 0]);

        $this->actingAs($user)
            ->postJson(route('cart.add'), ['product_id' => $soldOut->id])
            ->assertStatus(422)
            ->assertJson(['message' => 'Eco Straw is out of stock.']);

        // Stock dropped after the item was put in the cart
        $product = $this->createProduct(['stock' => 5]);
        session(['cart' => [
            $product->id => ['name' => 'Eco Bag', 'quantity' => 3, 'price' => 75000, 'image' => 'x.jpg'],
        ]]);
        $product->update(['stock' => 1]);

        $this->actingAs($user)
            ->get(route('cart.checkout'))
            ->assertRedirect(route('marketplace.index'))
            ->assertSessionHas('error', 'Only 1 item(s) of Eco Bag left in stock.');

        $this->actingAs($user)
            ->postJson(route('cart.process'))
            ->assertStatus(422);

        $this->assertDatabaseCount('payments', 0);
    }
}
