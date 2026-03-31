<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Models\ClientNote;
use App\Models\Prospect;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ClientService
{
    // ===== CRUD =====

    public function listForOwner(int $ownerId): Collection
    {
        return Client::where('owner_id', $ownerId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(int $ownerId, array $data): Client
    {
        return Client::create(array_merge($data, ['owner_id' => $ownerId]));
    }

    public function findForOwner(int $ownerId, int $id): Client
    {
        return Client::where('owner_id', $ownerId)->findOrFail($id);
    }

    /**
     * Show client enriched with related quotes, invoices, chantiers.
     * Mirrors Emergent GET /clients/{id} behavior.
     */
    public function showEnriched(int $ownerId, int $id): array
    {
        $client = $this->findForOwner($ownerId, $id);

        return array_merge($client->toArray(), [
            'quotes'    => $client->quotes()->orderByDesc('created_at')->limit(50)->get(),
            'invoices'  => $client->invoices()->orderByDesc('created_at')->limit(50)->get(),
            'chantiers' => $client->chantiers()->orderByDesc('created_at')->limit(50)->get(),
            'notes'     => $client->notes()->orderByDesc('created_at')->get(),
        ]);
    }

    public function update(int $ownerId, int $id, array $data): Client
    {
        $client = $this->findForOwner($ownerId, $id);
        $client->update($data);

        return $client->fresh();
    }

    public function delete(int $ownerId, int $id): void
    {
        $this->findForOwner($ownerId, $id)->delete();
    }

    // ===== NOTES =====

    public function addNote(int $ownerId, int $clientId, array $data): ClientNote
    {
        $client = $this->findForOwner($ownerId, $clientId);

        return ClientNote::create(array_merge($data, [
            'client_id' => $client->id,
            'owner_id'  => $ownerId,
        ]));
    }

    // ===== PORTAL TOKEN =====

    public function generatePortalToken(int $ownerId, int $clientId): Client
    {
        $client = $this->findForOwner($ownerId, $clientId);

        $client->update([
            'portal_token'            => Str::random(40),
            'portal_token_created_at' => now(),
        ]);

        return $client->fresh();
    }

    // ===== CONVERSION PROSPECT → CLIENT =====

    /**
     * Convert a prospect to a client.
     * Copies: name, phone, email, address, city, postal_code, company_name, siret.
     * Sets prospect status → converted.
     * Mirrors Emergent POST /prospects/{id}/convert-to-client.
     */
    public function convertFromProspect(int $ownerId, int $prospectId): Client
    {
        $prospect = Prospect::where('owner_id', $ownerId)->findOrFail($prospectId);

        $client = Client::create([
            'owner_id'    => $ownerId,
            'prospect_id' => $prospect->id,
            'name'        => $prospect->name,
            'phone'       => $prospect->phone,
            'email'       => $prospect->email,
            'address'     => $prospect->address,
            'city'        => $prospect->city,
            'postal_code' => $prospect->postal_code,
            'company_name' => null,
            'siret'       => null,
        ]);

        $prospect->update([
            'status'         => 'converted',
            'pipeline_stage' => 'signe',
        ]);

        return $client;
    }

    // ===== STATS (appelé depuis QuoteService / InvoiceService) =====

    public function incrementQuoteCount(int $clientId): void
    {
        Client::where('id', $clientId)->increment('total_quotes');
    }

    public function incrementInvoiceCount(int $clientId): void
    {
        Client::where('id', $clientId)->increment('total_invoices');
    }

    public function incrementRevenue(int $clientId, float $amount): void
    {
        Client::where('id', $clientId)->increment('total_revenue', $amount);
    }
}
