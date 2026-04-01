<?php

namespace App\Modules\Ecosystem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Ecosystem\Services\SocialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function __construct(private SocialService $socialService) {}

    public function discoverUsers(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $page = (int) $request->query('page', 1);

        return response()->json($this->socialService->discoverUsers($user->id, $page));
    }

    public function showProfile(int $id): JsonResponse
    {
        return response()->json($this->socialService->showProfile($id));
    }

    public function toggleFollow(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->socialService->toggleFollow($user->id, $id));
    }

    public function followers(int $id): JsonResponse
    {
        return response()->json($this->socialService->listFollowers($id));
    }

    public function following(int $id): JsonResponse
    {
        return response()->json($this->socialService->listFollowing($id));
    }

    public function personalizedFeed(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $page = (int) $request->query('page', 1);

        return response()->json($this->socialService->personalizedFeed($user->id, $page));
    }

    public function myProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->socialService->myProfile($user->id));
    }
}
