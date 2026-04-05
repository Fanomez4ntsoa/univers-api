<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seed 20 listings from AbracadaBati Emergent MOCK_ANNONCES.
 * Distributes listings across existing artisan users in round-robin.
 */
class ListingSeeder extends Seeder
{
    public function run(): void
    {
        Listing::truncate();

        $users = User::where('user_type', 'professionnel')->get();

        if ($users->isEmpty()) {
            $this->command->warn('No artisan users found — run ArtisanSeeder first.');
            return;
        }

        $listings = $this->getListings();

        foreach ($listings as $index => $listing) {
            $user = $users[$index % $users->count()];

            $conditionMap = [
                'Neuf'           => 'new',
                'Très bon état'  => 'refurbished',
                'Bon état'       => 'used',
            ];

            $categoryMap = [
                'electricite'  => 'outils',
                'carrelage'    => 'materiaux',
                'maconnerie'   => 'equipements',
                'jardin'       => 'outils',
                'chauffage'    => 'equipements',
                'peinture'     => 'materiaux',
                'menuiserie'   => 'outils',
                'solaire'      => 'equipements',
                'plomberie'    => 'materiaux',
                'piscine'      => 'equipements',
                'renovation'   => 'materiaux',
            ];

            Listing::create([
                'user_id'    => $user->id,
                'title'      => $listing['title'],
                'description' => $listing['description'],
                'price'      => $listing['price'],
                'price_type' => $listing['price_negotiable'] ? 'negotiable' : 'fixed',
                'category'   => $categoryMap[$listing['category']] ?? 'occasion',
                'condition'  => $conditionMap[$listing['condition']] ?? 'used',
                'city'       => $listing['city'],
                'images'     => $listing['photos'],
                'status'     => 'active',
                'expires_at' => now()->addDays(30),
            ]);

            $this->command->info("  ✓ {$listing['title']} ({$listing['city']}) → {$user->display_name}");
        }

        $this->command->info('Done! ' . count($listings) . ' listings seeded.');
    }

