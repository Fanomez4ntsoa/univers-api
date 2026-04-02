<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_request_id',
    'artisan_id',
    'message',
    'price',
    'delay_days',
    'status',
])]
class ProjectQuote extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'delay_days' => 'integer',
        ];
    }

    public function projectRequest(): BelongsTo
    {
        return $this->belongsTo(ProjectRequest::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }
}
