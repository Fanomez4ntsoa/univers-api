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
    'client_name',
    'client_email',
    'client_address',
    'client_siret',
    'client_tva_number',
    'quote_id',
    'invoice_number',
    'items',
    'subtotal',
    'tax_rate',
    'tax_amount',
    'total',
    'amount_paid',
    'amount_due',
    'payment_terms',
    'notes',
    'status',
    'due_date',
    'payment_date',
    'sent_at',
    'paid_at',
])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'items'        => 'array',
            'subtotal'     => 'decimal:2',
            'tax_rate'     => 'decimal:2',
            'tax_amount'   => 'decimal:2',
            'total'        => 'decimal:2',
            'amount_paid'  => 'decimal:2',
            'amount_due'   => 'decimal:2',
            'due_date'     => 'date',
            'payment_date' => 'date',
            'sent_at'      => 'datetime',
            'paid_at'      => 'datetime',
        ];
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
