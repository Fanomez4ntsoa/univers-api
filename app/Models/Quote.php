<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'owner_id',
    'client_id',
    'quote_number',
    'title',
    'chantier_type',
    'chantier_address',
    'work_description',
    'items',
    'subtotal',
    'tax_rate',
    'tax_amount',
    'total',
    'global_discount_percent',
    'global_discount_amount',
    'status',
    'valid_until',
    'validity_days',
    'payment_terms',
    'payment_delay_days',
    'notes',
    'internal_notes',
    'terms_and_conditions',
    'signed',
    'signature_url',
    'signed_by',
    'signed_at',
    'signed_ip',
    'sent_at',
    'viewed_at',
    'invoice_id',
])]
class Quote extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'items'                    => 'array',
            'subtotal'                 => 'decimal:2',
            'tax_rate'                 => 'decimal:2',
            'tax_amount'               => 'decimal:2',
            'total'                    => 'decimal:2',
            'global_discount_percent'  => 'decimal:2',
            'global_discount_amount'   => 'decimal:2',
            'signed'                   => 'boolean',
            'valid_until'              => 'date',
            'validity_days'            => 'integer',
            'payment_delay_days'       => 'integer',
            'signed_at'               => 'datetime',
            'sent_at'                 => 'datetime',
            'viewed_at'               => 'datetime',
        ];
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'viewed']);
    }

    public function isSignable(): bool
    {
        return in_array($this->status, ['sent', 'viewed']) && !$this->signed;
    }

    public function isConvertibleToInvoice(): bool
    {
        return $this->status === 'accepted' && $this->invoice_id === null;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
