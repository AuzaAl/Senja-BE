<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Passport needs a key pair to sign/verify test tokens.
        Passport::loadKeysFrom(storage_path());

        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Create a user with the given role.
     */
    protected function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    /**
     * Register a password-grant client so the login controller can be
     * exercised without hitting a real HTTP endpoint.
     */
    protected function fakePasswordClient(): Client
    {
        return Passport::clientModel()::create([
            'name' => 'Test Password Client',
            'secret' => 'test-secret',
            'provider' => 'users',
            'redirect_uris' => [],
            'grant_types' => ['password', 'refresh_token'],
            'revoked' => false,
        ]);
    }

    /**
     * Register a personal access client so personal access tokens can be
     * minted in tests (used to exercise token revocation on logout).
     */
    protected function createPersonalAccessClient(): Client
    {
        return Passport::clientModel()::create([
            'name' => 'Test Personal Access Client',
            'secret' => 'test-personal-secret',
            'provider' => 'users',
            'redirect_uris' => [],
            'grant_types' => ['personal_access'],
            'revoked' => false,
        ]);
    }
}
