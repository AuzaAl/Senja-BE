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
        return [
            'id' => $this->id,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'button_label' => $this->button_label,
            'button_link' => $this->button_link,
            'images' => collect($this->images ?? [])
                ->map(fn (array $image): array => $this->mediaItem(
                    $image['path'] ?? null,
                    $image['alt'] ?? null,
                ))
                ->values()
                ->all(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
