<?php

namespace App\Http\Requests\About;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutRequest extends FormRequest
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
            'hero' => ['nullable', 'array'],
            'hero.title' => ['nullable', 'string', 'max:255'],
            'hero.subtitle' => ['nullable', 'string', 'max:1000'],
            'hero.image' => ['nullable', 'string', 'max:2048'],

            'story' => ['nullable', 'array'],
            'story.title' => ['nullable', 'string', 'max:255'],
            'story.paragraphs' => ['nullable', 'array'],
            'story.paragraphs.*' => ['required_with:story.paragraphs', 'string'],

            'quote' => ['nullable', 'array'],
            'quote.text' => ['required_with:quote', 'string', 'max:5000'],
            'quote.author' => ['nullable', 'string', 'max:255'],

            'principles' => ['nullable', 'array'],
            'principles.*' => ['array'],
            'principles.*.title' => ['required_with:principles', 'string', 'max:255'],
            'principles.*.description' => ['nullable', 'string', 'max:5000'],

            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['array'],
            'capabilities.*.title' => ['required_with:capabilities', 'string', 'max:255'],
            'capabilities.*.description' => ['nullable', 'string', 'max:5000'],

            'process' => ['nullable', 'array'],
            'process.*' => ['array'],
            'process.*.step' => ['nullable', 'integer', 'min:1'],
            'process.*.title' => ['required_with:process', 'string', 'max:255'],
            'process.*.description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
