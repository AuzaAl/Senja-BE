<?php

namespace App\Models;

use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A business partner shown on the landing page, with a gallery and products.
 */
#[Fillable(['name', 'slug', 'logo', 'description', 'website', 'sort_order', 'is_active'])]
class Partner extends Model
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory;

    /**
     * Only active partners are exposed through the public endpoints.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Image gallery belonging to this partner.
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(PartnerGallery::class)->orderBy('sort_order');
    }

    /**
     * Products offered by this partner.
     */
    public function products(): HasMany
    {
        return $this->hasMany(PartnerProduct::class)->orderBy('sort_order');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
