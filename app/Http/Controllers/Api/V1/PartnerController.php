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
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return PartnerResource::collection($partners);
    }

    public function show(Partner $partner): PartnerResource|JsonResponse
    {
        if (! $partner->is_active) {
            return response()->json([
                'message' => 'Partner tidak ditemukan.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new PartnerResource($partner->load('gallery', 'products'));
    }

    public function store(StorePartnerRequest $request): JsonResponse
    {
        $partner = Partner::create([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug') ?? Str::slug($request->validated('name')),
            'description' => $request->validated('description'),
            'website' => $request->validated('website'),
            'sort_order' => $request->validated('sort_order'),
            'is_active' => (bool) ($request->validated('is_active') ?? true),
            'logo' => $this->resolveSingleImage($request, 'logo', 'logo_path'),
        ]);

        $this->replaceGallery($partner->gallery(), $request->validated('gallery') ?? []);
        $this->replaceProducts($partner->products(), $request->validated('products') ?? []);

        return (new PartnerResource($partner->fresh(['gallery', 'products'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdatePartnerRequest $request, Partner $partner): PartnerResource|JsonResponse
    {
        $partner->name = $request->validated('name');
        $partner->slug = $request->validated('slug') ?? $partner->slug;
        $partner->description = $request->validated('description');
        $partner->website = $request->validated('website');
        $partner->sort_order = $request->validated('sort_order');

        if ($request->has('is_active')) {
            $partner->is_active = (bool) $request->validated('is_active');
        }

        if ($request->hasFile('logo') || $request->filled('logo_path')) {
            $oldLogo = $partner->logo;
            $partner->logo = $this->resolveSingleImage($request, 'logo', 'logo_path', $partner->logo);
            ImageUploader::delete($oldLogo);
        }

        $partner->save();
        $this->replaceGallery($partner->gallery(), $request->validated('gallery') ?? []);
        $this->replaceProducts($partner->products(), $request->validated('products') ?? []);

        return new PartnerResource($partner->fresh(['gallery', 'products']));
    }

    public function destroy(Partner $partner): JsonResponse
    {
        ImageUploader::delete($partner->logo);

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
}
