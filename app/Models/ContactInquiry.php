<?php

namespace App\Models;

use Database\Factories\ContactInquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A contact form submission from the landing page.
 */
#[Fillable(['name', 'email', 'company', 'phone', 'project_type', 'timeline', 'message', 'status'])]
class ContactInquiry extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    /** @use HasFactory<ContactInquiryFactory> */
    use HasFactory;
}
