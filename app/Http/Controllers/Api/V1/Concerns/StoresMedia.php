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
     * existing path (`*_path`, `src`, plain string), then previous value.
     */
    protected function resolveSingleImage(Request $request, string $fileKey, string $pathKey, ?string $current = null): ?string
    {
        if ($request->hasFile($fileKey)) {
            return ImageUploader::store($request->file($fileKey), 'uploads');
        }

        if ($request->filled($pathKey)) {
            return $request->input($pathKey);
        }

        // FE/CMS compat: {src}, plain string, or `image` as path string.
        $fallbackKeys = ['src', 'image', 'path', 'url'];
        foreach ($fallbackKeys as $key) {
            $value = $request->input($key);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return $current;
    }

    /**
     * Persist the image of a nested gallery/product item.
     * Accepts FE `src` alias and plain string paths as well.
     *
     * @param  array<string, mixed>|string  $item
     */
    protected function itemImage(array|string $item): ?string
    {
        if (is_string($item)) {
            return $item ?: null;
        }

        if (! empty($item['image']) && $item['image'] instanceof \Illuminate\Http\UploadedFile) {
            return ImageUploader::store($item['image'], 'uploads');
        }

        return $item['image_path'] ?? $item['src'] ?? $item['path'] ?? null;
    }

    /**
     * Replace a gallery entirely: delete previous rows + files, then
     * recreate from the submitted items.
     *
     * @param  array<int, array<string, mixed>|string>  $items
     */
    protected function replaceGallery(HasMany $relation, array $items): void
    {
        foreach ($relation->get() as $media) {
            ImageUploader::delete($media->image_path);
        }

        $relation->delete();

        foreach ($items as $index => $item) {
            $item = is_string($item) ? ['image_path' => $item] : $item;

            $relation->create([
                'image_path' => $this->itemImage($item),
                'alt' => $item['alt'] ?? null,
                'position' => $item['position'] ?? null,
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
            $image = $item['image'] ?? null;
            $imagePath = $image instanceof \Illuminate\Http\UploadedFile
                ? ImageUploader::store($image, 'uploads')
                : ($item['image_path'] ?? (is_string($image) ? $image : null));

            $relation->create([
                'name' => $item['name'] ?? null,
                'category' => $item['category'] ?? null,
                'description' => $item['description'] ?? null,
                'image_path' => $imagePath,
                'link' => $item['link'] ?? null,
                'sort_order' => $item['sort_order'] ?? $index,
            ]);
        }
    }
}
