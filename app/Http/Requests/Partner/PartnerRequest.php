<?php

namespace App\Http\Requests\Partner;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PartnerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('partners', 'slug')->ignore($this->partner), 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'logo_path' => ['nullable', 'string', 'max:2048'],
            'description' => ['nullable', 'string', 'max:5000'],
            'website' => ['nullable', 'string', 'max:2048', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],

            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => [$this->imageOrPathRule()],
            'gallery.*.image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'gallery.*.image_path' => ['nullable', 'string', 'max:2048'],
            'gallery.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery.*.sort_order' => ['nullable', 'integer', 'min:0'],

            'products' => ['nullable', 'array', 'max:30'],
            'products.*' => ['array'],
            'products.*.name' => ['required_with:products', 'string', 'max:255'],
            'products.*.description' => ['nullable', 'string', 'max:5000'],
            'products.*.image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'products.*.image_path' => ['nullable', 'string', 'max:2048'],
            'products.*.link' => ['nullable', 'string', 'max:2048', 'url'],
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
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
