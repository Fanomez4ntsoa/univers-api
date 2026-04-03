<?php

namespace Database\Seeders;

use App\Models\FaqItem;
use Illuminate\Database\Seeder;

class FaqItemSeeder extends Seeder
{
    public function run(): void
    {
        FaqItem::truncate();

        $faqs = [
            [
                'question' => 'Comment fonctionne BatiAssist ?',
                'answer'   => 'BatiAssist combine un CRM batiment complet, 7 agents IA specialises et des assistants humains pour gerer ton entreprise. Les agents travaillent 24h/24 sur les taches repetitives, supervises par un assistant humain qui coordonne le tout.',
                'category' => 'general',
                'order'    => 1,
            ],
            [
                'question' => 'Que font les agents IA ?',
                'answer'   => 'Les 7 agents IA gerent : le standard telephonique, le service client (messagerie BatiAssist, SMS), les emails, le marketing sur les reseaux sociaux, le SEO et le blog, la prospection, et les taches administratives. Ils travaillent en permanence, 24h/24.',
                'category' => 'fonctionnement',
                'order'    => 2,
            ],
            [
                'question' => "Que fait l'assistant humain ?",
                'answer'   => 'Ton assistant humain supervise les agents IA, verifie leur travail, gere les cas complexes, et communique directement avec toi via la messagerie BatiAssist integree. Il coordonne toutes les actions pour ton entreprise.',
                'category' => 'fonctionnement',
                'order'    => 3,
            ],
            [
                'question' => 'Combien coute BatiAssist ?',
                'answer'   => "Quatre formules : Start a 149\u20ac/mois (CRM + agents IA), Assistant Mi-Temps a 499\u20ac/mois (+assistant 20h), Assistant Dedie a 949\u20ac/mois (+assistant 40h), Croissance a 1499\u20ac/mois (+2 assistants et prospection). Frais de mise en place : 500\u20ac.",
                'category' => 'tarifs',
                'order'    => 4,
            ],
            [
                'question' => 'Y a-t-il un engagement ?',
                'answer'   => 'Non, BatiAssist fonctionne sous forme d\'abonnement flexible sans engagement longue duree. Tu peux ajuster ou arreter a tout moment.',
                'category' => 'tarifs',
                'order'    => 5,
            ],
            [
                'question' => 'Quand le service commence-t-il ?',
                'answer'   => 'Apres la validation de l\'inscription, une phase de preparation d\'environ 15 jours est necessaire pour configurer ton CRM, activer les agents IA et former ton assistant (si applicable).',
                'category' => 'general',
                'order'    => 6,
            ],
            [
                'question' => 'Le CRM est-il adapte a mon metier ?',
                'answer'   => 'Oui, BatiAssist a ete concu specifiquement pour les artisans du batiment : plombiers, electriciens, macons, piscinistes, carreleurs, menuisiers, peintres, couvreurs et artisans multiservices.',
                'category' => 'fonctionnement',
                'order'    => 7,
            ],
            [
                'question' => 'Mes donnees sont-elles protegees ?',
                'answer'   => "Oui, toutes les informations de ton entreprise sont traitees de maniere confidentielle avec des protocoles de securite stricts. Tes donn\u00e9es restent ta propri\u00e9t\u00e9.",
                'category' => 'securite',
                'order'    => 8,
            ],
        ];

        foreach ($faqs as $faq) {
            FaqItem::create($faq);
        }
    }
}
