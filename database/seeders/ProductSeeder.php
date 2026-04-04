<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Database\Seeder;

/**
 * Seed 20 products from AbracadaBati Emergent MOCK_PRODUITS.
 * Maps each product to an artisan shop by category.
 */
class ProductSeeder extends Seeder
{
    /**
     * Category → shop slug mapping.
     */
    private const CATEGORY_SHOP_MAP = [
        'plomberie'     => 'plomberie-martin-lyon',
        'electricite'   => 'elec-pro-marseille',
        'maconnerie'    => 'maconnerie-dupont-toulouse',
        'carrelage'     => 'carrelage-concept-nice',
        'peinture'      => 'peinture-artdeco-bordeaux',
        'menuiserie'    => 'menuiserie-bois-nantes',
        'couverture'    => 'toiture-expertise-lille',
        'chauffage'     => 'chauffage-confort-strasbourg',
        'piscine'       => 'piscine-azur-montpellier',
        'jardin'        => 'jardin-creation-rennes',
        'climatisation' => 'clim-pro-aix',
        'renovation'    => 'facade-renovation-dijon',
        'solaire'       => 'elec-pro-marseille',       // solaire → electricien
        'isolation'     => 'toiture-expertise-lille',   // isolation → couvreur
    ];

    public function run(): void
    {
        ShopProduct::truncate();

        $products = $this->getProducts();

        foreach ($products as $index => $product) {
            $shopSlug = self::CATEGORY_SHOP_MAP[$product['category']] ?? null;

            if (!$shopSlug) {
                $this->command->warn("  Skipped: no shop for category '{$product['category']}'");
                continue;
            }

            $shop = Shop::where('slug', $shopSlug)->first();

            if (!$shop) {
                $this->command->warn("  Skipped: shop '{$shopSlug}' not found — run ArtisanSeeder first");
                continue;
            }

            ShopProduct::create([
                'shop_id'     => $shop->id,
                'user_id'     => $shop->user_id,
                'name'        => $product['title'],
                'description' => $product['description'],
                'price'       => $product['price'],
                'images'      => $product['photos'],
                'category'    => $product['category'],
                'stock'       => $product['stock'],
                'is_active'   => true,
            ]);

            $this->command->info("  ✓ [{$product['category']}] {$product['title']} → {$shopSlug}");
        }

        $this->command->info('Done! ' . count($products) . ' products seeded.');
    }

