<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chantier_id',
    'owner_id',
    'worker_name',
    'hours',
    'date',
    'description',
])]
class ChantierTimeEntry extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'hours' => 'decimal:2',
            'date'  => 'date',
        ];
    }

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
