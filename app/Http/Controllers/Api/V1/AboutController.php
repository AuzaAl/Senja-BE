<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\About\UpdateAboutRequest;
use App\Http\Resources\AboutResource;
use App\Models\About;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Landing-page about section: public read + admin update (singleton row).
 */
class AboutController extends Controller
{
    public function show(Request $request): AboutResource|JsonResponse
    {
        $about = About::first();

        if (! $about) {
            return response()->json([
                'message' => 'Konten about belum tersedia.',
            ], Response::HTTP_NOT_FOUND);
        }

        return new AboutResource($about);
    }

    public function update(UpdateAboutRequest $request): JsonResponse
    {
        $about = About::firstOrNew(['id' => 1]);

        foreach (['hero', 'story', 'quote', 'principles', 'capabilities', 'process'] as $field) {
            if ($request->has($field)) {
                $about->{$field} = $request->validated($field);
            }
        }

        $about->save();

        $resource = new AboutResource($about);

        return $about->wasRecentlyCreated
            ? $resource->response()->setStatusCode(Response::HTTP_CREATED)
            : $resource->response();
    }
}
