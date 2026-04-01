<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'shop_id',
    'user_id',
    'name',
    'description',
    'price',
    'images',
    'category',
    'stock',
    'is_active',
])]
class ShopProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'images'    => 'array',
            'price'     => 'decimal:2',
            'stock'     => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
