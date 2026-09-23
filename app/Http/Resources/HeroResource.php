<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\SerializesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroResource extends JsonResource
{
    use SerializesMedia;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = collect($this->images ?? [])
            ->map(function ($image): array {
                // Support both object items and plain string paths from CMS.
                if (is_string($image)) {
                    $image = ['path' => $image];
                }

                $item = $this->mediaItem(
                    $image['path'] ?? $image['image_path'] ?? null,
                    $image['alt'] ?? null,
                );

                return [...$item, 'src' => $item['src']];
            })
            ->values()
            ->all();

        return [
            'id' => $this->id,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'button_label' => $this->button_label,
            'buttonLabel' => $this->button_label,
            'button_link' => $this->button_link,
            'buttonLink' => $this->button_link,
            'images' => $images,
            // FE convenience: flat string list
            'image_urls' => array_values(array_filter(array_column($images, 'src'))),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
