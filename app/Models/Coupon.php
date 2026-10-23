<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'min_order_amount' => 'float',
        'is_active' => 'boolean',
    ];
}
