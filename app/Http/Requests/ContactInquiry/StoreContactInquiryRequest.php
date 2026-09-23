<?php

namespace App\Http\Requests\ContactInquiry;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactInquiryRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:255'],
            'timeline' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // FE/CMS send camelCase projectType — normalize to snake_case.
        if ($this->has('projectType') && ! $this->has('project_type')) {
            $this->merge(['project_type' => $this->input('projectType')]);
        }
    }
}
