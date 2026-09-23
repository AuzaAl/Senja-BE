<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\StoresMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\ImageUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ProjectController extends Controller
{
    use StoresMedia;

    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = Project::query()
            ->when($request->string('category')->trim()->toString(), fn ($query, string $category) => $query->where('category', $category))
            ->when($request->has('featured'), fn ($query) => $query->where('featured', $request->boolean('featured')))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ProjectResource::collection($projects);
    }

    public function show(Project $project): ProjectResource
    {
        return new ProjectResource($project->load('gallery', 'partners'));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'title' => $request->validated('title'),
            'slug' => $request->validated('slug') ?? Str::slug($request->validated('title')),
            'category' => $request->validated('category'),
            'featured' => (bool) ($request->validated('featured') ?? false),
            'summary' => $request->validated('summary'),
            'content' => $request->validated('content'),
            'stats' => $request->validated('stats'),
            'cover_image' => $this->resolveSingleImage($request, 'cover_image', 'cover_image_path'),
            'sort_order' => $request->validated('sort_order'),
        ]);

        $this->replaceGallery($project->gallery(), $request->validated('gallery') ?? []);
        $project->partners()->sync($request->validated('partners') ?? []);

        return (new ProjectResource($project->fresh(['gallery', 'partners'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource|JsonResponse
    {
        $project->title = $request->validated('title');
        $project->slug = $request->validated('slug') ?? $project->slug;
        $project->category = $request->validated('category');
        $project->featured = (bool) ($request->validated('featured') ?? $project->featured);
        $project->summary = $request->validated('summary');
        $project->content = $request->validated('content');
        $project->stats = $request->validated('stats');
        $project->sort_order = $request->validated('sort_order');

        if ($request->hasFile('cover_image') || $request->filled('cover_image_path')) {
            $oldCover = $project->cover_image;
            $project->cover_image = $this->resolveSingleImage($request, 'cover_image', 'cover_image_path', $project->cover_image);
            ImageUploader::delete($oldCover);
        }

        $project->save();
        $this->replaceGallery($project->gallery(), $request->validated('gallery') ?? []);
        $project->partners()->sync($request->validated('partners') ?? []);

        return new ProjectResource($project->fresh(['gallery', 'partners']));
    }

    public function destroy(Project $project): JsonResponse
    {
        ImageUploader::delete($project->cover_image);

        foreach ($project->gallery as $media) {
            ImageUploader::delete($media->image_path);
        }

        $project->delete();

        return response()->json([
            'message' => 'Proyek berhasil dihapus.',
        ]);
    }
}
