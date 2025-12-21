<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\FormRequest;

class CatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    protected function prepareForValidation()
    {
        if ($this->hasFile('filer')) {
            $mime = $this->file('filer')->getMimeType();
            //Log::info('CatRequest detected file MIME type: ' . $mime);
        }
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->input('id');

        return [
            'id' => ['nullable', 'integer', 'exists:cats,id'],
            'name' => [
                'required',
                'string',
                'max:255',
            Rule::unique('cats', 'name')->ignore($id),           
            ],
            'des'  => ['required', 'string'],
            'dess' => ['nullable', 'string'],

            // ✅ File validation lives here now
            'filer' => [
                'nullable',
                'file',
                'max:99990', // size in KB (~9MB)
                'mimetypes:text/plain,application/pdf,image/jpeg,image/png,image/gif,image/webp,image/tiff,
                           application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                           application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                           application/json,text/csv,application/csv,
                           application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/html,application/xhtml+xml

'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'A unique category name is required.',
            'name.unique'   => 'This category name already exists. Please choose another.',
            'des.required'  => 'A minimum short description is required.',
            'des.string'    => 'The short description must be text.',
            'dess.string'   => 'The detailed description must be text.',
            'filer.file'    => 'The uploaded file must be valid.',
            'filer.max'     => 'The file size must not exceed 87MB.',
            'filer.mimetypes' => 'The uploaded file type is not allowed.',
        ];
    }
}
