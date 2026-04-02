<?php

namespace App\Modules\Matching\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Matching\Requests\StoreProjectQuoteRequest;
use App\Modules\Matching\Requests\StoreProjectRequestRequest;
use App\Modules\Matching\Requests\UpdateProjectRequestRequest;
use App\Modules\Matching\Services\MatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MatchingController extends Controller
{
    public function __construct(private MatchingService $matchingService) {}

    // --- Côté particulier ---

    public function myRequests(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->matchingService->listMyRequests($user->id));
    }

    public function storeRequest(StoreProjectRequestRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->matchingService->createRequest($user->id, $request->validated()),
            201
        );
    }

    public function showRequest(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->matchingService->showRequest($user->id, $id));
    }

    public function updateRequest(UpdateProjectRequestRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->matchingService->updateRequest($user->id, $id, $request->validated())
        );
    }

    public function destroyRequest(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->matchingService->deleteRequest($user->id, $id);

        return response()->json(['message' => 'Demande supprimée.']);
    }

    public function closeRequest(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->matchingService->closeRequest($user->id, $id));
    }

    public function acceptQuote(Request $request, int $id, int $quoteId): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->matchingService->acceptQuote($user->id, $id, $quoteId));
    }

    // --- Côté artisan ---

    public function available(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->matchingService->listAvailable(
                $user->id,
                $request->query('category'),
                $request->query('city'),
            )
        );
    }

    public function submitQuote(StoreProjectQuoteRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->matchingService->submitQuote($user->id, $id, $request->validated()),
            201
        );
    }

    public function myQuotes(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->matchingService->listMyQuotes($user->id));
    }
}
