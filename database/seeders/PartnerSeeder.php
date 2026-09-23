<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed sample partners with galleries and products.
     */
    public function run(): void
    {
        if (Partner::exists()) {
            return;
        }

        Partner::factory()
            ->count(6)
            ->sequence(fn ($seq) => ['sort_order' => $seq->index + 1])
            ->withGallery(3)
            ->withProducts(2)
            ->create();
    }
}
