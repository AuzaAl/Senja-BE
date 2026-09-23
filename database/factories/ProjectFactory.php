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
            'number' => null,
            'category' => $this->faker->randomElement(['Workplace', 'F&B', 'Education', 'Hospitality']),
            'location' => $this->faker->randomElement(['Jakarta', 'Surabaya', 'Bandung', 'Bali']),
            'year' => (string) $this->faker->year(),
            'client' => $this->faker->company(),
            'featured' => $this->faker->boolean(20),
            'summary' => $this->faker->sentence(6),
            'description' => null,
            'overview' => $this->faker->paragraph(3),
            'challenge' => $this->faker->paragraph(2),
            'solution' => $this->faker->paragraph(2),
            'services' => ['Experience design', 'System integration'],
            'content' => $this->faker->paragraphs(3, true),
            'stats' => [
                ['value' => '05', 'label' => 'Integrated displays'],
                ['value' => '01', 'label' => 'Control platform'],
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
