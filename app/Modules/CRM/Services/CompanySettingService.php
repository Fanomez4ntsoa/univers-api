<?php

namespace App\Modules\CRM\Services;

use App\Models\CompanySetting;

class CompanySettingService
{
    /**
     * Get settings for the authenticated artisan.
     * Auto-creates a default record if none exists (Emergent upsert behavior).
     */
    public function getForUser(int $userId): CompanySetting
    {
        return CompanySetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'company_name'    => '',
                'address'         => '',
                'city'            => '',
                'postal_code'     => '',
                'phone'           => '',
                'email'           => '',
                'siret'           => '',
                'cgv_text'        => '',
                'payment_terms'   => 'Paiement à 30 jours',
                'quote_counter'   => 0,
                'invoice_counter' => 0,
            ]
        );
    }

    /**
     * Update settings for the authenticated artisan.
     * quote_counter and invoice_counter are NOT updatable via this method.
     * Mirrors Emergent PUT /settings behavior.
     */
    public function update(int $userId, array $data): CompanySetting
    {
        // Prevent manual modification of counters
        unset($data['quote_counter'], $data['invoice_counter'], $data['user_id']);

        $settings = $this->getForUser($userId);
        $settings->update($data);

        return $settings->fresh();
    }
}
