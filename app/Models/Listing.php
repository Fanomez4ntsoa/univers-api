<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'description',
    'price',
    'price_type',
    'category',
    'condition',
    'city',
    'images',
    'status',
    'views_count',
    'contact_count',
    'expires_at',
])]
class Listing extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'images'        => 'array',
            'price'         => 'decimal:2',
            'views_count'   => 'integer',
            'contact_count' => 'integer',
            'expires_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
