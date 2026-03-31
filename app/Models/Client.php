<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'owner_id',
    'prospect_id',
    'name',
    'phone',
    'email',
    'address',
    'city',
    'postal_code',
    'company_name',
    'siret',
    'total_quotes',
    'total_invoices',
    'total_revenue',
    'portal_token',
    'portal_token_created_at',
])]
class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'total_quotes'            => 'integer',
            'total_invoices'          => 'integer',
            'total_revenue'           => 'decimal:2',
            'portal_token_created_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function chantiers(): HasMany
    {
        return $this->hasMany(Chantier::class);
    }
}
