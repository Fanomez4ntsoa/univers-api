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
    'quote_id',
    'invoice_id',
    'address',
    'lat',
    'lng',
    'geofence_radius',
    'chantier_type',
    'status',
    'pipeline_stage',
    'planned_start_date',
    'planned_end_date',
    'actual_start_date',
    'actual_end_date',
    'assigned_workers',
    'quote_amount',
    'actual_cost',
    'margin',
    'rentability',
])]
class Chantier extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'assigned_workers'   => 'array',
            'lat'                => 'decimal:7',
            'lng'                => 'decimal:7',
            'planned_start_date' => 'date',
            'planned_end_date'   => 'date',
            'actual_start_date'  => 'date',
            'actual_end_date'    => 'date',
            'quote_amount'       => 'decimal:2',
            'actual_cost'        => 'decimal:2',
            'margin'             => 'decimal:2',
            'rentability'        => 'decimal:2',
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

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
