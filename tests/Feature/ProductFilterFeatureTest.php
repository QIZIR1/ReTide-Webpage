<?php

namespace Tests\Feature;

use App\Models\MarketplaceProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fitur 3: Filter & Sorting Produk Lanjutan.
 */
class ProductFilterFeatureTest extends TestCase
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

    public function test_filter_marketplace_by_category_and_price_range(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $cheapBag = $this->createProduct(['name' => 'Cheap Bag', 'category' => 'bags', 'price' => 30000]);
        $midBag = $this->createProduct(['name' => 'Mid Bag', 'category' => 'bags', 'price' => 80000]);
        $this->createProduct(['name' => 'Luxury Bag', 'category' => 'bags', 'price' => 250000]);
        $this->createProduct(['name' => 'Ocean Bottle', 'category' => 'drinkware', 'price' => 50000]);

        $response = $this->actingAs($user)->get(route('marketplace.index', [
            'category' => 'bags',
            'min_price' => 25000,
            'max_price' => 100000,
            'sort' => 'price_asc',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) use ($cheapBag, $midBag) {
            return $products->pluck('id')->all() === [$cheapBag->id, $midBag->id];
        });
        $response->assertSee('Mid Bag');
        $response->assertDontSee('Luxury Bag');
        $response->assertDontSee('Ocean Bottle');
    }

    public function test_sort_marketplace_by_best_selling(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $low = $this->createProduct(['name' => 'Low Seller', 'sold_count' => 3]);
        $top = $this->createProduct(['name' => 'Top Seller', 'sold_count' => 120]);
        $mid = $this->createProduct(['name' => 'Mid Seller', 'sold_count' => 40]);

        $this->actingAs($user)
            ->get(route('marketplace.index', ['sort' => 'best_selling']))
            ->assertStatus(200)
            ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$top->id, $mid->id, $low->id]);
    }

    public function test_invalid_filter_values_are_ignored(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->createProduct(['name' => 'Product A']);
        $this->createProduct(['name' => 'Product B']);

        // Unknown category / sort and a non-numeric price are ignored
        $this->actingAs($user)
            ->get(route('marketplace.index', [
                'category' => 'weapons',
                'sort' => 'DROP TABLE',
                'min_price' => 'abc',
            ]))
            ->assertStatus(200)
            ->assertViewHas('filters', ['category' => null, 'min_price' => null, 'max_price' => null, 'sort' => 'newest'])
            ->assertViewHas('products', fn ($products) => $products->count() === 2);
    }
}
