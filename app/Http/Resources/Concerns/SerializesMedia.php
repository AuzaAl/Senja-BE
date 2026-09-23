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
     *
     * @return array{path: ?string, url: ?string, alt: ?string}
     */
    protected function mediaItem(?string $path, ?string $alt = null): array
    {
        return [
            'path' => $path,
            'url' => $this->mediaUrl($path),
            'alt' => $alt,
        ];
    }

    /**
     * Serialize a gallery of media items (models or arrays).
     *
     * @param  Collection<int, object>|array<int, array<string, mixed>>|null  $items
     * @return array<int, array<string, mixed>>
     */
    protected function mediaCollection(mixed $items): array
    {
        return collect($items ?? [])
            ->map(function (mixed $item): array {
                $data = is_array($item) ? $item : $item->getAttributes();

                return [
                    ...$this->mediaItem(
                        $data['image_path'] ?? $data['path'] ?? null,
                        $data['alt'] ?? null,
                    ),
                    'sort_order' => $data['sort_order'] ?? 0,
                ];
            })
            ->values()
            ->all();
    }
}
