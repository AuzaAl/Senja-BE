<?php

namespace Tests\Feature;

use App\Models\About;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AboutTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'hero' => [
                'title' => 'Tentang Kami',
                'subtitle' => 'Cerita singkat studio.',
                'image' => 'uploads/about.webp',
            ],
            'story' => [
                'title' => 'Cerita kami',
                'paragraphs' => ['Kami suka membuat hal yang hebat.'],
            ],
            'quote' => ['text' => 'Kerja bagus.', 'author' => 'Tim'],
            'principles' => [
                ['title' => 'Kejelasan', 'description' => 'Jelas dan sederhana.'],
            ],
            'capabilities' => [
                ['title' => 'Desain', 'description' => 'Desain yang berdampak.'],
            ],
            'process' => [
                ['step' => 1, 'title' => 'Discover', 'description' => 'Memahami kebutuhan.'],
            ],
        ], $overrides);
    }

    public function test_public_about_returns_published_content(): void
    {
        About::factory()->create(['story' => ['title' => 'Story Publik', 'paragraphs' => []]]);

        $this->getJson('/api/v1/about')
            ->assertOk()
            ->assertJsonPath('data.story.title', 'Story Publik')
            ->assertJsonStructure(['data' => ['hero', 'story', 'quote', 'principles', 'capabilities', 'process']]);
    }

    public function test_public_about_returns_404_when_no_content(): void
    {
        $this->getJson('/api/v1/about')->assertNotFound();
    }

    public function test_admin_can_update_about(): void
    {
        About::factory()->create();
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->putJson('/api/v1/about', $this->payload(['quote' => ['text' => 'Quote baru.', 'author' => 'Bo']]))
            ->assertOk()
            ->assertJsonPath('data.quote.text', 'Quote baru.');

        $this->assertDatabaseHas('abouts', ['id' => 1]);
    }

    public function test_update_about_creates_singleton_when_missing(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->putJson('/api/v1/about', $this->payload())->assertCreated();
        $this->assertSame(1, About::count());
    }

    public function test_editor_can_update_about(): void
    {
        About::factory()->create();
        Passport::actingAs($this->userWithRole('editor'), [], 'api');

        $this->putJson('/api/v1/about', $this->payload())->assertOk();
    }

    public function test_viewer_cannot_update_about(): void
    {
        Passport::actingAs($this->userWithRole('viewer'), [], 'api');

        $this->putJson('/api/v1/about', $this->payload())->assertForbidden();
    }

    public function test_guest_cannot_update_about(): void
    {
        $this->putJson('/api/v1/about', $this->payload())->assertUnauthorized();
    }

    public function test_update_about_validates_section_structures(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->putJson('/api/v1/about', $this->payload(['story' => ['paragraphs' => 'bukan array']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['story.paragraphs']);
    }
}
