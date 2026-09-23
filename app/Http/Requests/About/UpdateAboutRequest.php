<?php

namespace App\Http\Requests\About;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAboutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Backward compat: old clients send image as plain string path.
        foreach (['hero', 'story', 'capabilities'] as $section) {
            $image = $this->input("{$section}.image");
            if (is_string($image)) {
                $this->merge(["{$section}" => array_merge(
                    (array) $this->input($section, []),
                    ['image' => ['src' => $image]]
                )]);
            }
        }

        // Backward compat: old shape sent principles/capabilities/process as flat lists.
        // Wrap them into new nested {items/steps} so old tests + clients keep working.
        foreach ([
            'principles' => 'items',
            'process' => 'steps',
        ] as $section => $nestKey) {
            $value = $this->input($section);
            if (is_array($value) && array_is_list($value)) {
                $this->merge([$section => [$nestKey => $value]]);
            }
        }

        $capabilities = $this->input('capabilities');
        if (is_array($capabilities) && array_is_list($capabilities)) {
            // Old: [{title, description}] → new services[] + keep raw for flexibility.
            $services = array_values(array_filter(array_map(
                fn ($item) => is_array($item) ? ($item['title'] ?? null) : (is_string($item) ? $item : null),
                $capabilities
            )));
            $this->merge(['capabilities' => ['services' => $services]]);
        }
    }

    /**
     * Full editorial shape used by FE AboutPage + CMS about-form.
     * All sections nullable for partial updates.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'hero' => ['nullable', 'array'],
            'hero.eyebrow' => ['nullable', 'string', 'max:255'],
            'hero.title' => ['nullable', 'string', 'max:255'],
            'hero.description' => ['nullable', 'string', 'max:2000'],
            'hero.subtitle' => ['nullable', 'string', 'max:2000'],
            'hero.image' => ['nullable', 'array'],
            'hero.image.src' => ['nullable', 'string', 'max:2048'],
            'hero.image.alt' => ['nullable', 'string', 'max:255'],

            'story' => ['nullable', 'array'],
            'story.eyebrow' => ['nullable', 'string', 'max:255'],
            'story.title' => ['nullable', 'string', 'max:500'],
            'story.paragraphs' => ['nullable', 'array', 'max:20'],
            'story.paragraphs.*' => ['string'],
            'story.stats' => ['nullable', 'array', 'max:20'],
            'story.stats.*.value' => ['nullable', 'string', 'max:50'],
            'story.stats.*.label' => ['nullable', 'string', 'max:255'],
            'story.image' => ['nullable', 'array'],
            'story.image.src' => ['nullable', 'string', 'max:2048'],
            'story.image.alt' => ['nullable', 'string', 'max:255'],

            'quote' => ['nullable', 'array'],
            'quote.eyebrow' => ['nullable', 'string', 'max:255'],
            'quote.text' => ['nullable', 'string', 'max:5000'],
            'quote.author' => ['nullable', 'string', 'max:255'],

            'principles' => ['nullable', 'array'],
            'principles.eyebrow' => ['nullable', 'string', 'max:255'],
            'principles.title' => ['nullable', 'string', 'max:255'],
            'principles.description' => ['nullable', 'string', 'max:2000'],
            'principles.items' => ['nullable', 'array', 'max:20'],
            'principles.items.*.title' => ['required_with:principles.items', 'string', 'max:255'],
            'principles.items.*.description' => ['nullable', 'string', 'max:5000'],

            'capabilities' => ['nullable', 'array'],
            'capabilities.eyebrow' => ['nullable', 'string', 'max:255'],
            'capabilities.title' => ['nullable', 'string', 'max:255'],
            'capabilities.description' => ['nullable', 'string', 'max:2000'],
            'capabilities.visualLabel' => ['nullable', 'string', 'max:255'],
            'capabilities.services' => ['nullable', 'array', 'max:30'],
            'capabilities.services.*' => ['string', 'max:255'],
            'capabilities.image' => ['nullable', 'array'],
            'capabilities.image.src' => ['nullable', 'string', 'max:2048'],
            'capabilities.image.alt' => ['nullable', 'string', 'max:255'],

            'process' => ['nullable', 'array'],
            'process.eyebrow' => ['nullable', 'string', 'max:255'],
            'process.title' => ['nullable', 'string', 'max:255'],
            'process.description' => ['nullable', 'string', 'max:2000'],
            'process.steps' => ['nullable', 'array', 'max:20'],
            'process.steps.*.title' => ['required_with:process.steps', 'string', 'max:255'],
            'process.steps.*.description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
