<?php

namespace App\Http\Requests\Upload;

use App\Support\ImageUploader;
use Illuminate\Foundation\Http\FormRequest;

class StoreUploadRequest extends FormRequest
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
        return ImageUploader::rules(true);
    }
}
