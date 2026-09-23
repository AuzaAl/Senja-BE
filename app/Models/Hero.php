<?php

namespace App\Models;

use Database\Factories\HeroFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Singleton hero section for the landing page.
 */
#[Fillable(['eyebrow', 'title', 'subtitle', 'button_label', 'button_link', 'images'])]
class Hero extends Model
{
    /** @use HasFactory<HeroFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
    }
}
