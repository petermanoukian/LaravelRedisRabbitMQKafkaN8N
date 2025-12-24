<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Optional: diagnostics only
        // if ($this->hasFile('filer')) \Log::info('FILE MIME: ' . $this->file('filer')->getMimeType());
        // if ($this->hasFile('img'))   \Log::info('IMG MIME: ' . $this->file('img')->getMimeType());
        // if ($this->hasFile('img2'))  \Log::info('THUMB MIME: ' . $this->file('img2')->getMimeType());
    }

    public function rules(): array
    {
        $id    = $this->route('id') ?? $this->input('id');
        $catid = $this->input('catid');

        return [
            // Identity
            'id'    => ['nullable', 'integer', 'exists:prods,id'],
            'catid' => ['required', 'integer', 'exists:cats,id'],

            // Name unique per category (name + catid combination must be unique)
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('prods', 'name')
                    ->where(fn ($q) => $q->where('catid', $catid))
                    ->ignore($id),
            ],

            // Content
            'des'  => ['required', 'string'],
            'dess' => ['nullable', 'string'],

            // File upload (document/generic) — strong control here
            'filer' => [
                'nullable',
                'file',
                'max:99990', // size in KB
                'mimetypes:text/plain,application/pdf,image/jpeg,image/jpg,image/png,image/gif,image/webp,image/tiff,
                              application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,
                              application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,
                              application/json,application/octet-stream,text/csv,application/csv,
                              application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,
                              text/html,application/xhtml+xml'
            ],

            // Main image — strong control here
            'img' => [
                'nullable',
                'file',
                'max:109990', // size in KB (~99MB)
                'mimetypes:image/jpeg,image/jpg,image/png,image/gif,image/webp,image/tiff'
            ],

           
        ];
    }

    public function messages(): array
    {
        return [
            // Core
            'catid.required' => 'A category ID is required.',
            'catid.exists'   => 'The selected category does not exist.',
            'name.required'  => 'A unique product name is required.',
            'name.unique'    => 'This product name already exists in this category.',
            'des.required'   => 'A minimum short description is required.',
            'des.string'     => 'The short description must be text.',
            'dess.string'    => 'The detailed description must be text.',

            // File messages
            'filer.file'      => 'The uploaded file must be valid.',
            'filer.max'       => 'The file size exceeds the allowed limit.',
            'filer.mimetypes' => 'The uploaded file type is not allowed.',

            // Image messages
            'img.file'        => 'The uploaded image must be valid.',
            'img.max'         => 'The image size exceeds the allowed limit.',
            'img.mimetypes'   => 'The uploaded image type is not allowed.',
        ];
    }
}
