<?php

namespace Database\Seeders;

use App\Models\SiteStat;
use Illuminate\Database\Seeder;

class SiteStatSeeder extends Seeder
{
    public function run(): void
    {
        SiteStat::truncate();

        $stats = [
            [
                'key'       => 'clients_satisfaits',
                'value'     => '98%',
                'label'     => 'Clients satisfaits',
                'is_active' => true,
            ],
            [
                'key'       => 'productivite',
                'value'     => '+40%',
                'label'     => 'Productivité moyenne',
                'is_active' => true,
            ],
            [
                'key'       => 'mise_en_place',
                'value'     => '15j',
                'label'     => 'Mise en place',
                'is_active' => true,
            ],
            [
                'key'       => 'devis_rapide',
                'value'     => '3min',
                'label'     => 'Devis',
                'is_active' => true,
            ],
        ];

        foreach ($stats as $stat) {
            SiteStat::create($stat);
        }
    }
}
