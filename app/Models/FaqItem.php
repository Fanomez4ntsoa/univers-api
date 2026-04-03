<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'question',
    'answer',
    'category',
    'order',
    'is_active',
])]
class FaqItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'order'     => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
