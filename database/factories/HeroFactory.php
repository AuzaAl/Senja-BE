<?php

namespace Database\Factories;

use App\Models\Hero;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hero>
 */
class HeroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eyebrow' => 'Smart workplace solutions',
            'title' => 'Technology that connects people, spaces & ideas',
            'subtitle' => 'We design, integrate, and support intelligent workplace solutions that enable collaboration, communication, and growth.',
            'button_label' => 'Explore our work',
            'button_link' => '#solutions',
            'images' => [
                ['path' => '/images/1.png', 'alt' => 'Senja-enabled executive meeting room with integrated displays'],
            ],
        ];
    }
}
