<?php

namespace Tests\Feature;

use App\Models\Partner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Nusantara Creative',
            'description' => 'Mitra pengembangan brand terkemuka.',
            'website' => 'https://nusantara.example.com',
            'sort_order' => 1,
            'is_active' => true,
            'gallery' => [],
            'products' => [],
        ], $overrides);
    }

    public function test_public_partners_returns_only_active_partners(): void
    {
        $active = Partner::factory()->count(2)->create(['sort_order' => 1]);
        Partner::factory()->count(2)->inactive()->create();

        $response = $this->getJson('/api/v1/partners')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);

        $this->assertSame(
            $active->sortBy('name')->pluck('slug')->values()->all(),
            collect($response->json('data'))->pluck('slug')->values()->all(),
        );
    }

    public function test_public_partner_by_slug_returns_detail_with_gallery_and_products(): void
    {
        $partner = Partner::factory()->withGallery(2)->withProducts(2)->create();

        $this->getJson("/api/v1/partners/{$partner->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $partner->slug)
            ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'logo', 'gallery', 'products']])
            ->assertJsonCount(2, 'data.gallery')
            ->assertJsonCount(2, 'data.products');
    }

    public function test_public_partner_by_slug_hides_inactive_partner(): void
    {
        $partner = Partner::factory()->inactive()->create();

        $this->getJson("/api/v1/partners/{$partner->slug}")->assertNotFound();
    }

    public function test_public_partner_by_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/v1/partners/tidak-ada')->assertNotFound();
    }

    public function test_admin_can_create_partner_with_slug_from_name(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/partners', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Nusantara Creative')
            ->assertJsonPath('data.slug', 'nusantara-creative');

        $this->assertDatabaseHas('partners', [
            'name' => 'Nusantara Creative',
            'slug' => 'nusantara-creative',
            'is_active' => true,
        ]);
    }

    public function test_create_partner_stores_uploaded_logo_and_gallery(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/partners', $this->payload([
            'logo' => UploadedFile::fake()->image('logo.webp'),
            'gallery' => [
                ['image' => UploadedFile::fake()->image('g1.jpg'), 'alt' => 'Kantor', 'sort_order' => 0],
            ],
        ]))
            ->assertCreated()
            ->assertJsonCount(1, 'data.gallery')
            ->assertJsonStructure(['data' => ['logo' => ['path', 'url'], 'gallery' => [['path', 'url', 'alt']]]]);

        $this->assertCount(2, Storage::disk('public')->files('uploads'));
    }

    public function test_create_partner_rejects_duplicate_slug(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        Partner::factory()->create(['name' => 'Dup', 'slug' => 'nusantara-creative']);

        $this->postJson('/api/v1/partners', $this->payload(['slug' => 'nusantara-creative']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_admin_can_update_partner(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $partner = Partner::factory()->create();

        $this->putJson("/api/v1/partners/{$partner->id}", $this->payload(['name' => 'Senyap Studio']))
            ->assertOk()
            ->assertJsonPath('data.name', 'Senyap Studio');

        $this->assertDatabaseHas('partners', ['id' => $partner->id, 'name' => 'Senyap Studio']);
    }

    public function test_admin_can_delete_partner(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $partner = Partner::factory()->create();

        $this->deleteJson("/api/v1/partners/{$partner->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Partner berhasil dihapus.');

        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_editor_can_create_partner(): void
    {
        Passport::actingAs($this->userWithRole('editor'), [], 'api');

        $this->postJson('/api/v1/partners', $this->payload())->assertCreated();
    }

    public function test_viewer_cannot_create_partner(): void
    {
        Passport::actingAs($this->userWithRole('viewer'), [], 'api');

        $this->postJson('/api/v1/partners', $this->payload())->assertForbidden();
    }

    public function test_guest_cannot_create_partner(): void
    {
        $this->postJson('/api/v1/partners', $this->payload())->assertUnauthorized();
    }
}
