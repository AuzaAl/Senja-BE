<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Rebrand Senja',
            'category' => 'Branding',
            'featured' => true,
            'summary' => 'Perombakan identitas visual.',
            'content' => 'Konten lengkap proyek.',
            'stats' => [
                'client' => 'PT Sinar',
                'year' => 2026,
                'budget' => 'Rp 250 juta',
                'duration' => '4 bulan',
            ],
            'sort_order' => 1,
            'gallery' => [],
            'partners' => [],
        ], $overrides);
    }

    public function test_public_projects_lists_all(): void
    {
        Project::factory()->count(3)->create();

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3);
    }

    public function test_public_projects_filters_by_category(): void
    {
        Project::factory()->count(2)->category('Website')->create();
        Project::factory()->count(3)->category('Branding')->create();

        $this->getJson('/api/v1/projects?category=Branding')
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.category', 'Branding');
    }

    public function test_public_projects_filters_by_featured(): void
    {
        Project::factory()->featured()->count(2)->create();
        Project::factory()->count(3)->create(['featured' => false]);

        $this->getJson('/api/v1/projects?featured=1')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }

    public function test_public_project_by_slug_returns_detail_with_gallery_and_partners(): void
    {
        $project = Project::factory()->withGallery(3)->withPartners(2)->create();

        $this->getJson("/api/v1/projects/{$project->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $project->slug)
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'category', 'featured', 'summary', 'content', 'stats', 'cover_image', 'gallery', 'partners']])
            ->assertJsonCount(3, 'data.gallery')
            ->assertJsonCount(2, 'data.partners');
    }

    public function test_public_project_by_unknown_slug_returns_404(): void
    {
        $this->getJson('/api/v1/projects/tidak-ada')->assertNotFound();
    }

    public function test_admin_can_create_project_with_slug_from_title(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/projects', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Rebrand Senja')
            ->assertJsonPath('data.slug', 'rebrand-senja');

        $this->assertDatabaseHas('projects', [
            'title' => 'Rebrand Senja',
            'slug' => 'rebrand-senja',
            'category' => 'Branding',
        ]);
    }

    public function test_create_project_stores_uploaded_cover_and_gallery(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/projects', $this->payload([
            'cover_image' => UploadedFile::fake()->image('cover.webp'),
            'gallery' => [
                ['image' => UploadedFile::fake()->image('g1.jpg'), 'alt' => 'Mockup', 'sort_order' => 0],
            ],
            'partners' => [Partner::factory()->create()->id],
        ]))
            ->assertCreated()
            ->assertJsonCount(1, 'data.gallery')
            ->assertJsonCount(1, 'data.partners')
            ->assertJsonStructure(['data' => ['cover_image' => ['path', 'url']]]);

        $this->assertCount(2, Storage::disk('public')->files('uploads'));
    }

    public function test_create_project_rejects_duplicate_slug(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        Project::factory()->create(['title' => 'Dup', 'slug' => 'rebrand-senja']);

        $this->postJson('/api/v1/projects', $this->payload(['slug' => 'rebrand-senja']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_admin_can_update_project(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $project = Project::factory()->create();

        $this->putJson("/api/v1/projects/{$project->slug}", $this->payload(['title' => 'Judul Baru']))
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul Baru');

        $this->assertDatabaseHas('projects', ['id' => $project->id, 'title' => 'Judul Baru']);
    }

    public function test_admin_can_delete_project(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $project = Project::factory()->create();

        $this->deleteJson("/api/v1/projects/{$project->slug}")
            ->assertOk()
            ->assertJsonPath('message', 'Proyek berhasil dihapus.');

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_editor_can_create_project(): void
    {
        Passport::actingAs($this->userWithRole('editor'), [], 'api');

        $this->postJson('/api/v1/projects', $this->payload())->assertCreated();
    }

    public function test_viewer_cannot_create_project(): void
    {
        Passport::actingAs($this->userWithRole('viewer'), [], 'api');

        $this->postJson('/api/v1/projects', $this->payload())->assertForbidden();
    }

    public function test_guest_cannot_create_project(): void
    {
        $this->postJson('/api/v1/projects', $this->payload())->assertUnauthorized();
    }
}
