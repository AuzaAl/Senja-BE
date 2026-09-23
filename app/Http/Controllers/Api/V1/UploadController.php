<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Upload\StoreUploadRequest;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    public function store(StoreUploadRequest $request): JsonResponse
    {
        $path = ImageUploader::store($request->file('image'), 'uploads');

        return response()->json([
            'data' => [
                'path' => $path,
                'url' => ImageUploader::url($path),
            ],
        ], Response::HTTP_CREATED);
    }
}
