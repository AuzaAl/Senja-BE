<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerGallery;
use App\Models\PartnerProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => null,
            'description' => $this->faker->paragraph(2),
            'website' => $this->faker->url(),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Attach a number of gallery images.
     */
    public function withGallery(int $count = 3): static
    {
        return $this->afterCreating(function (Partner $partner) use ($count): void {
            PartnerGallery::factory()->count($count)->create(['partner_id' => $partner->id]);
        });
    }

    /**
     * Attach a number of products.
     */
    public function withProducts(int $count = 2): static
    {
        return $this->afterCreating(function (Partner $partner) use ($count): void {
            PartnerProduct::factory()->count($count)->create(['partner_id' => $partner->id]);
        });
    }

    /**
     * Mark the partner as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
