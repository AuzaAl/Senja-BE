<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Editor',
            'email' => 'new.editor@senja.id',
            'password' => 'secret-password',
            'roles' => ['editor'],
        ], $overrides);
    }

    public function test_admin_can_list_users(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $this->userWithRole('viewer', ['email' => 'viewer@senja.id']);

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'email', 'roles']]])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_non_admin_cannot_list_users(): void
    {
        Passport::actingAs($this->userWithRole('editor'), [], 'api');

        $this->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_guest_cannot_list_users(): void
    {
        $this->getJson('/api/v1/users')->assertUnauthorized();
    }

    public function test_admin_can_create_user_with_roles(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/users', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.email', 'new.editor@senja.id')
            ->assertJsonPath('data.roles.0', 'editor');

        $this->assertDatabaseHas('users', ['email' => 'new.editor@senja.id']);
    }

    public function test_create_user_validates_unique_email(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        User::factory()->create(['email' => 'dupe@senja.id']);

        $this->postJson('/api/v1/users', $this->payload(['email' => 'dupe@senja.id']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_update_user(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $target = $this->userWithRole('viewer');

        $this->putJson("/api/v1/users/{$target->id}", ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');
    }

    public function test_admin_can_delete_user(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $target = $this->userWithRole('viewer');

        $this->deleteJson("/api/v1/users/{$target->id}")->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->userWithRole('admin');
        Passport::actingAs($admin, [], 'api');

        $this->deleteJson("/api/v1/users/{$admin->id}")->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_assign_role(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $target = User::factory()->create();

        $this->postJson("/api/v1/users/{$target->id}/roles", ['roles' => ['editor']])
            ->assertOk()
            ->assertJsonPath('data.roles.0', 'editor');

        $this->assertTrue($target->fresh()->hasRole('editor'));
    }

    public function test_admin_can_revoke_role(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $target = $this->userWithRole('editor');

        $this->deleteJson("/api/v1/users/{$target->id}/roles", ['roles' => ['editor']])
            ->assertOk();

        $this->assertFalse($target->fresh()->hasRole('editor'));
    }

    public function test_assign_role_rejects_unknown_role(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $target = User::factory()->create();

        $this->postJson("/api/v1/users/{$target->id}/roles", ['roles' => ['ghost']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['roles.0']);
    }

    public function test_editor_cannot_assign_role(): void
    {
        Passport::actingAs($this->userWithRole('editor'), [], 'api');
        $target = User::factory()->create();

        $this->postJson("/api/v1/users/{$target->id}/roles", ['roles' => ['admin']])
            ->assertForbidden();
    }
}
