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
            ->with(['gallery', 'partners'])
            ->when($request->string('category')->trim()->toString(), fn ($query, string $category) => $query->where('category', $category))
            ->when($request->has('featured'), fn ($query) => $query->where('featured', $request->boolean('featured')))
            ->when($request->string('search')->trim()->toString(), fn ($query, string $search) => $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('client', 'like', "%{$search}%")->orWhere('location', 'like', "%{$search}%")))
            ->when($request->string('year')->trim()->toString(), fn ($query, string $year) => $query->where('year', $year))
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
        $validated = $request->validated();

        $project = Project::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            'number' => $validated['number'] ?? null,
            'category' => $validated['category'] ?? null,
            'location' => $validated['location'] ?? null,
            'year' => $validated['year'] ?? null,
            'client' => $validated['client'] ?? null,
            'featured' => (bool) ($validated['featured'] ?? false),
            'summary' => $validated['summary'] ?? $validated['description'] ?? null,
            'description' => $validated['description'] ?? $validated['summary'] ?? null,
            'overview' => $validated['overview'] ?? $validated['content'] ?? null,
            'challenge' => $validated['challenge'] ?? null,
            'solution' => $validated['solution'] ?? null,
            'services' => $validated['services'] ?? null,
            'content' => $validated['content'] ?? $validated['overview'] ?? null,
            'stats' => $validated['stats'] ?? null,
            'cover_image' => $this->resolveProjectCover($request),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $this->replaceGallery($project->gallery(), $validated['gallery'] ?? []);
        $project->partners()->sync($this->resolvePartnerIds($validated['partners'] ?? []));

        return (new ProjectResource($project->fresh(['gallery', 'partners'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateProjectRequest $request, Project $project): ProjectResource|JsonResponse
    {
        $validated = $request->validated();

        $project->fill([
            'title' => $validated['title'] ?? $project->title,
            'slug' => $validated['slug'] ?? $project->slug,
            'number' => $validated['number'] ?? $project->number,
            'category' => $validated['category'] ?? $project->category,
            'location' => $validated['location'] ?? $project->location,
            'year' => $validated['year'] ?? $project->year,
            'client' => $validated['client'] ?? $project->client,
            'featured' => array_key_exists('featured', $validated) ? (bool) $validated['featured'] : $project->featured,
            'summary' => $validated['summary'] ?? $validated['description'] ?? $project->summary,
            'description' => $validated['description'] ?? $validated['summary'] ?? $project->description,
            'overview' => $validated['overview'] ?? $validated['content'] ?? $project->overview,
            'challenge' => $validated['challenge'] ?? $project->challenge,
            'solution' => $validated['solution'] ?? $project->solution,
            'services' => $validated['services'] ?? $project->services,
            'content' => $validated['content'] ?? $validated['overview'] ?? $project->content,
            'stats' => $validated['stats'] ?? $project->stats,
            'sort_order' => $validated['sort_order'] ?? $project->sort_order,
        ]);

        $newCover = $this->resolveProjectCover($request, $project->cover_image);
        if ($newCover !== $project->cover_image) {
            ImageUploader::delete($project->cover_image);
            $project->cover_image = $newCover;
        }

        $project->save();

        if (array_key_exists('gallery', $validated)) {
            $this->replaceGallery($project->gallery(), $validated['gallery'] ?? []);
        }

        if (array_key_exists('partners', $validated)) {
            $project->partners()->sync($this->resolvePartnerIds($validated['partners'] ?? []));
        }

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

    /**
     * Resolve cover from file, *_path, `image` (FE) or `src` alias.
     */
    private function resolveProjectCover(Request $request, ?string $current = null): ?string
    {
        if ($request->hasFile('cover_image')) {
            return ImageUploader::store($request->file('cover_image'), 'uploads');
        }

        foreach (['cover_image_path', 'cover_image', 'image', 'src'] as $key) {
            $value = $request->input($key);
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return $current;
    }

    /**
     * @param  array<int, int|string>  $partners
     * @return array<int, int>
     */
    private function resolvePartnerIds(array $partners): array
    {
        return collect($partners)
            ->map(function ($item) {
                if (is_numeric($item)) {
                    return (int) $item;
                }

                return \App\Models\Partner::where('slug', $item)->value('id');
            })
            ->filter()
            ->values()
            ->all();
    }
}
