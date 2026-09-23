<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\StoresMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PartnerController extends Controller
{
    use StoresMedia;

    public function index(Request $request): AnonymousResourceCollection
    {
        $partners = Partner::query()
            ->with(['gallery', 'products'])
            // Public default only active; CMS admin can pass ?active=all|0|1
            ->when(
                $request->string('active')->toString() !== 'all',
                fn ($query) => $query->when(
                    $request->has('active'),
                    fn ($q) => $q->where('is_active', $request->boolean('active')),
                    fn ($q) => $q->where('is_active', true)
                )
            )
            ->when($request->string('category')->trim()->toString(), fn ($query, string $category) => $query->where('category', $category))
            ->when($request->string('search')->trim()->toString(), fn ($query, string $search) => $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return PartnerResource::collection($partners);
    }

    public function show(Partner $partner): PartnerResource|JsonResponse
    {
        if (! $partner->is_active && ! request()->user()) {
            return response()->json([
                'message' => 'Partner tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new PartnerResource($partner->load('gallery', 'products'));
    }

    public function store(StorePartnerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $partner = Partner::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'number' => $validated['number'] ?? null,
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'capabilities' => $validated['capabilities'] ?? null,
            'relationship' => $validated['relationship'] ?? null,
            'relationship_detail' => $validated['relationship_detail'] ?? null,
            'website' => $validated['website'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
            'logo' => $this->resolvePartnerImage($request, 'logo', 'logo_path', ['image', 'src']),
            'hero_image' => $this->resolvePartnerImage($request, 'hero_image', 'hero_image_path', ['heroImage', 'hero_image', 'src']),
        ]);

        $this->replaceGallery($partner->gallery(), $validated['gallery'] ?? []);
        $this->replaceProducts($partner->products(), $validated['products'] ?? []);

        return (new PartnerResource($partner->fresh(['gallery', 'products'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): PartnerResource|JsonResponse
    {
        $validated = $request->validated();

        $partner->fill([
            'name' => $validated['name'] ?? $partner->name,
            'slug' => $validated['slug'] ?? $partner->slug,
            'number' => $validated['number'] ?? $partner->number,
            'category' => $validated['category'] ?? $partner->category,
            'description' => $validated['description'] ?? $partner->description,
            'capabilities' => $validated['capabilities'] ?? $partner->capabilities,
            'relationship' => $validated['relationship'] ?? $partner->relationship,
            'relationship_detail' => $validated['relationship_detail'] ?? $partner->relationship_detail,
            'website' => $validated['website'] ?? $partner->website,
            'sort_order' => $validated['sort_order'] ?? $partner->sort_order,
        ]);

        if (array_key_exists('is_active', $validated)) {
            $partner->is_active = (bool) $validated['is_active'];
        }

        $newLogo = $this->resolvePartnerImage($request, 'logo', 'logo_path', ['image', 'src'], $partner->logo);
        if ($newLogo !== $partner->logo) {
            ImageUploader::delete($partner->logo);
            $partner->logo = $newLogo;
        }

        $newHero = $this->resolvePartnerImage($request, 'hero_image', 'hero_image_path', ['heroImage', 'hero_image', 'src'], $partner->hero_image);
        if ($newHero !== $partner->hero_image) {
            ImageUploader::delete($partner->hero_image);
            $partner->hero_image = $newHero;
        }

        $partner->save();

        if (array_key_exists('gallery', $validated)) {
            $this->replaceGallery($partner->gallery(), $validated['gallery'] ?? []);
        }

        if (array_key_exists('products', $validated)) {
            $this->replaceProducts($partner->products(), $validated['products'] ?? []);
        }

        return new PartnerResource($partner->fresh(['gallery', 'products']));
    }

    public function destroy(Partner $partner): JsonResponse
    {
        ImageUploader::delete($partner->logo);
        ImageUploader::delete($partner->hero_image);

        foreach ($partner->gallery as $media) {
            ImageUploader::delete($media->image_path);
        }

        foreach ($partner->products as $product) {
            ImageUploader::delete($product->image_path);
        }

        $partner->delete();

        return response()->json([
            'message' => 'Partner berhasil dihapus.',
        ]);
    }

    /**
     * @param  array<int, string>  $aliases
     */
    private function resolvePartnerImage(Request $request, string $fileKey, string $pathKey, array $aliases = [], ?string $current = null): ?string
    {
        if ($request->hasFile($fileKey)) {
            return ImageUploader::store($request->file($fileKey), 'uploads');
        }

        foreach (array_merge([$pathKey, $fileKey], $aliases) as $key) {
            $value = $request->input($key);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return $current;
    }
}
