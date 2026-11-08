<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'transaction_id',
        'payment_type',
        'status',
        'gross_amount',
        'user_id',
        'payload',
        'items',
    ];

    protected $casts = [
        'payload' => 'array',
        'items' => 'array',
        'fulfilled_at' => 'datetime',
        'gross_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
