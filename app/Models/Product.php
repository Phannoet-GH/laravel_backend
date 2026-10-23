<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'brand',
        'category',
        'price',
        'original_price',
        'rating',
        'reviews_count',
        'image_url',
        'description',
        'sizes',
        'colors',
        'specs',
        'badge',
        'in_stock',
    ];

    protected $casts = [
        'price' => 'float',
        'original_price' => 'float',
        'rating' => 'float',
        'reviews_count' => 'integer',
        'in_stock' => 'boolean',
        'sizes' => 'array',
        'colors' => 'array',
        'specs' => 'array',
    ];

    protected $appends = [
        'image',
        'reviews',
    ];

    public function getImageAttribute(): ?string
    {
        return $this->image_url;
    }

    public function getReviewsAttribute(): int
    {
        return $this->reviews_count ?? 0;
    }

    public function customerReviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