    /**
     * Exact data from AbracadaBati Emergent MOCK_PRODUITS.
     */
    private function getProducts(): array
    {
        return [
            [
                'title'       => 'Perceuse visseuse sans fil Bosch Professional GSR 18V-60',
                'description' => 'Perceuse-visseuse sans fil 18V avec moteur brushless pour une longue durée de vie. Couple max 60 Nm. Livrée avec 2 batteries 4.0Ah ProCORE, chargeur rapide GAL 18V-40 et coffret L-BOXX. Idéale pour les professionnels exigeants.',
                'price'       => 289.99,
                'category'    => 'electricite',
                'stock'       => 45,
                'photos'      => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800', 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800'],
            ],
            [
                'title'       => 'Carrelage grès cérame effet béton 60x60cm - Gris anthracite',
                'description' => 'Carrelage grand format en grès cérame rectifié. Effet béton ciré contemporain. Résistant au gel et antidérapant R10. Idéal intérieur/extérieur. Vendu par lot de 1.44m² (4 carreaux).',
                'price'       => 34.90,
                'category'    => 'carrelage',
                'stock'       => 230,
                'photos'      => ['https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=800'],
            ],
            [
                'title'       => 'Peinture acrylique mate Tollens - Blanc absolu 10L',
                'description' => 'Peinture murale monocouche haute qualité. Finition mate veloutée. Excellente opacité et pouvoir couvrant (12m²/L). Séchage rapide, lessivable. Certification A+ faibles émissions.',
                'price'       => 79.90,
                'category'    => 'peinture',
                'stock'       => 120,
                'photos'      => ['https://images.unsplash.com/photo-1562259949-e8e7689d7828?w=800'],
            ],
            [
                'title'       => 'Mitigeur thermostatique douche Grohe Grohtherm 1000',
                'description' => 'Mitigeur thermostatique mural pour douche. Technologie TurboStat pour température stable. Butée de sécurité 38°C. Corps lisse pour nettoyage facile. Finition StarLight chrome brillant.',
                'price'       => 159.00,
                'category'    => 'plomberie',
                'stock'       => 67,
                'photos'      => ['https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=800'],
            ],
            [
                'title'       => 'Panneau solaire monocristallin 400W - Longi Hi-MO 5',
                'description' => 'Panneau photovoltaïque haute performance. Technologie Half-Cut pour meilleur rendement. Rendement 21.3%. Garantie produit 12 ans, garantie performance linéaire 25 ans.',
                'price'       => 189.00,
                'category'    => 'solaire',
                'stock'       => 89,
                'photos'      => ['https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800'],
            ],
            [
                'title'       => 'Scie circulaire plongeante Festool TS 55 REBQ-Plus',
                'description' => 'La référence des scies plongeantes. Moteur 1200W, profondeur de coupe 55mm. Système anti-recul FastFix. Compatible rails de guidage. Livrée en Systainer.',
                'price'       => 549.00,
                'category'    => 'menuiserie',
                'stock'       => 23,
                'photos'      => ['https://images.unsplash.com/photo-1616401784845-180882ba9ba8?w=800'],
            ],
            [
                'title'       => 'Radiateur électrique connecté Atlantic Divali 1500W',
                'description' => 'Radiateur à inertie fonte connecté. Programmation intelligente via app Cozytouch. Détection fenêtre ouverte et présence. Design épuré blanc. Garantie 3 ans.',
                'price'       => 449.00,
                'category'    => 'chauffage',
                'stock'       => 56,
                'photos'      => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800'],
            ],
            [
                'title'       => 'Échafaudage roulant aluminium - Hauteur 6m',
                'description' => 'Échafaudage professionnel en aluminium léger. Montage rapide sans outil. Plateaux antidérapants, garde-corps intégrés. Roulettes avec freins. Charge max 200kg/m².',
                'price'       => 789.00,
                'category'    => 'maconnerie',
                'stock'       => 18,
                'photos'      => ['https://images.unsplash.com/photo-1504307651254-35680f356dfd?w=800'],
            ],
            [
                'title'       => 'Robot tondeuse Husqvarna Automower 310 Mark II',
                'description' => "Robot tondeuse connecté pour surfaces jusqu'à 1000m². Navigation GPS assistée. Gestion multi-zones. App Automower Connect. Ultra silencieux 58dB.",
                'price'       => 1299.00,
                'category'    => 'jardin',
                'stock'       => 12,
                'photos'      => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'],
            ],
            [
                'title'       => 'Robot piscine Dolphin E40i - WiFi connecté',
                'description' => "Robot nettoyeur électrique pour piscines jusqu'à 12m. Nettoie fond, parois et ligne d'eau. Pilotage via smartphone. Brosses combinées PVC/mousse. Cycle 2h.",
                'price'       => 899.00,
                'category'    => 'piscine',
                'stock'       => 34,
                'photos'      => ['https://images.unsplash.com/photo-1575429198097-0414ec08e8cd?w=800'],
            ],
            [
                'title'       => 'Perforateur burineur Makita HR2470 SDS-Plus',
                'description' => 'Perforateur 3 modes : perçage, perfo-béton, burinage. Puissance 780W, énergie de frappe 2.4J. Mandrin SDS-Plus. Coffret avec jeu de forets et burins.',
                'price'       => 189.00,
                'category'    => 'electricite',
                'stock'       => 78,
                'photos'      => ['https://images.unsplash.com/photo-1504148455328-c376907d081c?w=800'],
            ],
            [
                'title'       => 'Pompe à chaleur air-eau Daikin Altherma 3 - 8kW',
                'description' => "PAC monobloc R32 haute efficacité. Chauffage + ECS + rafraîchissement. COP jusqu'à 5.1. Compatible plancher chauffant et radiateurs. Pilotage Daikin Residential Controller.",
                'price'       => 4890.00,
                'category'    => 'chauffage',
                'stock'       => 8,
                'photos'      => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800'],
            ],
            [
                'title'       => 'Coupe-carreaux électrique Rubi DC-250 1200',
                'description' => 'Table de coupe professionnelle. Longueur de coupe 102cm, diagonale 72cm. Moteur 1500W. Disque diamant 250mm inclus. Bac eau avec système de recirculation.',
                'price'       => 1249.00,
                'category'    => 'carrelage',
                'stock'       => 15,
                'photos'      => ['https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800'],
            ],
            [
                'title'       => 'Isolant laine de roche Rockwool Rockplus 200mm - R=5.7',
                'description' => 'Panneau semi-rigide pour isolation des combles perdus. Haute performance thermique R=5.7. Classement feu A1 incombustible. Pack de 3m² (2 panneaux 1200x1000mm).',
                'price'       => 42.90,
                'category'    => 'isolation',
                'stock'       => 340,
                'photos'      => ['https://images.unsplash.com/photo-1607400201889-565b1ee75f8e?w=800'],
            ],
            [
                'title'       => 'Climatiseur réversible Mitsubishi MSZ-AP35VG - 3.5kW',
                'description' => 'Unité murale inverter A+++. Technologie Plasma Quad filtration. Mode silencieux 19dB. WiFi intégré pour pilotage à distance. Garantie 5 ans pièces.',
                'price'       => 1190.00,
                'category'    => 'climatisation',
                'stock'       => 28,
                'photos'      => ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800'],
            ],
            [
                'title'       => 'Fenêtre de toit Velux GGL UK04 - 134x98cm',
                'description' => 'Fenêtre à rotation en bois massif. Double vitrage Confort isolation renforcée. Finition vernis incolore. Barre de manoeuvre en partie haute. Compatible volet roulant.',
                'price'       => 489.00,
                'category'    => 'renovation',
                'stock'       => 42,
                'photos'      => ['https://images.unsplash.com/photo-1497366216548-37526070297c?w=800'],
            ],
            [
                'title'       => 'Meuleuse angulaire Hilti AG 125-19SE - 1900W',
                'description' => 'Meuleuse professionnelle avec Active Torque Control. Disque 125mm, 1900W. Système anti-kickback. Poignée latérale AVR anti-vibrations. Coffret de transport.',
                'price'       => 329.00,
                'category'    => 'electricite',
                'stock'       => 34,
                'photos'      => ['https://images.unsplash.com/photo-1572981779307-38b8cabb2407?w=800'],
            ],
            [
                'title'       => 'Bétonnière électrique Altrad BT180 - Cuve 180L',
                'description' => 'Bétonnière professionnelle 180L de capacité de malaxage. Moteur 800W. Couronne fonte et pignons traités. Châssis renforcé avec roues. Malaxage homogène.',
                'price'       => 389.00,
                'category'    => 'maconnerie',
                'stock'       => 19,
                'photos'      => ['https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=800'],
            ],
            [
                'title'       => 'Parquet contrecollé chêne naturel 14mm - Click',
                'description' => 'Parquet 3 plis pose flottante Click. Parement chêne français 3.5mm. Finition huilée naturelle mat. Compatible chauffage au sol. Pack de 2.2m².',
                'price'       => 64.90,
                'category'    => 'menuiserie',
                'stock'       => 156,
                'photos'      => ['https://images.unsplash.com/photo-1615971677499-5467cbab01c0?w=800'],
            ],
            [
                'title'       => 'Taille-haie thermique Stihl HS 82 RC-E - 75cm',
                'description' => 'Taille-haie professionnel à lames doubles. Moteur 2-MIX 22.7cc. Lame 75cm coupe simple. Système ErgoStart démarrage facile. Poignée rotative multi-positions.',
                'price'       => 489.00,
                'category'    => 'jardin',
                'stock'       => 21,
                'photos'      => ['https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800'],
            ],
        ];
    }
}
