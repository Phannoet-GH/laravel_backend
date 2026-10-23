<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wishlist extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'description',
        'icon',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'wishlist_items', 'wishlist_id', 'product_id');
    }

    public function toResponseArray(): array
    {
        return [
            'id' => (string) $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description ?? '',
            'icon' => $this->icon ?? '✨',
            'is_default' => (bool) $this->is_default,
            'created_at' => $this->created_at?->toISOString() ?? now()->toISOString(),
            'product_ids' => $this->products()->pluck('products.id')->map(fn($id) => (int) $id)->toArray(),
        ];
    }
}
