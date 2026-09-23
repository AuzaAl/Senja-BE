<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerProduct>
 */
class PartnerProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_id' => Partner::factory(),
            'name' => $this->faker->words(2, true),
            'category' => $this->faker->randomElement(['Professional Display', 'Collaboration', 'Network', 'Projection']),
            'description' => $this->faker->sentence(),
            'image_path' => 'images/product-'.$this->faker->numberBetween(1, 5).'.jpg',
            'link' => $this->faker->url(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
