<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'title',
    'description',
    'company_name',
    'city',
    'contract_type',
    'category',
    'salary_min',
    'salary_max',
    'experience_level',
    'is_active',
    'expires_at',
    'applications_count',
])]
class JobOffer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jobs_offers';

    protected function casts(): array
    {
        return [
            'salary_min'         => 'decimal:2',
            'salary_max'         => 'decimal:2',
            'is_active'          => 'boolean',
            'applications_count' => 'integer',
            'expires_at'         => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }
}
