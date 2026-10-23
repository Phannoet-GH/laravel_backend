<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'order_number',
        'user_id',
        'total_amount',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'promo_code',
        'shipping_address',
        'payment_method',
        'status',
        'tracking_number',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'subtotal' => 'float',
        'shipping_fee' => 'float',
        'discount_amount' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
