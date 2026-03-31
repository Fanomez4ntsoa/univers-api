<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class InvoiceService
{
    // ===== CRUD =====

    public function listForOwner(int $ownerId, ?string $status = null, ?int $clientId = null): Collection
    {
        $query = Invoice::where('owner_id', $ownerId);

        if ($status) {
            $query->where('status', $status);
        }
        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        return $query->with('client:id,name,email')
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(int $ownerId, array $data): Invoice
    {
        $client = Client::where('owner_id', $ownerId)->findOrFail($data['client_id']);

        $data['owner_id'] = $ownerId;
        $data['invoice_number'] = $this->generateInvoiceNumber();
        $data['status'] = 'draft';

        // Denormalize client info
        $data['client_name'] = $client->name;
        $data['client_email'] = $client->email;
        $data['client_address'] = $client->address;
        $data['client_siret'] = $client->siret;
        $data['client_tva_number'] = null;

        $this->calculateTotals($data);
        $data['amount_due'] = $data['total'];
        $data['amount_paid'] = 0;

        $invoice = Invoice::create($data);

        // Increment client stats
        Client::where('id', $client->id)->increment('total_invoices');

        return $invoice->load('client:id,name,email');
    }

    public function findForOwner(int $ownerId, int $id): Invoice
    {
        return Invoice::where('owner_id', $ownerId)
            ->with('client', 'quote')
            ->findOrFail($id);
    }

    public function update(int $ownerId, int $id, array $data): Invoice
    {
        $invoice = Invoice::where('owner_id', $ownerId)->findOrFail($id);

        if (!$invoice->isEditable()) {
            abort(422, 'Seule une facture en brouillon peut être modifiée.');
        }

        if (isset($data['items'])) {
            $this->calculateTotals($data);
            $data['amount_due'] = $data['total'];
        }

        $invoice->update($data);

        return $invoice->fresh()->load('client:id,name,email');
    }

    public function delete(int $ownerId, int $id): void
    {
        $invoice = Invoice::where('owner_id', $ownerId)->findOrFail($id);

        if (in_array($invoice->status, ['paid', 'partial'])) {
            abort(422, 'Une facture payée ou partiellement payée ne peut pas être supprimée.');
        }

        $invoice->delete();
    }

    // ===== ACTIONS =====

    /**
     * Mark invoice as sent. Mirrors Emergent pattern.
     */
    public function send(int $ownerId, int $id): Invoice
    {
        $invoice = Invoice::where('owner_id', $ownerId)->findOrFail($id);

        if ($invoice->status !== 'draft') {
            abort(422, 'Seule une facture en brouillon peut être envoyée.');
        }

        $invoice->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Mark invoice as fully paid.
     * Mirrors Emergent POST /factures/{id}/mark-paid.
     */
    public function markPaid(int $ownerId, int $id): Invoice
    {
        $invoice = Invoice::where('owner_id', $ownerId)->findOrFail($id);

        if ($invoice->isPaid()) {
            abort(422, 'Cette facture est déjà payée.');
        }

        $invoice->update([
            'status'       => 'paid',
            'amount_paid'  => $invoice->total,
            'amount_due'   => 0,
            'paid_at'      => now(),
            'payment_date' => now()->toDateString(),
        ]);

        // Increment client revenue
        $clientService = app(ClientService::class);
        $clientService->incrementRevenue($invoice->client_id, (float) $invoice->total);

        return $invoice->fresh();
    }

    /**
     * Mark invoice as cancelled.
     */
    public function cancel(int $ownerId, int $id): Invoice
    {
        $invoice = Invoice::where('owner_id', $ownerId)->findOrFail($id);

        if ($invoice->isPaid()) {
            abort(422, 'Une facture payée ne peut pas être annulée.');
        }

        $invoice->update([
            'status'     => 'cancelled',
            'amount_due' => 0,
        ]);

        return $invoice->fresh();
    }

    // ===== HELPERS PRIVÉS =====

    private function generateInvoiceNumber(): string
    {
        return 'FAC-' . now()->format('Ym') . '-' . strtoupper(Str::random(6));
    }

    /**
     * Calculate line totals + invoice totals.
     * Same calculation logic as QuoteService (shared with Emergent DevisLine).
     */
    private function calculateTotals(array &$data): void
    {
        $items = $data['items'] ?? [];
        $subtotalHt = 0;
        $totalTva = 0;

        foreach ($items as &$item) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discountPercent = (float) ($item['discount_percent'] ?? 0);
            $tvaRate = (float) ($item['tva_rate'] ?? 20);

            $subtotalBeforeDiscount = $qty * $unitPrice;
            $discountAmount = round($subtotalBeforeDiscount * ($discountPercent / 100), 2);
            $lineSubtotal = round($subtotalBeforeDiscount - $discountAmount, 2);
            $tvaAmount = round($lineSubtotal * ($tvaRate / 100), 2);
            $lineTotal = round($lineSubtotal + $tvaAmount, 2);

            $item['discount_amount'] = $discountAmount;
            $item['subtotal'] = $lineSubtotal;
            $item['tva_amount'] = $tvaAmount;
            $item['total'] = $lineTotal;

            $subtotalHt += $lineSubtotal;
            $totalTva += $tvaAmount;
        }

        $data['items'] = $items;
        $data['subtotal'] = round($subtotalHt, 2);
        $data['tax_amount'] = round($totalTva, 2);
        $data['total'] = round($subtotalHt + $totalTva, 2);
    }
}
