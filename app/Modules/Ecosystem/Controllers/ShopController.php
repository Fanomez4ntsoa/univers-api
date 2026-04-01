<?php

namespace App\Modules\Ecosystem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Ecosystem\Requests\StoreShopProductRequest;
use App\Modules\Ecosystem\Requests\UpdateShopProductRequest;
use App\Modules\Ecosystem\Requests\UpdateShopRequest;
use App\Modules\Ecosystem\Services\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(private ShopService $shopService) {}

    // --- Ma boutique (protégé) ---

    public function showMine(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->shopService->getMyShop($user->id));
    }

    public function updateMine(UpdateShopRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->shopService->updateMyShop($user->id, $request->validated())
        );
    }

    // --- Mes produits (protégé) ---

    public function listMyProducts(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->shopService->listMyProducts($user->id));
    }

    public function storeProduct(StoreShopProductRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->shopService->createProduct($user->id, $request->validated()),
            201
        );
    }

    public function updateProduct(UpdateShopProductRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->shopService->updateProduct($user->id, $id, $request->validated())
        );
    }

    public function destroyProduct(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->shopService->deleteProduct($user->id, $id);

        return response()->json(['message' => 'Produit supprimé.']);
    }

    // --- Boutiques publiques ---

    public function listPublic(): JsonResponse
    {
        return response()->json($this->shopService->listPublicShops());
    }

    public function showPublic(string $slug): JsonResponse
    {
        return response()->json($this->shopService->showPublicShop($slug));
    }
}
