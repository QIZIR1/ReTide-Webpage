<?php

namespace Tests\Feature;

use App\Models\MarketplaceProduct;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 2: Validasi Kuantitas & Stock Lock Prevention.
 */
class StockValidationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(array $overrides = []): MarketplaceProduct
    {
        return MarketplaceProduct::create(array_merge([
            'name' => 'Eco Bag',
            'description' => 'Organic cotton bag',
            'price' => 75000,
            'stock' => 10,
        ], $overrides));
    }

    private function putInCart(MarketplaceProduct $product, int $quantity): void
    {
        session(['cart' => [
            $product->id => ['name' => $product->name, 'quantity' => $quantity, 'price' => $product->price, 'image' => 'x.jpg'],
        ]]);
    }

    public function test_cannot_add_product_to_cart_exceeding_available_stock(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = $this->createProduct(['name' => 'Eco Cup', 'stock' => 2]);

        // Adding up to the available stock is allowed
        $this->actingAs($user)->postJson(route('cart.add'), ['product_id' => $product->id])->assertStatus(200);
        $this->actingAs($user)->postJson(route('cart.add'), ['product_id' => $product->id])->assertStatus(200);

        // Third add exceeds the stock of 2
        $this->actingAs($user)
            ->postJson(route('cart.add'), ['product_id' => $product->id])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Only 2 item(s) of Eco Cup left in stock.',
            ]);

        // Increasing the quantity in the cart beyond stock is rejected too
        $this->actingAs($user)
            ->postJson(route('cart.update'), ['product_id' => $product->id, 'quantity' => 3])
            ->assertStatus(422);

        $this->assertEquals(2, session('cart')[$product->id]['quantity']);
    }

    public function test_checkout_is_blocked_when_stock_drops_after_adding_to_cart(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = $this->createProduct(['stock' => 5]);

        $this->putInCart($product, 3);

        // Stock changes after the item was put in the cart
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

    public function test_successful_purchase_decreases_stock_and_increases_sold_count(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = $this->createProduct(['stock' => 3, 'sold_count' => 10]);

        // Buy the whole stock through the demo checkout flow
        $this->putInCart($product, 3);
        $this->actingAs($user)->get(route('cart.checkout'))->assertStatus(200);

        // Stock is only reduced once the payment is completed
        $this->assertEquals(3, $product->fresh()->stock);

        $this->actingAs($user)->get(route('marketplace.success'))->assertStatus(200);

        $product->refresh();
        $this->assertEquals(0, $product->stock);
        $this->assertEquals(13, $product->sold_count);
        $this->assertNotNull(Payment::where('user_id', $user->id)->first()->fulfilled_at);

        // The product is now out of stock
        $this->actingAs($user)
            ->postJson(route('cart.add'), ['product_id' => $product->id])
            ->assertStatus(422)
            ->assertJson(['message' => 'Eco Bag is out of stock.']);
    }
}
