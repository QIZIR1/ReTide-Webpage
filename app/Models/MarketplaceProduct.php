<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceProduct extends Model
{
    protected $table = 'marketplace_products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image_path',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];
}
