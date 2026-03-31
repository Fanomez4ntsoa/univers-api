<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'core_uuid',
    'email',
    'username',
    'display_name',
    'first_name',
    'last_name',
    'phone',
    'avatar_url',
    'user_type',
    'role',
    'city',
    'metier',
    'company_name',
    'siret',
    'is_verified',
    'is_active',
    'has_pro_subscription',
    'last_synced_at',
])]
class User extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_verified'          => 'boolean',
            'is_active'            => 'boolean',
            'has_pro_subscription' => 'boolean',
            'last_synced_at'       => 'datetime',
        ];
    }

    public function isProfessionnel(): bool
    {
        return $this->user_type === 'professionnel';
    }

    public function isParticulier(): bool
    {
        return $this->user_type === 'particulier';
    }
}
