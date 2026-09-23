<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectGallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectGallery>
 */
class ProjectGalleryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'image_path' => 'images/project-gallery-'.$this->faker->numberBetween(1, 5).'.jpg',
            'alt' => $this->faker->sentence(2),
            'position' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
