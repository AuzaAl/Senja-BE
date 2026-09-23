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
            'hero' => $this->normalizeImageSection($this->hero),
            'story' => $this->normalizeImageSection($this->story),
            'quote' => $this->quote,
            'principles' => $this->principles,
            'capabilities' => $this->normalizeImageSection($this->capabilities),
            'process' => $this->process,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Normalize image shapes: FE uses {src,alt}, BE stores string paths.
     * Expose both `image{src,alt,url}` and legacy `url`.
     *
     * @param  array<string, mixed>|null  $section
     * @return array<string, mixed>|null
     */
    private function normalizeImageSection(?array $section): ?array
    {
        if ($section === null) {
            return null;
        }

        if (isset($section['image']) && is_array($section['image'])) {
            $src = $section['image']['src'] ?? null;
            $section['image'] = [
                'src' => $src,
                'alt' => $section['image']['alt'] ?? null,
                'url' => $this->mediaUrl($src),
            ];
            $section['url'] = $section['image']['url'];
        } elseif (isset($section['image']) && is_string($section['image'])) {
            $section['image'] = [
                'src' => $section['image'],
                'alt' => null,
                'url' => $this->mediaUrl($section['image']),
            ];
            $section['url'] = $section['image']['url'];
        }

        // Backward compat: subtitle <-> description
        if (isset($section['subtitle']) && ! isset($section['description'])) {
            $section['description'] = $section['subtitle'];
        }

        if (isset($section['description']) && ! isset($section['subtitle'])) {
            $section['subtitle'] = $section['description'];
        }

        return $section;
    }
}
