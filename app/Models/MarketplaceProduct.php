<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceProduct extends Model
{
    /**
     * Recycled product categories (key => label).
     */
    public const CATEGORIES = [
        'bags' => 'Tas & Pouch',
        'drinkware' => 'Botol & Tumbler',
        'fashion' => 'Fashion & Aksesoris',
        'home' => 'Dekorasi & Rumah Tangga',
        'stationery' => 'Alat Tulis',
    ];

    /**
     * Sorting options for the public marketplace (key => label).
     */
    public const SORT_OPTIONS = [
        'newest' => 'Terbaru',
        'best_selling' => 'Terlaris',
        'price_asc' => 'Harga Terendah',
        'price_desc' => 'Harga Tertinggi',
    ];

    protected $table = 'marketplace_products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'stock',
        'sold_count',
        'image_path',
    ];

    protected $casts = [
        'stock' => 'integer',
        'sold_count' => 'integer',
    ];
}
