<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'img' => [
                'nullable',
                'file',
                'max:' . ($this->input('maxFileSize') ?? 99920), // size in KB (~90MB)
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'img.file'  => 'The uploaded image must be valid.',
            'img.max'   => 'The image size must not exceed 80MB.',
        ];
    }
}
