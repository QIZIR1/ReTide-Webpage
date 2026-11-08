<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'amount',
        'message',
        'user_id',
        'order_id',
        'receipt_code',
    ];

    public function updates()
    {
        return $this->hasMany(DonationUpdate::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }

    public function isPaid(): bool
    {
        return $this->payment?->status === 'success';
    }

    public static function generateReceiptCode(): string
    {
        return 'RCPT-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }
}