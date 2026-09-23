<?php

namespace App\Models;

use Database\Factories\AboutFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Singleton About page content stored as nested JSON sections:
 * hero, story, quote, principles, capabilities, process.
 */
#[Fillable(['hero', 'story', 'quote', 'principles', 'capabilities', 'process'])]
class About extends Model
{
    /** @use HasFactory<AboutFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hero' => 'array',
            'story' => 'array',
            'quote' => 'array',
            'principles' => 'array',
            'capabilities' => 'array',
            'process' => 'array',
        ];
    }
}
