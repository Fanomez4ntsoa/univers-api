<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TestimonialSeeder::class,
            FaqItemSeeder::class,
            SiteStatSeeder::class,
            ArtisanSeeder::class,
            ProductSeeder::class,
            ListingSeeder::class,
        ]);
    }
}
