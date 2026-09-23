<?php

namespace App\Models;

use Database\Factories\ProjectGalleryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single image in a project's gallery.
 */
#[Fillable(['project_id', 'image_path', 'alt', 'sort_order'])]
class ProjectGallery extends Model
{
    protected $table = 'project_gallery';

    /** @use HasFactory<ProjectGalleryFactory> */
    use HasFactory;

    /**
     * The project this image belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
