<?php

namespace App\Modules\Ecosystem\Services;

use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ShopService
{
    // ===== MA BOUTIQUE (protégé) =====

    /**
     * Get or auto-create the authenticated user's shop.
     * Mirrors Emergent firstOrCreate pattern like CompanySettings.
     */
    public function getMyShop(int $userId): Shop
    {
        $shop = Shop::where('user_id', $userId)->first();

        if ($shop) {
            return $shop;
        }

        $user = User::findOrFail($userId);
        $baseName = $user->company_name ?: $user->display_name ?: 'Ma Boutique';

        return Shop::create([
            'user_id'  => $userId,
            'name'     => $baseName,
            'slug'     => $this->generateSlug($baseName, $userId),
            'category' => $user->metier,
            'city'     => $user->city,
        ]);
    }

    public function updateMyShop(int $userId, array $data): Shop
    {
        $shop = $this->getMyShop($userId);

        // Regenerate slug if name changes
        if (isset($data['name']) && $data['name'] !== $shop->name) {
            $data['slug'] = $this->generateSlug($data['name'], $userId);
        }

        $shop->update($data);

        return $shop->fresh();
    }

    // ===== MES PRODUITS (protégé) =====

    public function listMyProducts(int $userId): Collection
    {
        $shop = $this->getMyShop($userId);

        return ShopProduct::where('shop_id', $shop->id)
            ->orderByDesc('created_at')
            ->get();
    }

    public function createProduct(int $userId, array $data): ShopProduct
    {
        $shop = $this->getMyShop($userId);

        $data['shop_id'] = $shop->id;
        $data['user_id'] = $userId;

        return ShopProduct::create($data);
    }

    public function updateProduct(int $userId, int $productId, array $data): ShopProduct
    {
        $product = ShopProduct::findOrFail($productId);

        if ($product->user_id !== $userId) {
            abort(403, 'Vous ne pouvez modifier que vos propres produits.');
        }

        $product->update($data);

        return $product->fresh();
    }

    public function deleteProduct(int $userId, int $productId): void
    {
        $product = ShopProduct::findOrFail($productId);

        if ($product->user_id !== $userId) {
            abort(403, 'Vous ne pouvez supprimer que vos propres produits.');
        }

        $product->delete();
    }

    // ===== BOUTIQUES PUBLIQUES =====

    /**
     * List all active shops.
     * Mirrors Emergent GET /boutique.
     */
    public function listPublicShops(): Collection
    {
        return Shop::where('is_active', true)
            ->with('user:id,display_name,avatar_url,metier,phone')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Show a public shop by slug with its active products.
     * Mirrors Emergent GET /boutique/{slug}.
     */
    public function showPublicShop(string $slug): array
    {
        $shop = Shop::where('slug', $slug)
            ->where('is_active', true)
            ->with('user:id,display_name,avatar_url,metier,city,phone')
            ->firstOrFail();

        $products = $shop->products()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->get();

        return [
            'shop'     => $shop,
            'products' => $products,
        ];
    }

    // ===== HELPERS =====

    /**
     * Generate URL-friendly slug from name.
     * Mirrors Emergent generate_slug() with accent handling + userId suffix.
     */
    private function generateSlug(string $name, int $userId): string
    {
        $slug = Str::slug($name);

        if (empty($slug)) {
            $slug = 'boutique';
        }

        $slug = $slug . '-' . substr(md5((string) $userId), 0, 6);

        // Ensure uniqueness
        $existing = Shop::where('slug', $slug)->exists();
        if ($existing) {
            $slug .= '-' . Str::random(4);
        }

        return $slug;
    }
}
