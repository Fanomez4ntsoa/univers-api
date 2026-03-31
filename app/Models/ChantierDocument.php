<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'chantier_id',
    'owner_id',
    'name',
    'file_url',
    'file_type',
    'description',
])]
class ChantierDocument extends Model
{
    use HasFactory;

    public function chantier(): BelongsTo
    {
        return $this->belongsTo(Chantier::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
