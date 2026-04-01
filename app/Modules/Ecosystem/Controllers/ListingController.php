<?php

namespace App\Modules\Ecosystem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Ecosystem\Requests\StoreListingRequest;
use App\Modules\Ecosystem\Requests\UpdateListingRequest;
use App\Modules\Ecosystem\Services\ListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function __construct(private ListingService $listingService) {}

    // --- Mes annonces (protégé) ---

    public function myListings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->listingService->listMine($user->id));
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->listingService->create($user->id, $request->validated()),
            201
        );
    }

    public function update(UpdateListingRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->listingService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->listingService->delete($user->id, $id);

        return response()->json(['message' => 'Annonce supprimée.']);
    }

    public function markSold(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->listingService->markSold($user->id, $id));
    }

    // --- Annonces publiques ---

    public function listPublic(Request $request): JsonResponse
    {
        return response()->json(
            $this->listingService->listPublic(
                $request->query('category'),
                $request->query('city'),
                $request->query('price_type'),
            )
        );
    }

    public function showPublic(int $id): JsonResponse
    {
        return response()->json($this->listingService->showPublic($id));
    }
}
