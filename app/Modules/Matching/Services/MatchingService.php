<?php

namespace App\Modules\Matching\Services;

use App\Models\ProjectQuote;
use App\Models\ProjectRequest;
use Illuminate\Database\Eloquent\Collection;

class MatchingService
{
    // ===== CÔTÉ PARTICULIER — Mes demandes =====

    public function listMyRequests(int $userId): Collection
    {
        return ProjectRequest::where('user_id', $userId)
            ->withCount('quotes')
            ->orderByDesc('created_at')
            ->get();
    }

    public function createRequest(int $userId, array $data): ProjectRequest
    {
        $data['user_id'] = $userId;
        $data['status'] = 'open';

        return ProjectRequest::create($data);
    }

    public function showRequest(int $userId, int $id): array
    {
        $request = ProjectRequest::where('user_id', $userId)
            ->findOrFail($id);

        $quotes = $request->quotes()
            ->with('artisan:id,username,display_name,avatar_url,metier,city,company_name')
            ->orderByDesc('created_at')
            ->get();

        return [
            'request' => $request,
            'quotes'  => $quotes,
        ];
    }

    public function updateRequest(int $userId, int $id, array $data): ProjectRequest
    {
        $request = ProjectRequest::where('user_id', $userId)->findOrFail($id);

        if (in_array($request->status, ['matched', 'closed'])) {
            abort(422, 'Cette demande ne peut plus être modifiée (statut : ' . $request->status . ').');
        }

        $request->update($data);

        return $request->fresh();
    }

    public function deleteRequest(int $userId, int $id): void
    {
        $request = ProjectRequest::where('user_id', $userId)->findOrFail($id);

        if ($request->status === 'matched') {
            abort(422, 'Une demande avec un artisan accepté ne peut pas être supprimée.');
        }

        $request->delete();
    }

    public function closeRequest(int $userId, int $id): ProjectRequest
    {
        $request = ProjectRequest::where('user_id', $userId)->findOrFail($id);

        $request->update(['status' => 'closed']);

        return $request->fresh();
    }

    /**
     * Accept an artisan's quote on a request.
     * Sets request status → matched, accepted quote → accepted, other quotes → refused.
     */
    public function acceptQuote(int $userId, int $requestId, int $quoteId): ProjectRequest
    {
        $request = ProjectRequest::where('user_id', $userId)->findOrFail($requestId);

        if ($request->status !== 'open' && $request->status !== 'in_review') {
            abort(422, 'Cette demande ne peut plus accepter de devis (statut : ' . $request->status . ').');
        }

        $quote = ProjectQuote::where('project_request_id', $requestId)
            ->findOrFail($quoteId);

        // Accept this quote
        $quote->update(['status' => 'accepted']);

        // Refuse all other quotes
        ProjectQuote::where('project_request_id', $requestId)
            ->where('id', '!=', $quoteId)
            ->where('status', 'pending')
            ->update(['status' => 'refused']);

        // Update request status
        $request->update(['status' => 'matched']);

        return $request->fresh();
    }

    // ===== CÔTÉ ARTISAN — Demandes disponibles =====

    /**
     * List open requests (not the artisan's own).
     */
    public function listAvailable(int $userId, ?string $category = null, ?string $city = null): Collection
    {
        $query = ProjectRequest::where('status', 'open')
            ->where('user_id', '!=', $userId)
            ->with('user:id,username,display_name,city')
            ->withCount('quotes');

        if ($category) {
            $query->where('category', $category);
        }
        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Submit a quote on a request. One per artisan per request.
     */
    public function submitQuote(int $artisanId, int $requestId, array $data): ProjectQuote
    {
        $request = ProjectRequest::findOrFail($requestId);

        if ($request->status !== 'open') {
            abort(422, 'Cette demande n\'accepte plus de devis.');
        }

        if ($request->user_id === $artisanId) {
            abort(422, 'Vous ne pouvez pas répondre à votre propre demande.');
        }

        $existing = ProjectQuote::where('project_request_id', $requestId)
            ->where('artisan_id', $artisanId)
            ->first();

        if ($existing) {
            abort(422, 'Vous avez déjà soumis un devis pour cette demande.');
        }

        return ProjectQuote::create(array_merge($data, [
            'project_request_id' => $requestId,
            'artisan_id'         => $artisanId,
            'status'             => 'pending',
        ]))->load('artisan:id,username,display_name,avatar_url,metier,city');
    }

    /**
     * List quotes submitted by this artisan.
     */
    public function listMyQuotes(int $artisanId): Collection
    {
        return ProjectQuote::where('artisan_id', $artisanId)
            ->with('projectRequest:id,title,category,city,status,budget_min,budget_max')
            ->orderByDesc('created_at')
            ->get();
    }
}
