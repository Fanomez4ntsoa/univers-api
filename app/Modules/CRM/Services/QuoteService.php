<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class QuoteService
{
    // ===== CRUD =====

    public function listForOwner(int $ownerId, ?string $status = null, ?int $clientId = null): Collection
    {
        $query = Quote::where('owner_id', $ownerId);

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

    public function create(int $ownerId, array $data): Quote
    {
        $data['owner_id'] = $ownerId;
        $data['quote_number'] = $this->generateQuoteNumber();
        $data['status'] = 'draft';

        $this->calculateTotals($data);
        $this->calculateValidUntil($data);

        $quote = Quote::create($data);

        // Increment client stats
        Client::where('id', $data['client_id'])->increment('total_quotes');

        return $quote->load('client:id,name,email');
    }

    public function findForOwner(int $ownerId, int $id): Quote
    {
        return Quote::where('owner_id', $ownerId)
            ->with('client')
            ->findOrFail($id);
    }

    public function update(int $ownerId, int $id, array $data): Quote
    {
        $quote = Quote::where('owner_id', $ownerId)->findOrFail($id);

        if (!$quote->isEditable()) {
            abort(422, 'Ce devis ne peut plus être modifié (statut : ' . $quote->status . ').');
        }

        if (isset($data['items'])) {
            $this->calculateTotals($data);
        }

        if (isset($data['validity_days'])) {
            $this->calculateValidUntil($data, $quote);
        }

        $quote->update($data);

        return $quote->fresh()->load('client:id,name,email');
    }

    public function delete(int $ownerId, int $id): void
    {
        $quote = Quote::where('owner_id', $ownerId)->findOrFail($id);

        if (in_array($quote->status, ['accepted', 'invoiced'])) {
            abort(422, 'Un devis accepté ou facturé ne peut pas être supprimé.');
        }

        $quote->delete();
    }

    // ===== ACTIONS =====

    /**
     * Mark quote as sent. Mirrors Emergent POST /devis/{id}/send.
     */
    public function send(int $ownerId, int $id): Quote
    {
        $quote = Quote::where('owner_id', $ownerId)->findOrFail($id);

        if ($quote->status !== 'draft') {
            abort(422, 'Seul un devis en brouillon peut être envoyé.');
        }

        $quote->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        return $quote->fresh();
    }

    /**
     * Sign/accept a quote. Mirrors Emergent POST /devis/{id}/sign.
     */
    public function sign(int $ownerId, int $id, array $signatureData): Quote
    {
        $quote = Quote::where('owner_id', $ownerId)->findOrFail($id);

        if (!$quote->isSignable()) {
            abort(422, 'Ce devis ne peut pas être signé (statut : ' . $quote->status . ').');
        }

        $quote->update([
            'status'        => 'accepted',
            'signed'        => true,
            'signature_url' => $signatureData['signature_image'] ?? null,
            'signed_by'     => $signatureData['signed_by'] ?? null,
            'signed_at'     => now(),
            'signed_ip'     => $signatureData['signed_ip'] ?? null,
        ]);

        return $quote->fresh();
    }

    /**
     * Duplicate a quote with new number and draft status.
     * Mirrors Emergent POST /devis/{id}/duplicate.
     */
    public function duplicate(int $ownerId, int $id): Quote
    {
        $original = Quote::where('owner_id', $ownerId)->findOrFail($id);

        $newData = $original->toArray();

        // Reset identity + status
        unset($newData['id'], $newData['created_at'], $newData['updated_at'], $newData['deleted_at']);
        $newData['quote_number'] = $this->generateQuoteNumber();
        $newData['status'] = 'draft';
        $newData['signed'] = false;
        $newData['signature_url'] = null;
        $newData['signed_by'] = null;
        $newData['signed_at'] = null;
        $newData['signed_ip'] = null;
        $newData['sent_at'] = null;
        $newData['viewed_at'] = null;
        $newData['invoice_id'] = null;

        $this->calculateValidUntil($newData);

        return Quote::create($newData)->load('client:id,name,email');
    }

    /**
     * Convert accepted quote to invoice.
     * Mirrors Emergent POST /devis/{id}/convert-invoice.
     */
    public function convertToInvoice(int $ownerId, int $id): Invoice
    {
        $quote = Quote::where('owner_id', $ownerId)->findOrFail($id);

        if (!$quote->isConvertibleToInvoice()) {
            abort(422, 'Seul un devis accepté et non encore facturé peut être converti.');
        }

        $client = Client::findOrFail($quote->client_id);

        $invoice = Invoice::create([
            'owner_id'         => $ownerId,
            'client_id'        => $quote->client_id,
            'client_name'      => $client->name,
            'client_email'     => $client->email,
            'client_address'   => $client->address,
            'client_siret'     => $client->siret,
            'client_tva_number' => null,
            'quote_id'         => $quote->id,
            'invoice_number'   => $this->generateInvoiceNumber(),
            'items'            => $quote->items,
            'subtotal'         => $quote->subtotal,
            'tax_rate'         => $quote->tax_rate,
            'tax_amount'       => $quote->tax_amount,
            'total'            => $quote->total,
            'amount_paid'      => 0,
            'amount_due'       => $quote->total,
            'payment_terms'    => $quote->payment_terms ?? 'Paiement à réception',
            'status'           => 'draft',
            'due_date'         => now()->addDays($quote->payment_delay_days),
        ]);

        $quote->update([
            'status'     => 'invoiced',
            'invoice_id' => $invoice->id,
        ]);

        // Increment client stats
        Client::where('id', $quote->client_id)->increment('total_invoices');

        return $invoice;
    }

    // ===== HELPERS PRIVÉS =====

    /**
     * Generate quote number: DEV-YYYYMM-XXXXXX
     */
    private function generateQuoteNumber(): string
    {
        $prefix = 'DEV-' . now()->format('Ym') . '-';
        $suffix = strtoupper(Str::random(6));

        return $prefix . $suffix;
    }

    /**
     * Generate invoice number: FAC-YYYYMM-XXXXXX
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'FAC-' . now()->format('Ym') . '-';
        $suffix = strtoupper(Str::random(6));

        return $prefix . $suffix;
    }

    /**
     * Calculate line totals + devis totals.
     * Mirrors Emergent calculation logic exactly.
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

        // Global discount
        $globalDiscountPercent = (float) ($data['global_discount_percent'] ?? 0);
        $globalDiscountAmount = round($subtotalHt * ($globalDiscountPercent / 100), 2);
        $subtotalHt = round($subtotalHt - $globalDiscountAmount, 2);

        $data['subtotal'] = $subtotalHt;
        $data['tax_amount'] = round($totalTva, 2);
        $data['global_discount_amount'] = $globalDiscountAmount;
        $data['total'] = round($subtotalHt + $totalTva, 2);
    }

    /**
     * Calculate valid_until from validity_days.
     */
    private function calculateValidUntil(array &$data, ?Quote $existing = null): void
    {
        $days = $data['validity_days'] ?? ($existing?->validity_days ?? 30);
        $data['valid_until'] = now()->addDays($days)->toDateString();
    }
}
