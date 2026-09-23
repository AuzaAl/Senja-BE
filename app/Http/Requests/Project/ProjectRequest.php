<?php

namespace App\Http\Requests\Project;

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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($this->project), 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'category' => ['nullable', 'string', 'max:255'],
            'featured' => ['nullable', 'boolean'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'content' => ['nullable', 'string'],
            'stats' => ['nullable', 'array'],
            'cover_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'cover_image_path' => ['nullable', 'string', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],

            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => [$this->imageOrPathRule()],
            'gallery.*.image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'gallery.*.image_path' => ['nullable', 'string', 'max:2048'],
            'gallery.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'partners' => ['nullable', 'array', 'max:30'],
            'partners.*' => ['integer', Rule::exists('partners', 'id')],
        ];
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
