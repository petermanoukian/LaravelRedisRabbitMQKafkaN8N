<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filer' => [
                'nullable',
                'file',
                'max:' . ($this->input('maxFileSize') ?? 99990),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'filer.file' => 'The uploaded file must be valid.',
            'filer.max'  => 'The file size must not exceed 89MB.',
        ];
    }

}
