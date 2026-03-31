<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'company_name',
    'logo_url',
    'siret',
    'tva_number',
    'cgv_text',
    'payment_terms',
    'bank_details',
    'address',
    'city',
    'postal_code',
    'phone',
    'email',
    'website',
    'quote_counter',
    'invoice_counter',
])]
class CompanySetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'bank_details'    => 'array',
            'quote_counter'   => 'integer',
            'invoice_counter' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
