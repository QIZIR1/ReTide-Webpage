<?php

namespace Tests\Feature;

use App\Models\MarketplaceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = MarketplaceProduct::create([
            'name' => 'Eco Cup',
            'description' => 'Cup made of recycled plastic.',
            'price' => 50000,
            'image_path' => 'https://res.cloudinary.com/ecocup.jpg',
        ]);

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Eco Cup added to cart!',
        ]);

        $cart = session()->get('cart');
        $this->assertNotNull($cart);
        $this->assertArrayHasKey($product->id, $cart);
        $this->assertEquals('Eco Cup', $cart[$product->id]['name']);
        $this->assertEquals(1, $cart[$product->id]['quantity']);
        $this->assertEquals(50000, $cart[$product->id]['price']);
        $this->assertEquals('https://res.cloudinary.com/ecocup.jpg', $cart[$product->id]['image']);
    }

    public function test_add_to_cart_fails_for_non_existent_product(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => 99999,
        ]);

        $response->assertStatus(404);
    }

    public function test_user_can_sync_cart(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = MarketplaceProduct::create([
            'name' => 'Eco Straws',
            'description' => 'Eco friendly reusable straws',
            'price' => 20000,
            'image_path' => 'https://res.cloudinary.com/straws.jpg',
        ]);

        $payload = [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => 'Eco Straws',
                    'quantity' => 3,
                    'price' => 20000,
                    'image' => 'https://res.cloudinary.com/straws.jpg',
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson(route('cart.sync'), $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Cart synced successfully!',
        ]);

        $cart = session()->get('cart');
        $this->assertNotNull($cart);
        $this->assertArrayHasKey($product->id, $cart);
        $this->assertEquals($product->name, $cart[$product->id]['name']);
        $this->assertEquals(3, $cart[$product->id]['quantity']);
        $this->assertEquals($product->price, $cart[$product->id]['price']);
        $this->assertEquals($product->image_path, $cart[$product->id]['image']);
    }

    public function test_user_cannot_manipulate_price_during_sync(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = MarketplaceProduct::create([
            'name' => 'Eco Bag',
            'description' => 'Organic cotton bag',
            'price' => 75000,
            'image_path' => 'https://res.cloudinary.com/ecobag.jpg',
        ]);

        // Manipulated client payload tries to set price to 1000 and different name/image
        $payload = [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => 'Manipulated Bag Name',
                    'quantity' => 2,
                    'price' => 1000,
                    'image' => 'https://res.cloudinary.com/manipulated.jpg',
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson(route('cart.sync'), $payload);

        $response->assertStatus(200);

        $cart = session()->get('cart');
        $this->assertNotNull($cart);
        $this->assertArrayHasKey($product->id, $cart);

        // Ensure canonical database attributes are used, and client-supplied attributes are ignored
        $this->assertEquals($product->name, $cart[$product->id]['name']);
        $this->assertEquals(2, $cart[$product->id]['quantity']); // quantity is kept
        $this->assertEquals($product->price, $cart[$product->id]['price']); // price must be canonical
        $this->assertEquals($product->image_path, $cart[$product->id]['image']); // image must be canonical
    }

    public function test_checkout_redirects_if_cart_is_empty(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('cart.checkout'));

        $response->assertRedirect(route('marketplace.index'));
        $response->assertSessionHas('error', 'Cart is empty!');
    }

    public function test_checkout_happy_path_creates_payment_and_returns_snap_token(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Mock Midtrans Snap
        $mockSnap = Mockery::mock('alias:\Midtrans\Snap');
        $mockSnap->shouldReceive('getSnapToken')->andReturn('dummy-snap-token');

        // Checkout re-validates stock, so the product must exist in the database
        $product = MarketplaceProduct::create([
            'name' => 'Eco Bag',
            'description' => 'Organic cotton bag',
            'price' => 75000,
            'stock' => 10,
        ]);

        // Put some items in the cart
        $cartData = [
            $product->id => [
                'name' => 'Eco Bag',
                'quantity' => 2,
                'price' => 75000,
                'image' => 'https://res.cloudinary.com/ecobag.jpg',
            ],
        ];
        session(['cart' => $cartData]);

        $response = $this->actingAs($user)->get(route('cart.checkout'));

        $response->assertStatus(200);
        $response->assertViewIs('checkout');
        $response->assertViewHas('snapToken', 'dummy-snap-token');

        $this->assertDatabaseHas('payments', [
            'payment_type' => 'marketplace',
            'status' => 'pending',
            'gross_amount' => 150000.00,
            'user_id' => $user->id,
            'snap_token' => 'dummy-snap-token',
        ]);
    }

    public function test_process_redirects_if_cart_is_empty(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('cart.process'));

        $response->assertRedirect(route('marketplace.index'));
        $response->assertSessionHas('error', 'Cart is empty!');
    }

    public function test_process_happy_path_creates_payment_and_returns_json_response(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        // Mock Midtrans Snap
        $mockSnap = Mockery::mock('alias:\Midtrans\Snap');
        $mockSnap->shouldReceive('getSnapToken')->andReturn('dummy-snap-token');

        // Process re-validates stock, so the product must exist in the database
        $product = MarketplaceProduct::create([
            'name' => 'Eco Shoes',
            'description' => 'Shoes made of recycled plastic',
            'price' => 500000,
            'stock' => 10,
        ]);

        // Put some items in the cart
        $cartData = [
            $product->id => [
                'name' => 'Eco Shoes',
                'quantity' => 1,
                'price' => 500000,
                'image' => 'https://res.cloudinary.com/ecoshoes.jpg',
            ],
        ];
        session(['cart' => $cartData]);

        $response = $this->actingAs($user)->postJson(route('cart.process'));

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'snap_token' => 'dummy-snap-token',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_type' => 'marketplace',
            'status' => 'pending',
            'gross_amount' => 500000.00,
            'user_id' => $user->id,
            'snap_token' => 'dummy-snap-token',
        ]);
    }

    public function test_adding_existing_product_to_cart_increments_quantity(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = MarketplaceProduct::create([
            'name' => 'Eco Cup',
            'description' => 'Cup made of recycled plastic.',
            'price' => 50000,
            'image_path' => 'https://res.cloudinary.com/ecocup.jpg',
        ]);

        // Add once
        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
        ]);
        $response->assertStatus(200);

        // Add again
        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'product_id' => $product->id,
        ]);
        $response->assertStatus(200);

        $cart = session()->get('cart');
        $this->assertNotNull($cart);
        $this->assertCount(1, $cart); // Ensure only one entry exists in cart
        $this->assertEquals(2, $cart[$product->id]['quantity']); // Ensure quantity is incremented to 2
    }

    public function test_unauthenticated_guests_are_redirected_to_login_for_cart_endpoints(): void
    {
        // 1. Add to cart
        $response = $this->post(route('cart.add'), ['product_id' => 1]);
        $response->assertRedirect(route('login'));

        // 2. Sync cart
        $response = $this->post(route('cart.sync'), ['cart' => []]);
        $response->assertRedirect(route('login'));

        // 3. Checkout
        $response = $this->get(route('cart.checkout'));
        $response->assertRedirect(route('login'));

        // 4. Process cart
        $response = $this->post(route('cart.process'));
        $response->assertRedirect(route('login'));
    }
}
