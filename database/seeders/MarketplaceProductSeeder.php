<?php

namespace Database\Seeders;

use App\Models\MarketplaceProduct;
use Illuminate\Database\Seeder;

class MarketplaceProductSeeder extends Seeder
{
    /**
     * Demo products for local development (categories, stock and sold count
     * are varied so filtering, sorting and stock validation can be tried out).
     */
    public function run(): void
    {
        $products = [
            ['Ocean Tote Bag', 'bags', 85000, 15, 120, 'Tas tote dari jaring ikan bekas yang didaur ulang.'],
            ['Recycled Pouch Mini', 'bags', 35000, 3, 45, 'Pouch kecil serbaguna dari plastik laut.'],
            ['Eco Tumbler 500ml', 'drinkware', 120000, 20, 210, 'Tumbler dari botol PET daur ulang.'],
            ['Ocean Glass Bottle', 'drinkware', 65000, 0, 80, 'Botol kaca hasil daur ulang limbah pantai.'],
            ['Upcycled Bracelet', 'fashion', 25000, 50, 300, 'Gelang dari tali tambang kapal bekas.'],
            ['Recycled Cap', 'fashion', 95000, 8, 15, 'Topi dari benang plastik daur ulang.'],
            ['Driftwood Coaster Set', 'home', 55000, 12, 60, 'Tatakan gelas dari kayu apung.'],
            ['Ocean Plastic Planter', 'home', 150000, 5, 22, 'Pot tanaman dari plastik laut yang dilebur.'],
            ['Recycled Notebook', 'stationery', 30000, 40, 150, 'Buku catatan dari kertas daur ulang.'],
            ['Eco Pen Set', 'stationery', 20000, 2, 95, 'Set pulpen dari tutup botol daur ulang.'],
        ];

        foreach ($products as [$name, $category, $price, $stock, $sold, $description]) {
            MarketplaceProduct::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'category' => $category,
                    'price' => $price,
                    'stock' => $stock,
                    'sold_count' => $sold,
                    'image_path' => 'https://placehold.co/600x600/0b0b0b/63CFC0?text='.urlencode($name),
                ]
            );
        }
    }
}
