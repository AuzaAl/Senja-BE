<?php

namespace App\Models;

use Database\Factories\PartnerProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A product offered by a partner.
 */
#[Fillable(['partner_id', 'name', 'description', 'image_path', 'link', 'sort_order'])]
class PartnerProduct extends Model
{
    /** @use HasFactory<PartnerProductFactory> */
    use HasFactory;

    /**
     * The partner this product belongs to.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
