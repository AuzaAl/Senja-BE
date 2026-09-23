<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\SerializesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'featured' => $this->featured,
            'summary' => $this->summary,
            'content' => $this->content,
            'stats' => $this->stats,
            'cover_image' => $this->mediaItem($this->cover_image),
            'sort_order' => $this->sort_order,
            'gallery' => $this->whenLoaded('gallery', fn () => $this->mediaCollection($this->gallery)),
            'partners' => $this->whenLoaded('partners', fn () => $this->partners->map(fn ($partner): array => [
                'id' => $partner->id,
                'name' => $partner->name,
                'slug' => $partner->slug,
                'logo' => $this->mediaItem($partner->logo),
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
