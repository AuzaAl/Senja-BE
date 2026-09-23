<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed sample projects with galleries and partner links.
     */
    public function run(): void
    {
        if (Project::exists()) {
            return;
        }

        Project::factory()
            ->count(8)
            ->sequence(fn ($seq) => [
                'sort_order' => $seq->index + 1,
                'featured' => $seq->index < 2,
            ])
            ->withGallery(4)
            ->withPartners(2)
            ->create();
    }
}
