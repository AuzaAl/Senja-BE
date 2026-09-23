<?php

namespace Database\Seeders;

use App\Models\Hero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the homepage hero (singleton row).
     */
    public function run(): void
    {
        Hero::updateOrCreate(['id' => 1], [
            'eyebrow' => 'Welcome to Senja',
            'title' => 'Kami merancang pengalaman digital yang terasa hidup',
            'subtitle' => 'Senja adalah studio yang membangun brand, produk, dan kampanye untuk tim yang ambisius.',
            'button_label' => 'Mulai proyek',
            'button_link' => 'https://senja.id/contact',
            'images' => [
                ['path' => 'https://picsum.photos/seed/hero-senja/1600/900', 'alt' => 'Senja creative studio'],
                ['path' => 'https://picsum.photos/seed/hero-workspace/1600/900', 'alt' => 'Senja workspace'],
            ],
        ]);
    }
}
