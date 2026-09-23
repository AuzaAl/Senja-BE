<?php

namespace Database\Factories;

use App\Models\About;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<About>
 */
class AboutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hero' => [
                'title' => $this->faker->sentence(4),
                'subtitle' => $this->faker->sentence(8),
                'image' => 'images/about-hero.jpg',
            ],
            'story' => [
                'title' => 'Kisah Kami',
                'paragraphs' => [
                    $this->faker->paragraph(2),
                    $this->faker->paragraph(2),
                ],
            ],
            'quote' => [
                'text' => $this->faker->sentence(10),
                'author' => $this->faker->name(),
            ],
            'principles' => [
                ['title' => 'Integritas', 'description' => $this->faker->sentence()],
                ['title' => 'Kolaborasi', 'description' => $this->faker->sentence()],
            ],
            'capabilities' => [
                ['title' => 'Konsultasi', 'description' => $this->faker->sentence()],
                ['title' => 'Desain', 'description' => $this->faker->sentence()],
            ],
            'process' => [
                ['step' => 1, 'title' => 'Diskusi', 'description' => $this->faker->sentence()],
                ['step' => 2, 'title' => 'Riset', 'description' => $this->faker->sentence()],
                ['step' => 3, 'title' => 'Eksekusi', 'description' => $this->faker->sentence()],
            ],
        ];
    }
}
