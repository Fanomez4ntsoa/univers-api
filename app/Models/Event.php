<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'description',
    'event_type',
    'city',
    'address',
    'start_date',
    'end_date',
    'max_attendees',
    'attendees_count',
    'is_free',
    'price',
    'image_url',
    'status',
])]
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date'      => 'datetime',
            'end_date'        => 'datetime',
            'max_attendees'   => 'integer',
            'attendees_count' => 'integer',
            'is_free'         => 'boolean',
            'price'           => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }
}
