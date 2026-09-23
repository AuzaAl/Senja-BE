<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\Project;
use App\Models\ProjectGallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => $this->faker->randomElement(['Website', 'E-commerce', 'Company Profile', 'Branding']),
            'featured' => $this->faker->boolean(20),
            'summary' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(3, true),
            'stats' => [
                'client' => $this->faker->company(),
                'year' => $this->faker->year(),
                'budget' => 'Rp '.$this->faker->numberBetween(100, 900).' juta',
                'duration' => $this->faker->numberBetween(1, 12).' bulan',
            ],
            'cover_image' => null,
            'sort_order' => 0,
        ];
    }

    /**
     * Attach a number of gallery images.
     */
    public function withGallery(int $count = 3): static
    {
        return $this->afterCreating(function (Project $project) use ($count): void {
            ProjectGallery::factory()->count($count)->create(['project_id' => $project->id]);
        });
    }

    /**
     * Attach partners to the project.
     */
    public function withPartners(int $count = 2): static
    {
        return $this->afterCreating(function (Project $project) use ($count): void {
            $project->partners()->attach(
                Partner::factory()->count($count)->create()->pluck('id'),
            );
        });
    }

    /**
     * Mark the project as featured.
     */
    public function featured(): static
    {
        return $this->state(fn (): array => ['featured' => true]);
    }

    /**
     * Set a fixed category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (): array => ['category' => $category]);
    }
}
