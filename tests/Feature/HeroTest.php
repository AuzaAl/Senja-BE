<?php

namespace Tests\Feature;

use App\Models\Hero;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class HeroTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'eyebrow' => 'Solusi Digital',
            'title' => 'Bangun Brand yang Berkesan',
            'subtitle' => 'Kami bantu bisnis Anda tampil lebih baik.',
            'button_label' => 'Mulai Proyek',
            'button_link' => 'https://example.com/contact',
            'images' => [
                ['image_path' => 'uploads/hero-1.webp', 'alt' => 'Slide 1'],
            ],
        ], $overrides);
    }

    public function test_public_hero_returns_published_content(): void
    {
        Hero::factory()->create(['title' => 'Judul Hero Publik']);

        $this->getJson('/api/v1/hero')
            ->assertOk()
            ->assertJsonPath('data.title', 'Judul Hero Publik')
            ->assertJsonStructure(['data' => ['eyebrow', 'subtitle', 'button_label', 'button_link', 'images']]);
    }

    public function test_public_hero_returns_404_when_no_content(): void
    {
        $this->getJson('/api/v1/hero')->assertNotFound();
    }

    public function test_admin_can_update_hero_and_hard_saves_files(): void
    {
        Storage::fake('public');
        Hero::factory()->create();
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->putJson('/api/v1/hero', $this->payload([
            'images' => [
                ['image' => UploadedFile::fake()->image('slide.webp'), 'alt' => 'Baru'],
            ],
        ]))
            ->assertOk()
            ->assertJsonStructure(['data' => ['id', 'title', 'images']]);

        $this->assertDatabaseHas('heroes', ['title' => 'Bangun Brand yang Berkesan']);
        $this->assertCount(1, Storage::disk('public')->files('uploads'));
    }

    public function test_update_hero_creates_singleton_when_missing(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->putJson('/api/v1/hero', $this->payload())->assertCreated();
        $this->assertSame(1, Hero::count());
    }

    public function test_editor_can_update_hero(): void
    {
        Hero::factory()->create();
        Passport::actingAs($this->userWithRole('editor'), [], 'api');

        $this->putJson('/api/v1/hero', $this->payload())->assertOk();
    }

    public function test_viewer_cannot_update_hero(): void
    {
        Passport::actingAs($this->userWithRole('viewer'), [], 'api');

        $this->putJson('/api/v1/hero', $this->payload())->assertForbidden();
    }

    public function test_guest_cannot_update_hero(): void
    {
        $this->putJson('/api/v1/hero', $this->payload())->assertUnauthorized();
    }

    public function test_update_hero_validates_title(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->putJson('/api/v1/hero', $this->payload(['title' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }
}
