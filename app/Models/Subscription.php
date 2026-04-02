<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'stripe_customer_id',
    'stripe_subscription_id',
    'plan',
    'status',
    'current_period_start',
    'current_period_end',
    'cancelled_at',
])]
class Subscription extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'current_period_start' => 'datetime',
            'current_period_end'   => 'datetime',
            'cancelled_at'         => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }
}
