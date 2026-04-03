<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::truncate();

        $testimonials = [
            [
                'name'       => 'Pierre M.',
                'role'       => 'Artisan plombier',
                'company'    => null,
                'city'       => 'Lyon',
                'avatar_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&h=100&fit=crop',
                'content'    => "Depuis que j'ai BatiAssist, je ne rate plus aucun appel. Mon assistante gère tout et je peux enfin me concentrer sur mes chantiers.",
                'rating'     => 5,
                'is_active'  => true,
            ],
            [
                'name'       => 'Sophie L.',
                'role'       => 'Architecte d\'intérieur',
                'company'    => null,
                'city'       => 'Paris',
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop',
                'content'    => "L'équipe digitale gère mes réseaux sociaux et mon blog. Ma visibilité a explosé et je reçois beaucoup plus de demandes qualifiées.",
                'rating'     => 5,
                'is_active'  => true,
            ],
            [
                'name'       => 'Marc D.',
                'role'       => 'Entrepreneur BTP',
                'company'    => null,
                'city'       => 'Marseille',
                'avatar_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop',
                'content'    => "Grâce à la machine à nouveaux clients, j'ai signé 3 nouveaux chantiers le premier mois. C'est un investissement qui rapporte.",
                'rating'     => 5,
                'is_active'  => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }
    }
}
