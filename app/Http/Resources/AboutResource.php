<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\SerializesMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutResource extends JsonResource
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
            'hero' => $this->aboutSection($this->hero),
            'story' => $this->story,
            'quote' => $this->quote,
            'principles' => $this->principles,
            'capabilities' => $this->capabilities,
            'process' => $this->process,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Serialize the About hero section, appending the image URL.
     *
     * @param  array<string, mixed>|null  $hero
     * @return array<string, mixed>|null
     */
    private function aboutSection(?array $hero): ?array
    {
        if ($hero === null) {
            return null;
        }

        return [
            ...$hero,
            'url' => $this->mediaUrl($hero['image'] ?? null),
        ];
    }
}
