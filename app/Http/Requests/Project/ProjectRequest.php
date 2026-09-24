<?php

namespace App\Http\Requests\Project;

use App\Support\ImageUploader;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $ignoreId = $this->route('project') instanceof \App\Models\Project
            ? $this->route('project')->id
            : $this->project;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($ignoreId), 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'number' => ['nullable', 'string', 'max:10'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:10'],
            'client' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'overview' => ['nullable', 'string'],
            'challenge' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'services' => ['nullable', 'array', 'max:30'],
            'services.*' => ['string', 'max:255'],
            'content' => ['nullable', 'string'],
            'stats' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'file', 'image', 'mimes:'.ImageUploader::ALLOWED_MIME, 'max:5120'],
            'cover_image_path' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => [$this->imageOrPathRule()],
            'gallery.*.image' => ['nullable', 'file', 'image', 'mimes:'.ImageUploader::ALLOWED_MIME, 'max:5120'],
            'gallery.*.image_path' => ['nullable', 'string', 'max:2048'],
            'gallery.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery.*.position' => ['nullable', 'string', 'max:255'],
            'gallery.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'partners' => ['nullable', 'array', 'max:30'],
            'partners.*' => [$this->partnerExistsRule()],
        ];
    }

    /**
     * Accept partner id (int) or slug (string) for FE/CMS convenience.
     */
    private function partnerExistsRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (is_numeric($value)) {
                if (! \App\Models\Partner::whereKey($value)->exists()) {
                    $fail('Partner tidak ditemukan.');
                }

                return;
            }

            if (is_string($value) && \App\Models\Partner::where('slug', $value)->exists()) {
                return;
            }

            $fail('Partner tidak ditemukan (isi id atau slug).');
        };
    }

    /**
     * A gallery item must carry either an uploaded file or an existing path.
     */
    private function imageOrPathRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (empty($value['image'] ?? null) && empty($value['image_path'] ?? null)) {
                $fail('Setiap item gambar wajib memiliki `image` (file) atau `image_path`.');
            }
        };
    }
}
