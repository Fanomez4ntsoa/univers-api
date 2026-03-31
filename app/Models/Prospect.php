<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'owner_id',
    'name',
    'phone',
    'email',
    'address',
    'city',
    'postal_code',
    'lat',
    'lng',
    'chantier_type',
    'estimated_value',
    'source',
    'status',
    'pipeline_stage',
    'signature_score',
    'notes',
    'next_followup_date',
])]
class Prospect extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'estimated_value'    => 'decimal:2',
            'lat'                => 'decimal:7',
            'lng'                => 'decimal:7',
            'signature_score'    => 'integer',
            'next_followup_date' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
