<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoreAuthService
{
    private string $coreUrl;

    public function __construct()
    {
        $this->coreUrl = rtrim(config('app.core_api_url'), '/');
    }

    /**
     * Call Core GET /api/me and return the profile data, or null on failure.
     */
    public function fetchMe(string $token): ?array
    {
        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get("{$this->coreUrl}/api/me");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('CoreAuthService::fetchMe failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create or update local user from Core /me response.
     * Core GET /api/me returns a flat profile object:
     * { id, user_id, email, username, display_name, role, user_type, ... }
     */
    public function syncUser(array $coreData): User
    {
        // Core /api/me returns a flat object — id and user_id both hold the UUID
        $coreUuid = $coreData['id'] ?? $coreData['user_id'] ?? null;

        return User::updateOrCreate(
            ['core_uuid' => $coreUuid],
            [
                'email'                => $coreData['email'] ?? '',
                'username'             => $coreData['username'] ?? null,
                'display_name'         => $coreData['display_name'] ?? null,
                'first_name'           => $coreData['first_name'] ?? null,
                'last_name'            => $coreData['last_name'] ?? null,
                'phone'                => $coreData['phone'] ?? null,
                'avatar_url'           => $coreData['profile_photo'] ?? null,
                'user_type'            => $coreData['user_type'] ?? 'particulier',
                'role'                 => $coreData['role'] ?? 'social_user',
                'city'                 => $coreData['city'] ?? null,
                'metier'               => $coreData['metier'] ?? null,
                'company_name'         => $coreData['company_name'] ?? null,
                'siret'                => $coreData['siret'] ?? null,
                'bio'                  => $coreData['bio'] ?? null,
                'is_verified'          => $coreData['is_verified'] ?? false,
                'identity_status'      => $coreData['identity_status'] ?? null,
                'is_active'            => $coreData['is_active'] ?? true,
                'has_pro_subscription' => $coreData['has_pro_subscription'] ?? false,
                'shop_enabled'         => $coreData['shop_enabled'] ?? false,
                'last_synced_at'       => now(),
            ]
        );
    }
}
