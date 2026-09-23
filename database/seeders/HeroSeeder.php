<?php

namespace Database\Seeders;

use App\Models\Hero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the homepage hero (singleton row) — mirrors FE Hero.tsx + CMS defaultHeroContent.
     */
    public function run(): void
    {
        Hero::updateOrCreate(['id' => 1], [
            'eyebrow' => 'Smart workplace solutions',
            'title' => 'Technology that connects people, spaces & ideas',
            'subtitle' => 'We design, integrate, and support intelligent workplace solutions that enable collaboration, communication, and growth.',
            'button_label' => 'Explore our work',
            'button_link' => '#solutions',
            'images' => [
                ['path' => '/images/1.png', 'alt' => 'Senja-enabled executive meeting room with integrated displays'],
            ],
        ]);
    }
}
