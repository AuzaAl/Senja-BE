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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->mediaItem($this->logo),
            'description' => $this->description,
            'website' => $this->website,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'gallery' => $this->whenLoaded('gallery', fn () => $this->mediaCollection($this->gallery)),
            'products' => $this->whenLoaded('products', fn () => $this->products->map(fn ($product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'image' => $this->mediaItem($product->image_path),
                'link' => $product->link,
                'sort_order' => $product->sort_order,
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
