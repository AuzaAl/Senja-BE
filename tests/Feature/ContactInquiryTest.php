<?php

namespace Tests\Feature;

use App\Mail\NewContactInquiry;
use App\Models\ContactInquiry;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ContactInquiryTest extends TestCase
{
    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'company' => 'PT Sinar',
            'phone' => '081234567890',
            'project_type' => 'Website',
            'timeline' => '3-6 bulan',
            'message' => 'Kami ingin membuat website baru.',
        ], $overrides);
    }

    public function test_public_submits_inquiry_and_sends_mail(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact-inquiries', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Andi Pratama')
            ->assertJsonPath('data.status', 'new');

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'Andi Pratama',
            'email' => 'andi@example.com',
            'status' => 'new',
        ]);

        Mail::assertSent(NewContactInquiry::class, function (NewContactInquiry $mail): bool {
            return $mail->hasTo(config('mail.from.address'))
                && $mail->inquiry->email === 'andi@example.com';
        });
    }

    public function test_public_inquiry_validates_required_fields(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact-inquiries', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    public function test_public_inquiry_validates_email_format(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact-inquiries', $this->payload(['email' => 'bukan-email']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_public_inquiry_does_not_send_mail_when_validation_fails(): void
    {
        Mail::fake();

        $this->postJson('/api/v1/contact-inquiries', $this->payload(['email' => 'bukan-email']))
            ->assertUnprocessable();

        Mail::assertNothingSent();
    }

    public function test_admin_can_list_inquiries(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        ContactInquiry::factory()->count(2)->create();

        $this->getJson('/api/v1/contact-inquiries')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_admin_can_delete_inquiry(): void
    {
        Passport::actingAs($this->userWithRole('admin'), [], 'api');
        $inquiry = ContactInquiry::factory()->create();

        $this->deleteJson("/api/v1/contact-inquiries/{$inquiry->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Inquiry berhasil dihapus.');

        $this->assertDatabaseMissing('contact_inquiries', ['id' => $inquiry->id]);
    }

    public function test_guest_cannot_list_inquiries(): void
    {
        $this->getJson('/api/v1/contact-inquiries')->assertUnauthorized();
    }

    public function test_viewer_cannot_delete_inquiry(): void
    {
        Passport::actingAs($this->userWithRole('viewer'), [], 'api');
        $inquiry = ContactInquiry::factory()->create();

        $this->deleteJson("/api/v1/contact-inquiries/{$inquiry->id}")->assertForbidden();
    }
}
