<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProdorderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Adjust if you want role-based checks; for now allow all.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'prodid'   => 'required|exists:prods,id',
            'quan'     => 'nullable|integer|min:1',
            'customer' => 'nullable|string|max:255',
        ];
    }

    /**
     * Customize validation messages (optional).
     */
    public function messages(): array
    {
        return [
            'prodid.required' => 'A product ID is required for the order.',
            'prodid.exists'   => 'The selected product does not exist.',
            'quan.integer'    => 'Quantity must be a valid integer.',
            'quan.min'        => 'Quantity must be at least 1.',
            'customer.string' => 'Customer name must be a valid string.',
            'customer.max'    => 'Customer name may not exceed 255 characters.',
        ];
    }
}
