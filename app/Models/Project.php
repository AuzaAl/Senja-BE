<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A project showcased on the landing page, with stats, partner references,
 * and an image gallery.
 */
#[Fillable(['title', 'slug', 'category', 'featured', 'summary', 'content', 'stats', 'cover_image', 'sort_order'])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Image gallery belonging to this project.
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(ProjectGallery::class)->orderBy('sort_order');
    }

    /**
     * Partners involved in this project.
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'project_partner');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
            'stats' => 'array',
        ];
    }
}
