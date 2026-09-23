<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerGallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PartnerGallery>
 */
class PartnerGalleryFactory extends Factory
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
            'image_path' => 'images/partner-gallery-'.$this->faker->numberBetween(1, 5).'.jpg',
            'alt' => $this->faker->sentence(2),
            'position' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
