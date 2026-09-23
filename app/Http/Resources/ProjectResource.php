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
        $cover = $this->mediaItem($this->cover_image);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'number' => $this->number ?? str_pad((string) (($this->sort_order ?? 0) ?: $this->id), 2, '0', STR_PAD_LEFT),
            'category' => $this->category,
            'location' => $this->location,
            'year' => $this->year,
            'client' => $this->client,
            'featured' => $this->featured,
            'summary' => $this->summary,
            // FE aliases: description <- description ?? summary, image <- cover url
            'description' => $this->description ?? $this->summary,
            'overview' => $this->overview ?? $this->content,
            'challenge' => $this->challenge,
            'solution' => $this->solution,
            'services' => $this->services ?? [],
            'content' => $this->content,
            'stats' => $this->stats,
            'cover_image' => $cover,
            'image' => $cover['src'],
            'sort_order' => $this->sort_order,
            'gallery' => $this->whenLoaded('gallery', fn () => $this->mediaCollection($this->gallery)),
            'partners' => $this->whenLoaded('partners', fn () => $this->partners->map(fn ($partner): array => [
                'id' => $partner->id,
                'name' => $partner->name,
                'slug' => $partner->slug,
                'logo' => $this->mediaItem($partner->logo),
                'image' => $this->mediaUrl($partner->logo),
            ])->values()),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