    /**
     * Exact data from AbracadaBati Emergent MOCK_ANNONCES.
     */
    private function getListings(): array
    {
        return [
            [
                'title'            => 'Perceuse visseuse Makita 18V - Excellent état',
                'description'      => 'Perceuse visseuse sans fil Makita DDF484 18V LXT. Utilisée quelques fois seulement, comme neuve. Livrée avec 2 batteries 5Ah, chargeur rapide et coffret Makpac. Parfaite pour bricoleurs exigeants.',
                'price'            => 189,
                'price_negotiable' => true,
                'condition'        => 'Très bon état',
                'category'         => 'electricite',
                'city'             => 'Lyon',
                'photos'           => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800', 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800'],
            ],
            [
                'title'            => 'Lot carrelage imitation bois 45m² - Neuf',
                'description'      => 'Carrelage grès cérame imitation parquet chêne naturel. Format 20x120cm, épaisseur 9mm. Reste de chantier, jamais posé, encore emballé. Antidérapant R10, idéal pièces humides.',
                'price'            => 650,
                'price_negotiable' => true,
                'condition'        => 'Neuf',
                'category'         => 'carrelage',
                'city'             => 'Marseille',
                'photos'           => ['https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=800'],
            ],
            [
                'title'            => 'Échafaudage aluminium complet - Hauteur 8m',
                'description'      => "Échafaudage professionnel en aluminium, hauteur max 8m. Plateaux, garde-corps, escaliers, roulettes avec freins. Conforme aux normes. Démonté, prêt à emporter. Facture d'origine disponible.",
                'price'            => 1200,
                'price_negotiable' => false,
                'condition'        => 'Bon état',
                'category'         => 'maconnerie',
                'city'             => 'Toulouse',
                'photos'           => ['https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800'],
            ],
            [
                'title'            => 'Robot tondeuse Husqvarna 315X - Comme neuf',
                'description'      => "Robot tondeuse Husqvarna Automower 315X. Surface jusqu'à 1600m². GPS, connexion smartphone, coupe silencieuse. Utilisé 1 saison. Câble périphérique et station de charge inclus.",
                'price'            => 1450,
                'price_negotiable' => true,
                'condition'        => 'Très bon état',
                'category'         => 'jardin',
                'city'             => 'Bordeaux',
                'photos'           => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'],
            ],
            [
                'title'            => 'Tableau électrique Schneider pré-câblé 4 rangées',
                'description'      => 'Tableau électrique Schneider Resi9 4 rangées, 52 modules. Pré-équipé disjoncteurs, différentiels 30mA. Conforme NF C 15-100. Parfait pour rénovation complète.',
                'price'            => 320,
                'price_negotiable' => false,
                'condition'        => 'Neuf',
                'category'         => 'electricite',
                'city'             => 'Nantes',
                'photos'           => ['https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800'],
            ],
            [
                'title'            => 'Pompe à chaleur Daikin Altherma 3 - 8kW',
                'description'      => 'PAC air/eau Daikin Altherma 3, puissance 8kW. Chauffage + ECS. Compresseur R32. Déposée lors rénovation, fonctionne parfaitement. Documentation et facture origine.',
                'price'            => 2800,
                'price_negotiable' => true,
                'condition'        => 'Bon état',
                'category'         => 'chauffage',
                'city'             => 'Lille',
                'photos'           => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800'],
            ],
            [
                'title'            => 'Lot peinture Tollens blanc mat 80L',
                'description'      => 'Peinture murale Tollens Idrotop blanc mat. 8 pots de 10L neufs jamais ouverts. Reste de chantier professionnel. Monocouche, lessivable, rendement 12m²/L.',
                'price'            => 280,
                'price_negotiable' => true,
                'condition'        => 'Neuf',
                'category'         => 'peinture',
                'city'             => 'Strasbourg',
                'photos'           => ['https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=800'],
            ],
            [
                'title'            => 'Scie circulaire Festool TS 55 REQ + Rail',
                'description'      => 'Scie plongeante Festool TS 55 REBQ-Plus avec rail de guidage 1400mm. Systainer, lame neuve 48 dents. Aspiration intégrée. La référence pour coupes parfaites.',
                'price'            => 520,
                'price_negotiable' => false,
                'condition'        => 'Très bon état',
                'category'         => 'menuiserie',
                'city'             => 'Nice',
                'photos'           => ['https://images.unsplash.com/photo-1616401784845-180882ba9ba8?w=800'],
            ],
            [
                'title'            => 'Kit panneaux solaires 3kWc complet',
                'description'      => 'Kit photovoltaïque complet : 8 panneaux 375Wc monocristallins + onduleur Enphase + fixations toiture tuiles. Installation facile. Garantie 25 ans panneaux.',
                'price'            => 3200,
                'price_negotiable' => true,
                'condition'        => 'Neuf',
                'category'         => 'solaire',
                'city'             => 'Montpellier',
                'photos'           => ['https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800'],
            ],
            [
                'title'            => 'Bétonnière électrique 160L Altrad',
                'description'      => 'Bétonnière électrique Altrad BT160, cuve 160L. Moteur 650W. Châssis renforcé, roues. Parfait pour travaux de maçonnerie. Utilisée sur 2 chantiers.',
                'price'            => 280,
                'price_negotiable' => true,
                'condition'        => 'Bon état',
                'category'         => 'maconnerie',
                'city'             => 'Rennes',
                'photos'           => ['https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800'],
            ],
            [
                'title'            => 'Radiateur électrique Atlantic 2000W x4',
                'description'      => 'Lot 4 radiateurs Atlantic Nirvana Neo 2000W. Connectés, programmables, détection fenêtre ouverte. Déposés suite changement chauffage. Comme neufs.',
                'price'            => 800,
                'price_negotiable' => true,
                'condition'        => 'Très bon état',
                'category'         => 'chauffage',
                'city'             => 'Grenoble',
                'photos'           => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800'],
            ],
            [
                'title'            => 'Perforateur Bosch GBH 18V-26 + Batteries',
                'description'      => 'Perforateur sans fil Bosch Professional GBH 18V-26 F. Mode perçage, perfo, burinage. 2 batteries ProCore 8Ah, chargeur GAL 18V-160 C. Coffret L-BOXX.',
                'price'            => 450,
                'price_negotiable' => false,
                'condition'        => 'Bon état',
                'category'         => 'electricite',
                'city'             => 'Dijon',
                'photos'           => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800'],
            ],
            [
                'title'            => 'Robot piscine Dolphin E35 - Garantie',
                'description'      => "Robot nettoyeur piscine Dolphin E35. Fond, parois, ligne d'eau. Cycle 2h30. Télécommande incluse. Utilisé 2 saisons, révisé. Garantie transférable 1 an.",
                'price'            => 680,
                'price_negotiable' => true,
                'condition'        => 'Bon état',
                'category'         => 'piscine',
                'city'             => 'Aix-en-Provence',
                'photos'           => ['https://images.unsplash.com/photo-1575429198097-0414ec08e8cd?w=800'],
            ],
            [
                'title'            => 'Velux GGL 114x118 + Store occultant',
                'description'      => 'Fenêtre de toit Velux GGL MK06 bois, 114x118cm. Double vitrage Confort. Volet roulant solaire SSL + store occultant DKL. Jamais posés, emballage origine.',
                'price'            => 890,
                'price_negotiable' => false,
                'condition'        => 'Neuf',
                'category'         => 'renovation',
                'city'             => 'Le Havre',
                'photos'           => ['https://images.unsplash.com/photo-1497366216548-37526070297c?w=800'],
            ],
            [
                'title'            => 'Meuleuse Hilti AG 125-A36 + Batterie',
                'description'      => "Meuleuse d'angle sans fil Hilti 36V, disque 125mm. Batterie B36/5.2 Li-Ion + chargeur C4/36-350. Protection anti-kickback. Très peu servie.",
                'price'            => 380,
                'price_negotiable' => true,
                'condition'        => 'Très bon état',
                'category'         => 'electricite',
                'city'             => 'Toulon',
                'photos'           => ['https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800'],
            ],
            [
                'title'            => 'Isolation laine de roche 200mm - 35m²',
                'description'      => 'Panneaux laine de roche Rockwool 200mm. R=5.7. Reste chantier isolation combles. 7 paquets neufs sous film. Classement feu A1.',
                'price'            => 420,
                'price_negotiable' => true,
                'condition'        => 'Neuf',
                'category'         => 'renovation',
                'city'             => 'Clermont-Ferrand',
                'photos'           => ['https://images.unsplash.com/photo-1607400201889-565b1ee75f8e?w=800'],
            ],
            [
                'title'            => 'Robinetterie Grohe Essence - Lot cuisine + SDB',
                'description'      => "Mitigeur évier Grohe Essence + Mitigeur lavabo + Colonne douche Rainshower. Finition chromée. Neufs dans emballages. Prix boutique > 1200\u20ac.",
                'price'            => 580,
                'price_negotiable' => true,
                'condition'        => 'Neuf',
                'category'         => 'plomberie',
                'city'             => 'Angers',
                'photos'           => ['https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800'],
            ],
            [
                'title'            => 'Coupe-carreaux électrique Rubi DU-200 EVO',
                'description'      => "Coupe-carreaux sur table Rubi DU-200 EVO, coupe jusqu'à 52cm. Laser intégré, bac eau. Table inclinable 45°. État impeccable, peu utilisé.",
                'price'            => 650,
                'price_negotiable' => false,
                'condition'        => 'Très bon état',
                'category'         => 'carrelage',
                'city'             => 'Metz',
                'photos'           => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'],
            ],
            [
                'title'            => 'Débroussailleuse Stihl FS 131 - Pro',
                'description'      => 'Débroussailleuse thermique Stihl FS 131, moteur 4-MIX 36.3cc. Harnais ErgoStart, guidon vélo. Tête fil + lame 3 dents. Entretien concessionnaire.',
                'price'            => 420,
                'price_negotiable' => true,
                'condition'        => 'Bon état',
                'category'         => 'jardin',
                'city'             => 'Perpignan',
                'photos'           => ['https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800'],
            ],
            [
                'title'            => 'Chauffe-eau thermodynamique Atlantic 250L',
                'description'      => 'Chauffe-eau thermodynamique Atlantic Calypso 250L. COP 3.2. Installé 2 ans, démonté suite déménagement. Parfait état de marche. Mode boost.',
                'price'            => 950,
                'price_negotiable' => true,
                'condition'        => 'Bon état',
                'category'         => 'plomberie',
                'city'             => 'Caen',
                'photos'           => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800'],
            ],
        ];
    }
}
