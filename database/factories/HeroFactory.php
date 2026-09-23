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
            'eyebrow' => 'Solusi Digital untuk Bisnis Anda',
            'title' => $this->faker->sentence(3),
            'subtitle' => $this->faker->sentence(8),
            'button_label' => 'Mulai Proyek',
            'button_link' => '/contact',
            'images' => [
                ['path' => 'images/hero-1.jpg', 'alt' => 'Hero Senja 1'],
                ['path' => 'images/hero-2.jpg', 'alt' => 'Hero Senja 2'],
            ],
        ];
    }
}
