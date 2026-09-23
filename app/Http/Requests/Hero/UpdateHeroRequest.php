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
            'button_link' => ['nullable', 'string', 'max:2048', 'url'],
            'images' => ['nullable', 'array'],
            'images.*' => [$this->imageOrPathRule()],
            'images.*.image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:5120'],
            'images.*.image_path' => ['nullable', 'string', 'max:2048'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
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
