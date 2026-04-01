<?php

namespace App\Modules\Ecosystem\Services;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;

class ListingService
{
    // ===== MES ANNONCES (protégé) =====

    public function listMine(int $userId): Collection
    {
        return Listing::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(int $userId, array $data): Listing
    {
        $data['user_id'] = $userId;
        $data['status'] = 'active';
        $data['expires_at'] = now()->addDays(30);

        return Listing::create($data)->load('user:id,username,display_name,avatar_url');
    }

    public function update(int $userId, int $id, array $data): Listing
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $userId) {
            abort(403, 'Vous ne pouvez modifier que vos propres annonces.');
        }

        $listing->update($data);

        return $listing->fresh();
    }

    public function delete(int $userId, int $id): void
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $userId) {
            abort(403, 'Vous ne pouvez supprimer que vos propres annonces.');
        }

        $listing->delete();
    }

    /**
     * Mark listing as sold.
     * Blocked if already sold or cancelled.
     */
    public function markSold(int $userId, int $id): Listing
    {
        $listing = Listing::findOrFail($id);

        if ($listing->user_id !== $userId) {
            abort(403, 'Vous ne pouvez modifier que vos propres annonces.');
        }

        if (in_array($listing->status, ['sold', 'cancelled'])) {
            abort(422, 'Cette annonce ne peut plus être marquée comme vendue (statut : ' . $listing->status . ').');
        }

        $listing->update(['status' => 'sold']);

        return $listing->fresh();
    }

    // ===== ANNONCES PUBLIQUES =====

    /**
     * List active, non-expired listings with optional filters.
     * Mirrors Emergent GET /listings with category, city, price filters.
     */
    public function listPublic(?string $category = null, ?string $city = null, ?string $priceType = null): Collection
    {
        $query = Listing::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with('user:id,username,display_name,avatar_url,city');

        if ($category) {
            $query->where('category', $category);
        }
        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }
        if ($priceType) {
            $query->where('price_type', $priceType);
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Show a public listing and increment views_count.
     * Mirrors Emergent GET /listings/{id}.
     */
    public function showPublic(int $id): Listing
    {
        $listing = Listing::with('user:id,username,display_name,avatar_url,city')
            ->findOrFail($id);

        $listing->increment('views_count');

        return $listing->fresh()->load('user:id,username,display_name,avatar_url,city');
    }
}
