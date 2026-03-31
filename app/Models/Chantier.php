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
    'client_id',
    'client_name',
    'quote_id',
    'quote_number',
    'invoice_id',
    'address',
    'city',
    'postal_code',
    'lat',
    'lng',
    'geofence_radius',
    'chantier_type',
    'work_description',
    'status',
    'pipeline_stage',
    'planned_start_date',
    'planned_end_date',
    'actual_start_date',
    'actual_end_date',
    'assigned_workers',
    'assigned_team',
    'quote_amount',
    'estimated_cost',
    'actual_cost',
    'total_hours',
    'margin',
    'rentability',
    'rentability_level',
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
            'estimated_cost'     => 'decimal:2',
            'actual_cost'        => 'decimal:2',
            'total_hours'        => 'decimal:2',
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

    public function documents(): HasMany
    {
        return $this->hasMany(ChantierDocument::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ChantierComment::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(ChantierTimeEntry::class);
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ChantierCost::class);
    }
}
