<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AuthTest extends TestCase
{
    private function fakeTokenEndpoint(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 1296000,
                'access_token' => 'fake-access-token',
                'refresh_token' => 'fake-refresh-token',
            ], 200),
        ]);
    }

    public function test_login_returns_token_and_user(): void
    {
        $this->fakeTokenEndpoint();
        $user = $this->userWithRole('admin', ['email' => 'admin@senja.id']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@senja.id',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'admin@senja.id')
            ->assertJsonPath('data.user.roles.0', 'admin')
            ->assertJsonPath('data.token.access_token', 'fake-access-token')
            ->assertJsonPath('data.token.refresh_token', 'fake-refresh-token');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->userWithRole('viewer', ['email' => 'user@senja.id']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@senja.id',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_me_returns_authenticated_user_with_roles_and_permissions(): void
    {
        $user = $this->userWithRole('editor');
        Passport::actingAs($user, [], 'api');

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.roles.0', 'editor')
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'roles', 'permissions']]);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_refresh_rotates_tokens(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response([
                'token_type' => 'Bearer',
                'expires_in' => 1296000,
                'access_token' => 'new-access-token',
                'refresh_token' => 'new-refresh-token',
            ], 200),
        ]);

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => 'old-refresh-token'])
            ->assertOk()
            ->assertJsonPath('data.token.access_token', 'new-access-token');
    }

    public function test_refresh_rejects_invalid_token(): void
    {
        Http::fake([
            '*/oauth/token' => Http::response(['error' => 'invalid_grant'], 400),
        ]);

        $this->postJson('/api/v1/auth/refresh', ['refresh_token' => 'bad'])
            ->assertUnauthorized();
    }

    public function test_logout_revokes_current_token(): void
    {
        $this->createPersonalAccessClient();

        $user = User::factory()->create();
        $token = $user->createToken('test')->getToken();

        Passport::actingAs($user, [], 'api');

        // Re-bind the persisted token AFTER actingAs (it installs a bare
        // in-memory AccessToken), so the logout controller can revoke the
        // exact database record.
        $user->withAccessToken(new AccessToken([
            'oauth_access_token_id' => $token->getKey(),
            'oauth_client_id' => $token->client_id,
            'oauth_user_id' => $user->getAuthIdentifier(),
            'oauth_scopes' => [],
        ]));

        $this->postJson('/api/v1/auth/logout')->assertOk();

        $this->assertTrue($token->fresh()->revoked);
    }
}
