<?php

namespace App\Http\Resources\Concerns;

use App\Support\ImageUploader;
use Illuminate\Support\Collection;

/**
 * Shared helpers for serializing stored media into path + url pairs.
 */
trait SerializesMedia
{
    /**
     * Resolve a stored path into its public URL.
     */
    protected function mediaUrl(?string $path): ?string
    {
        return ImageUploader::url($path);
    }

    /**
     * Serialize a single media item.
     * Includes `src` alias so FE (`image.src`) works without mapping.
     *
     * @return array{path: ?string, url: ?string, src: ?string, alt: ?string}
     */
    protected function mediaItem(?string $path, ?string $alt = null): array
    {
        $url = $this->mediaUrl($path);

        return [
            'path' => $path,
            'url' => $url,
            'src' => $url ?? $path,
            'alt' => $alt,
        ];
    }

    /**
     * Serialize a gallery of media items (models or arrays).
     * Exposes both BE (`path/url`) and FE (`src`) keys for compatibility.
     *
     * @param  Collection<int, object>|array<int, array<string, mixed>>|null  $items
     * @return array<int, array<string, mixed>>
     */
    protected function mediaCollection(mixed $items): array
    {
        return collect($items ?? [])
            ->map(function (mixed $item): array {
                $data = is_array($item) ? $item : $item->getAttributes();
                $path = $data['image_path'] ?? $data['path'] ?? null;
                $url = $this->mediaUrl($path) ?? $path;

                return [
                    ...$this->mediaItem($path, $data['alt'] ?? null),
                    'src' => $url,
                    'position' => $data['position'] ?? null,
                    'sort_order' => $data['sort_order'] ?? 0,
                ];
            })
            ->values()
            ->all();
    }
}
