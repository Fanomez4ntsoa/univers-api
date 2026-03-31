<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Modules\Auth\Services\CoreAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoreAuthMiddleware
{
    public function __construct(private CoreAuthService $coreAuthService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token manquant.'], 401);
        }

        $coreUser = $this->coreAuthService->fetchMe($token);

        if (!$coreUser) {
            return response()->json(['message' => 'Token invalide ou expiré.'], 401);
        }

        // Sync or create local user from Core data
        $user = $this->coreAuthService->syncUser($coreUser);

        // Attach user + raw token to request
        $request->attributes->set('auth_user', $user);
        $request->attributes->set('core_token', $token);

        // Make accessible via auth()->user() pattern via request macro
        app()->instance('bati_user', $user);

        return $next($request);
    }
}
