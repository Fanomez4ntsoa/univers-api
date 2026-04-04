<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Shop;

/**
 * Seed 12 artisan accounts via Core API + create their shops locally.
 * Data extracted verbatim from AbracadaBati Emergent MOCK_ARTISANS.
 *
 * Prerequisites:
 * - Core (abracadaworld-core) must be running on port 8000
 * - abracadabativ2 must be running on port 8001
 */
class ArtisanSeeder extends Seeder
{
    private const CORE_URL = 'http://localhost:8000';

    public function run(): void
    {
        $artisans = $this->getArtisansData();

        foreach ($artisans as $index => $artisan) {
            $this->command->info("Seeding artisan " . ($index + 1) . "/12: {$artisan['shop_name']}...");

            // 1. Register on Core
            $email = $artisan['email'];
            $password = 'Artisan2026!';

            $registerResponse = Http::acceptJson()->post(self::CORE_URL . '/api/auth/register', [
                'email'        => $email,
                'password'     => $password,
                'password_confirmation' => $password,
                'username'     => $artisan['slug'],
                'display_name' => $artisan['display_name'],
                'user_type'    => 'professionnel',
            ]);

            if (!$registerResponse->successful()) {
                // User might already exist — try login instead
                $loginResponse = Http::acceptJson()->post(self::CORE_URL . '/api/auth/login', [
                    'email'    => $email,
                    'password' => $password,
                ]);

                if (!$loginResponse->successful()) {
                    $this->command->warn("  Skipped {$email} — register/login failed");
                    continue;
                }

                $token = $loginResponse->json('token');
            } else {
                $token = $registerResponse->json('token');
            }

            // 2. Call /me to sync local user
            $meResponse = Http::withToken($token)
                ->acceptJson()
                ->get(self::CORE_URL . '/api/me');

            if (!$meResponse->successful()) {
                $this->command->warn("  Skipped {$email} — /me failed");
                continue;
            }

            $coreData = $meResponse->json();
            $coreUuid = $coreData['id'] ?? $coreData['user_id'] ?? null;

            // 3. Sync local user
            $user = User::updateOrCreate(
                ['core_uuid' => $coreUuid],
                [
                    'email'                => $email,
                    'username'             => $artisan['slug'],
                    'display_name'         => $artisan['display_name'],
                    'user_type'            => 'professionnel',
                    'role'                 => 'professionnel',
                    'city'                 => $artisan['city'],
                    'metier'               => $artisan['metier'],
                    'company_name'         => $artisan['shop_name'],
                    'is_verified'          => true,
                    'is_active'            => true,
                    'has_pro_subscription' => true,
                    'last_synced_at'       => now(),
                ]
            );

            // 4. Create shop locally
            Shop::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name'        => $artisan['shop_name'],
                    'slug'        => $artisan['slug'],
                    'description' => $artisan['description'],
                    'logo_url'    => null,
                    'cover_url'   => $artisan['cover_image'],
                    'category'    => $artisan['metier'],
                    'city'        => $artisan['city'],
                    'is_active'   => true,
                ]
            );

