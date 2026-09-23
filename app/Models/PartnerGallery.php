<?php

namespace App\Models;

use Database\Factories\PartnerGalleryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single image in a partner's gallery.
 */
#[Fillable(['partner_id', 'image_path', 'alt', 'sort_order'])]
class PartnerGallery extends Model
{
    protected $table = 'partner_gallery';

    /** @use HasFactory<PartnerGalleryFactory> */
    use HasFactory;

    /**
     * The partner this image belongs to.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
