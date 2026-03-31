<?php

namespace App\Modules\CRM\Services;

use App\Models\Chantier;
use App\Models\ChantierComment;
use App\Models\ChantierCost;
use App\Models\ChantierDocument;
use App\Models\ChantierTimeEntry;
use App\Models\Client;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Collection;

class ChantierService
{
    private const HOURLY_RATE = 50; // EUR/h default (Emergent hardcoded)

    // ===== CRUD =====

    public function listForOwner(int $ownerId, ?string $status = null): Collection
    {
        $query = Chantier::where('owner_id', $ownerId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with('client:id,name')
            ->orderBy('planned_start_date')
            ->get();
    }

    public function create(int $ownerId, array $data): Chantier
    {
        $client = Client::where('owner_id', $ownerId)->findOrFail($data['client_id']);
        $data['owner_id'] = $ownerId;
        $data['client_name'] = $client->name;
        $data['status'] = $data['status'] ?? 'to_plan';
        $data['pipeline_stage'] = $data['status'];

        // Denormalize quote info if provided
        if (!empty($data['quote_id'])) {
            $quote = Quote::where('owner_id', $ownerId)->find($data['quote_id']);
            if ($quote) {
                $data['quote_number'] = $quote->quote_number;
                $data['quote_amount'] = $data['quote_amount'] ?? $quote->total;
                $data['work_description'] = $data['work_description'] ?? $quote->work_description;
                $data['chantier_type'] = $data['chantier_type'] ?? $quote->chantier_type;
                $data['address'] = $data['address'] ?? $quote->chantier_address;
            }
        }

        return Chantier::create($data)->load('client:id,name');
    }

    public function findForOwner(int $ownerId, int $id): array
    {
        $chantier = Chantier::where('owner_id', $ownerId)
            ->with('client', 'quote', 'documents', 'comments', 'timeEntries', 'costs')
            ->findOrFail($id);

        return $chantier->toArray();
    }

    public function update(int $ownerId, int $id, array $data): Chantier
    {
        $chantier = Chantier::where('owner_id', $ownerId)->findOrFail($id);
        $chantier->update($data);

        return $chantier->fresh()->load('client:id,name');
    }

    public function delete(int $ownerId, int $id): void
    {
        $chantier = Chantier::where('owner_id', $ownerId)->findOrFail($id);

        if (in_array($chantier->status, ['in_progress', 'completed'])) {
            abort(422, 'Un chantier en cours ou terminé ne peut pas être supprimé.');
        }

        $chantier->delete();
    }

    // ===== PIPELINE =====

    /**
     * Get chantiers grouped by pipeline stage.
     * Mirrors Emergent GET /pipeline/chantier.
     */
    public function pipeline(int $ownerId): array
    {
        $stages = ['to_plan', 'planned', 'started', 'in_progress', 'completed'];
        $chantiers = Chantier::where('owner_id', $ownerId)
            ->with('client:id,name')
            ->orderBy('planned_start_date')
            ->get();

        $grouped = [];
        foreach ($stages as $stage) {
            $grouped[$stage] = $chantiers->where('pipeline_stage', $stage)->values();
        }

        return $grouped;
    }

    /**
     * Move a chantier to a new pipeline stage.
     * Mirrors Emergent PUT /pipeline/chantier/move.
     * Auto-sets actual_start_date / actual_end_date.
     */
    public function moveStage(int $ownerId, int $chantierId, string $newStage): Chantier
    {
        $chantier = Chantier::where('owner_id', $ownerId)->findOrFail($chantierId);

        $update = [
            'status'         => $newStage,
            'pipeline_stage' => $newStage,
        ];

        if ($newStage === 'started' && !$chantier->actual_start_date) {
            $update['actual_start_date'] = now()->toDateString();
        }

        if ($newStage === 'completed' && !$chantier->actual_end_date) {
            $update['actual_end_date'] = now()->toDateString();
        }

        $chantier->update($update);

        return $chantier->fresh()->load('client:id,name');
    }

    // ===== DOCUMENTS =====

    public function addDocument(int $ownerId, int $chantierId, array $data): ChantierDocument
    {
        Chantier::where('owner_id', $ownerId)->findOrFail($chantierId);

        return ChantierDocument::create(array_merge($data, [
            'chantier_id' => $chantierId,
            'owner_id'    => $ownerId,
        ]));
    }

    public function removeDocument(int $ownerId, int $chantierId, int $documentId): void
    {
        Chantier::where('owner_id', $ownerId)->findOrFail($chantierId);

        ChantierDocument::where('chantier_id', $chantierId)
            ->findOrFail($documentId)
            ->delete();
    }

    // ===== COMMENTS =====

    public function addComment(int $ownerId, int $chantierId, array $data): ChantierComment
    {
        Chantier::where('owner_id', $ownerId)->findOrFail($chantierId);

        return ChantierComment::create(array_merge($data, [
            'chantier_id' => $chantierId,
            'owner_id'    => $ownerId,
        ]));
    }

    // ===== TIME ENTRIES =====

    /**
     * Add time entry and recalculate rentability.
     * Mirrors Emergent POST /chantiers/{id}/time-entries.
     */
    public function addTimeEntry(int $ownerId, int $chantierId, array $data): ChantierTimeEntry
    {
        $chantier = Chantier::where('owner_id', $ownerId)->findOrFail($chantierId);

        $entry = ChantierTimeEntry::create(array_merge($data, [
            'chantier_id' => $chantierId,
            'owner_id'    => $ownerId,
        ]));

        $this->recalculateRentability($chantier);

        return $entry;
    }

    // ===== COSTS =====

    /**
     * Add cost entry and recalculate rentability.
     * Mirrors Emergent POST /chantiers/{id}/costs.
     */
    public function addCost(int $ownerId, int $chantierId, array $data): ChantierCost
    {
        $chantier = Chantier::where('owner_id', $ownerId)->findOrFail($chantierId);

        $cost = ChantierCost::create(array_merge($data, [
            'chantier_id' => $chantierId,
            'owner_id'    => $ownerId,
        ]));

        $this->recalculateRentability($chantier);

        return $cost;
    }

    // ===== RENTABILITY =====

    /**
     * Recalculate actual_cost, total_hours, margin, rentability_level.
     * Mirrors Emergent logic: actual_cost = (total_hours * 50) + material_costs
     */
    private function recalculateRentability(Chantier $chantier): void
    {
        $totalHours = $chantier->timeEntries()->sum('hours');
        $materialCosts = $chantier->costs()->sum('amount');

        $laborCost = $totalHours * self::HOURLY_RATE;
        $actualCost = $laborCost + $materialCosts;
        $quoteAmount = (float) $chantier->quote_amount;
        $margin = $quoteAmount > 0 ? $quoteAmount - $actualCost : 0;
        $marginPercent = $quoteAmount > 0 ? ($margin / $quoteAmount) * 100 : 0;

        $level = 'medium';
        if ($marginPercent >= 30) {
            $level = 'high';
        } elseif ($marginPercent < 10) {
            $level = 'low';
        }

        $chantier->update([
            'total_hours'       => round($totalHours, 2),
            'actual_cost'       => round($actualCost, 2),
            'margin'            => round($marginPercent, 2),
            'rentability'       => round($marginPercent, 2),
            'rentability_level' => $level,
        ]);
    }
}
