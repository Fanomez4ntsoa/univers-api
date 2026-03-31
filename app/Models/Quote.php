<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'owner_id',
    'client_id',
    'quote_number',
    'chantier_type',
    'chantier_address',
    'work_description',
    'items',
    'subtotal',
    'tax_rate',
    'tax_amount',
    'total',
    'status',
    'signed',
    'signature_url',
    'valid_until',
])]
class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'items'       => 'array',
            'subtotal'    => 'decimal:2',
            'tax_rate'    => 'decimal:2',
            'tax_amount'  => 'decimal:2',
            'total'       => 'decimal:2',
            'signed'      => 'boolean',
            'valid_until' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
