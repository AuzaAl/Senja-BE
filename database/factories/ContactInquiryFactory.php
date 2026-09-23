<?php

namespace Database\Factories;

use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactInquiry>
 */
class ContactInquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'company' => $this->faker->company(),
            'phone' => $this->faker->phoneNumber(),
            'project_type' => $this->faker->randomElement(['Website', 'E-commerce', 'Company Profile', 'Branding']),
            'timeline' => $this->faker->randomElement(['1-2 bulan', '3-6 bulan', 'Lebih dari 6 bulan']),
            'message' => $this->faker->paragraph(2),
            'status' => 'new',
        ];
    }

    /**
     * Set a specific status.
     */
    public function status(string $status): static
    {
        return $this->state(fn (): array => ['status' => $status]);
    }
}
