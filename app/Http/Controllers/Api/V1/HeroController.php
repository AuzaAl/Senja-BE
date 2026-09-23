<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hero\UpdateHeroRequest;
use App\Http\Resources\HeroResource;
use App\Models\Hero;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Landing-page hero: public read + admin update (singleton row).
 */
class HeroController extends Controller
{
    public function show(Request $request): HeroResource|JsonResponse
    {
        $hero = Hero::first();

        if (! $hero) {
            return response()->json([
                'message' => 'Konten hero belum tersedia.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new HeroResource($hero);
    }

    public function update(UpdateHeroRequest $request): JsonResponse
    {
        $hero = Hero::updateOrCreate(['id' => 1], [
            'eyebrow' => $request->validated('eyebrow'),
            'title' => $request->validated('title'),
            'subtitle' => $request->validated('subtitle'),
            'button_label' => $request->validated('button_label'),
            'button_link' => $request->validated('button_link'),
            'images' => $this->normalizeImages($request->validated('images') ?? []),
        ]);

        $resource = new HeroResource($hero);

        return $hero->wasRecentlyCreated
            ? $resource->response()->setStatusCode(Response::HTTP_CREATED)
            : $resource->response();
    }

    /**
     * Persist uploaded images, falling back to previously uploaded paths.
     *
     * @param  array<int, array<string, mixed>>  $images
     * @return array<int, array{path: ?string, alt: ?string}>
     */
    private function normalizeImages(array $images): array
    {
        return collect($images)
            ->map(fn (array $item): array => [
                'path' => ! empty($item['image'])
                    ? ImageUploader::store($item['image'], 'uploads')
                    : ($item['image_path'] ?? null),
                'alt' => $item['alt'] ?? null,
            ])
            ->values()
            ->all();
    }
}
