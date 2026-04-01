<?php

namespace App\Modules\ClientPortal\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;

class ClientPortalService
{
    /**
     * Find client by portal token or abort 404.
     */
    public function findClientByToken(string $token): Client
    {
        $client = Client::where('portal_token', $token)->first();

        if (!$client) {
            abort(404, 'Lien d\'accès invalide.');
        }

        return $client;
    }

    /**
     * Portal dashboard: client info + quotes + invoices + chantiers.
     * Mirrors Emergent GET /portal/{token}.
     */
    public function dashboard(string $token): array
    {
        $client = $this->findClientByToken($token);

        return [
            'client' => [
                'name'    => $client->name,
                'email'   => $client->email,
                'phone'   => $client->phone,
                'address' => $client->address,
                'city'    => $client->city,
            ],
            'quotes' => $client->quotes()
                ->where('status', '!=', 'draft')
                ->orderByDesc('created_at')
                ->get(['id', 'quote_number', 'title', 'status', 'total', 'valid_until', 'signed', 'created_at']),
            'invoices' => $client->invoices()
                ->where('status', '!=', 'draft')
                ->orderByDesc('created_at')
                ->get(['id', 'invoice_number', 'status', 'total', 'amount_due', 'due_date', 'created_at']),
            'chantiers' => $client->chantiers()
                ->orderByDesc('created_at')
                ->get(['id', 'address', 'chantier_type', 'work_description', 'status', 'quote_number', 'quote_amount', 'planned_start_date', 'planned_end_date']),
        ];
    }

    /**
     * List quotes for a client (excludes drafts).
     */
    public function listQuotes(string $token): array
    {
        $client = $this->findClientByToken($token);

        return $client->quotes()
            ->where('status', '!=', 'draft')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    /**
     * Show a single quote with company settings for CGV display.
     * Auto-transitions sent → viewed.
     * Mirrors Emergent GET /quotes/public/{id}.
     */
    public function showQuote(string $token, int $quoteId): array
    {
        $client = $this->findClientByToken($token);

        $quote = $client->quotes()
            ->where('status', '!=', 'draft')
            ->findOrFail($quoteId);

        // Auto-transition: sent → viewed
        if ($quote->status === 'sent') {
            $quote->update([
                'status'    => 'viewed',
                'viewed_at' => now(),
            ]);
            $quote->refresh();
        }

        // Load company settings for CGV display
        $company = $quote->owner?->companySetting;

        return [
            'quote'   => $quote,
            'company' => $company ? [
                'company_name' => $company->company_name,
                'logo_url'     => $company->logo_url,
                'address'      => $company->address,
                'city'         => $company->city,
                'postal_code'  => $company->postal_code,
                'phone'        => $company->phone,
                'siret'        => $company->siret,
                'cgv_text'     => $company->cgv_text,
            ] : null,
        ];
    }

    /**
     * Sign a quote from the client portal.
     * Mirrors Emergent POST /portal/{token}/devis/{id}/sign.
     */
    public function signQuote(string $token, int $quoteId, array $signatureData): Quote
    {
        $client = $this->findClientByToken($token);

        $quote = $client->quotes()->findOrFail($quoteId);

        if (!$quote->isSignable()) {
            abort(422, 'Ce devis ne peut pas être signé (statut : ' . $quote->status . ').');
        }

        // Check expiration
        if ($quote->valid_until && $quote->valid_until->isPast()) {
            abort(422, 'Ce devis a expiré le ' . $quote->valid_until->format('d/m/Y') . '.');
        }

        $quote->update([
            'status'        => 'accepted',
            'signed'        => true,
            'signature_url' => $signatureData['signature_image'],
            'signed_by'     => $signatureData['signed_by'],
            'signed_at'     => now(),
        ]);

        return $quote->fresh();
    }

    /**
     * List invoices for a client (excludes drafts).
     */
    public function listInvoices(string $token): array
    {
        $client = $this->findClientByToken($token);

        return $client->invoices()
            ->where('status', '!=', 'draft')
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    /**
     * Show a single invoice.
     */
    public function showInvoice(string $token, int $invoiceId): Invoice
    {
        $client = $this->findClientByToken($token);

        return $client->invoices()
            ->where('status', '!=', 'draft')
            ->findOrFail($invoiceId);
    }
}
