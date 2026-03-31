<?php

namespace App\Modules\CRM\Services;

use App\Models\Prospect;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProspectService
{
    public function listForOwner(int $ownerId): Collection
    {
        return Prospect::where('owner_id', $ownerId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(int $ownerId, array $data): Prospect
    {
        return Prospect::create(array_merge($data, ['owner_id' => $ownerId]));
    }

    public function findForOwner(int $ownerId, int $id): Prospect
    {
        return Prospect::where('owner_id', $ownerId)
            ->findOrFail($id);
    }

    public function update(int $ownerId, int $id, array $data): Prospect
    {
        $prospect = $this->findForOwner($ownerId, $id);
        $prospect->update($data);

        return $prospect->fresh();
    }

    public function delete(int $ownerId, int $id): void
    {
        $prospect = $this->findForOwner($ownerId, $id);
        $prospect->delete();
    }
}
