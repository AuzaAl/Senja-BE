<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\SerializesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    use SerializesMedia;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logo = $this->mediaItem($this->logo);
        $hero = $this->mediaItem($this->hero_image ?? null);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'number' => $this->number ?? str_pad((string) (($this->sort_order ?? 0) ?: $this->id), 2, '0', STR_PAD_LEFT),
            'category' => $this->category,
            'logo' => $logo,
            // FE aliases
            'image' => $logo['src'],
            'heroImage' => $hero['src'],
            'hero_image' => $hero,
            'description' => $this->description,
            'capabilities' => $this->capabilities ?? [],
            'relationship' => $this->relationship,
            'relationshipDetail' => $this->relationship_detail,
            'relationship_detail' => $this->relationship_detail,
            'website' => $this->website,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'gallery' => $this->whenLoaded('gallery', fn () => $this->mediaCollection($this->gallery)),
            'products' => $this->whenLoaded('products', fn () => $this->products->map(function ($product): array {
                $image = $this->mediaItem($product->image_path);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'description' => $product->description,
                    'image' => $image['src'] ?? $image,
                    'image_detail' => $image,
                    'link' => $product->link,
                    'sort_order' => $product->sort_order,
                ];
            })->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
