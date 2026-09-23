<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@senja.id'],
            [
                'name' => 'Senja Admin',
                'password' => Hash::make('password'),
            ],
        );

        $admin->assignRole('admin');

        $this->call([
            HeroSeeder::class,
            AboutSeeder::class,
            PartnerSeeder::class,
            ProjectSeeder::class,
        ]);
    }
}