            $this->command->info("  ✓ {$artisan['shop_name']} ({$artisan['city']})");
        }

        $this->command->info('Done! 12 artisans seeded.');
    }

    /**
     * Exact data from AbracadaBati Emergent MOCK_ARTISANS.
     */
    private function getArtisansData(): array
    {
        return [
            [
                'slug'         => 'plomberie-martin-lyon',
                'shop_name'    => 'Plomberie Martin & Fils',
                'display_name' => 'Pierre Martin',
                'metier'       => 'Plombier',
                'description'  => 'Entreprise familiale depuis 1985. Spécialistes en plomberie générale, chauffage et sanitaires. Intervention rapide 7j/7.',
                'city'         => 'Lyon',
                'postal_code'  => '69003',
                'email'        => 'plomberie.martin@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800',
            ],
            [
                'slug'         => 'elec-pro-marseille',
                'shop_name'    => 'Élec Pro Méditerranée',
                'display_name' => 'Thomas Girard',
                'metier'       => 'Électricien',
                'description'  => 'Électriciens certifiés Qualifelec. Mise aux normes, domotique, bornes de recharge. Devis gratuit sous 24h.',
                'city'         => 'Marseille',
                'postal_code'  => '13008',
                'email'        => 'elec.pro@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800',
            ],
            [
                'slug'         => 'maconnerie-dupont-toulouse',
                'shop_name'    => 'Maçonnerie Dupont',
                'display_name' => 'Jean Dupont',
                'metier'       => 'Maçon',
                'description'  => 'Maçonnerie traditionnelle et moderne. Construction, rénovation, extension. Travail soigné garanti.',
                'city'         => 'Toulouse',
                'postal_code'  => '31000',
                'email'        => 'maconnerie.dupont@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
            ],
            [
                'slug'         => 'carrelage-concept-nice',
                'shop_name'    => "Carrelage Concept Côte d'Azur",
                'display_name' => 'Lucas Moreau',
                'metier'       => 'Carreleur',
                'description'  => 'Pose de carrelage haut de gamme. Spécialistes grands formats, mosaïque et pierre naturelle.',
                'city'         => 'Nice',
                'postal_code'  => '06000',
                'email'        => 'carrelage.concept@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=800',
            ],
            [
                'slug'         => 'peinture-artdeco-bordeaux',
                'shop_name'    => "Art'Déco Peinture",
                'display_name' => 'Sophie Lefèvre',
                'metier'       => 'Peintre',
                'description'  => "Peinture décorative et technique. Ravalement, papier peint, effets décoratifs. Artisan d'art.",
                'city'         => 'Bordeaux',
                'postal_code'  => '33000',
                'email'        => 'artdeco.peinture@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=800',
            ],
            [
                'slug'         => 'menuiserie-bois-nantes',
                'shop_name'    => "L'Atelier du Bois",
                'display_name' => 'Marc Dubois',
                'metier'       => 'Menuisier',
                'description'  => 'Menuiserie sur mesure. Fabrication artisanale de meubles, cuisines, dressings et agencements.',
                'city'         => 'Nantes',
                'postal_code'  => '44000',
                'email'        => 'atelier.bois@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1616401784845-180882ba9ba8?w=800',
            ],
            [
                'slug'         => 'toiture-expertise-lille',
                'shop_name'    => 'Toiture Expertise Nord',
                'display_name' => 'Antoine Bernard',
                'metier'       => 'Couvreur',
                'description'  => 'Couverture, zinguerie et isolation de toiture. Urgence fuite 24h/24. Garantie décennale.',
                'city'         => 'Lille',
                'postal_code'  => '59000',
                'email'        => 'toiture.expertise@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1607400201889-565b1ee75f8e?w=800',
            ],
            [
                'slug'         => 'chauffage-confort-strasbourg',
                'shop_name'    => 'Chauffage Confort Plus',
                'display_name' => 'Nicolas Robert',
                'metier'       => 'Chauffagiste',
                'description'  => 'Installation et entretien de systèmes de chauffage. PAC, chaudières, plancher chauffant.',
                'city'         => 'Strasbourg',
                'postal_code'  => '67000',
                'email'        => 'chauffage.confort@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800',
            ],
            [
                'slug'         => 'piscine-azur-montpellier',
                'shop_name'    => 'Piscines Azur Méditerranée',
                'display_name' => 'David Petit',
                'metier'       => 'Pisciniste',
                'description'  => 'Construction et rénovation de piscines. Liner, béton, traitement automatique. SAV réactif.',
                'city'         => 'Montpellier',
                'postal_code'  => '34000',
                'email'        => 'piscines.azur@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1575429198097-0414ec08e8cd?w=800',
            ],
            [
                'slug'         => 'jardin-creation-rennes',
                'shop_name'    => 'Jardins & Créations',
                'display_name' => 'François Leroy',
                'metier'       => 'Paysagiste',
                'description'  => 'Conception et création de jardins. Terrasses, clôtures, arrosage automatique. Entretien.',
                'city'         => 'Rennes',
                'postal_code'  => '35000',
                'email'        => 'jardins.creations@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800',
            ],
            [
                'slug'         => 'clim-pro-aix',
                'shop_name'    => "Clim'Pro Provence",
                'display_name' => 'Julien Garcia',
                'metier'       => 'Climaticien',
                'description'  => 'Installation et maintenance climatisation. Réversible, gainable, multi-split. Certification RGE.',
                'city'         => 'Aix-en-Provence',
                'postal_code'  => '13100',
                'email'        => 'clim.pro@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800',
            ],
            [
                'slug'         => 'facade-renovation-dijon',
                'shop_name'    => 'Façades & Rénovation',
                'display_name' => 'Philippe Roux',
                'metier'       => 'Façadier',
                'description'  => 'Ravalement de façades, ITE, crépi. Traitement des fissures et imperméabilisation.',
                'city'         => 'Dijon',
                'postal_code'  => '21000',
                'email'        => 'facades.renovation@abracadabati.fr',
                'cover_image'  => 'https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800',
            ],
        ];
    }
}
