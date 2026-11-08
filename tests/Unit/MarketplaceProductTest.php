<?php

namespace Tests\Unit;

use App\Models\MarketplaceProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_product_can_be_instantiated_and_persisted(): void
    {
        $product = MarketplaceProduct::create([
            'name' => 'Eco Bottle',
            'description' => 'A bottle made of ocean plastic.',
            'price' => 150000.00,
            'image_path' => 'https://res.cloudinary.com/eco-bottle.jpg',
        ]);

        $this->assertDatabaseHas('marketplace_products', [
            'id' => $product->id,
            'name' => 'Eco Bottle',
            'description' => 'A bottle made of ocean plastic.',
            'price' => 150000.00,
            'image_path' => 'https://res.cloudinary.com/eco-bottle.jpg',
        ]);

        $this->assertEquals('marketplace_products', $product->getTable());
        $this->assertEquals('id', $product->getKeyName());
        $this->assertEquals([
            'name',
            'description',
            'category',
            'price',
            'stock',
            'sold_count',
            'image_path',
        ], $product->getFillable());
    }
}
