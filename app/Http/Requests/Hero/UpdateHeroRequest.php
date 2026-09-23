<?php

namespace App\Http\Requests\Hero;

use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHeroRequest extends FormRequest
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
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:1000'],
            'button_label' => ['nullable', 'string', 'max:255'],
            // FE/CMS use anchors (e.g. "#solutions") and relative paths — accept any non-empty string.
            'button_link' => ['nullable', 'string', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => [$this->imageOrPathRule()],
            'images.*.image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'images.*.image_path' => ['nullable', 'string', 'max:2048'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // CMS sends images as string[] — normalize to [{image_path, alt}] for validation.
        $images = $this->input('images');

        if (is_array($images)) {
            $normalized = array_map(
                fn ($item) => is_string($item) ? ['image_path' => $item] : $item,
                $images
            );

            $this->merge(['images' => $normalized]);
        }
    }

    /**
     * A gallery item must carry either an uploaded file, an existing path,
     * or a plain string path (normalized above).
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
