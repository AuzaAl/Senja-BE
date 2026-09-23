<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Support\ImageUploader;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

/**
 * Shared helpers for persisting uploaded media on content resources.
 */
trait StoresMedia
{
    /**
     * Resolve a single-image field: prefer an uploaded file, then an
     * existing path, then the previously stored value.
     */
    protected function resolveSingleImage(Request $request, string $fileKey, string $pathKey, ?string $current = null): ?string
    {
        if ($request->hasFile($fileKey)) {
            return ImageUploader::store($request->file($fileKey), 'uploads');
        }

        if ($request->filled($pathKey)) {
            return $request->input($pathKey);
        }

        return $current;
    }

    /**
     * Persist the image of a nested gallery/product item.
     *
     * @param  array<string, mixed>  $item
     */
    protected function itemImage(array $item): ?string
    {
        if (! empty($item['image'])) {
            return ImageUploader::store($item['image'], 'uploads');
        }

        return $item['image_path'] ?? null;
    }

    /**
     * Replace a gallery entirely: delete previous rows + files, then
     * recreate from the submitted items.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function replaceGallery(HasMany $relation, array $items): void
    {
        foreach ($relation->get() as $media) {
            ImageUploader::delete($media->image_path);
        }

        $relation->delete();

        foreach ($items as $index => $item) {
            $relation->create([
                'image_path' => $this->itemImage($item),
                'alt' => $item['alt'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);
        }
    }

    /**
     * Replace a partner's product list entirely.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function replaceProducts(HasMany $relation, array $items): void
    {
        foreach ($relation->get() as $product) {
            ImageUploader::delete($product->image_path);
        }

        $relation->delete();

        foreach ($items as $index => $item) {
            $relation->create([
                'name' => $item['name'] ?? null,
                'description' => $item['description'] ?? null,
                'image_path' => $this->itemImage($item),
                'link' => $item['link'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);
        }
    }
}
