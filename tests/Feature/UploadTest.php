<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class UploadTest extends TestCase
{
    public function test_admin_can_upload_image(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/uploads', [
            'image' => UploadedFile::fake()->image('logo.webp')->size(200),
        ])
            ->assertCreated()
            ->assertJsonStructure(['data' => ['path', 'url']])
            ->assertJsonPath('data.path', fn (string $path): bool => str_ends_with($path, '.webp'))
            ->assertJsonPath('data.url', fn (string $url): bool => str_contains($url, '/storage/'));

        $files = Storage::disk('public')->files('uploads');
        $this->assertCount(1, $files);
        $this->assertTrue(Storage::disk('public')->exists($files[0]));
    }

    public function test_editor_can_upload_image(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('editor'), [], 'api');

        $this->postJson('/api/v1/uploads', [
            'image' => UploadedFile::fake()->image('cover.jpg')->size(100),
        ])->assertCreated();
    }

    public function test_upload_rejects_non_image(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/uploads', [
            'image' => UploadedFile::fake()->create('document.txt', 10, 'text/plain'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);

        $this->assertEmpty(Storage::disk('public')->files('uploads'));
    }

    public function test_upload_rejects_oversized_image(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/uploads', [
            'image' => UploadedFile::fake()->image('big.png')->size(6000),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_upload_requires_image(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');

        $this->postJson('/api/v1/uploads', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_guest_cannot_upload(): void
    {
        $this->postJson('/api/v1/uploads', [
            'image' => UploadedFile::fake()->image('logo.webp')->size(100),
        ])->assertUnauthorized();
    }

    public function test_viewer_cannot_upload(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->userWithRole('viewer'), [], 'api');

        $this->postJson('/api/v1/uploads', [
            'image' => UploadedFile::fake()->image('logo.webp')->size(100),
        ])->assertForbidden();
    }
}
