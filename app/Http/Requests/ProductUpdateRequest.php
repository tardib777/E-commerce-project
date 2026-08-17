<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|min:3|max:30',
            'description' => 'sometimes|string|min:30|max:1000',
            'price' => 'sometimes|decimal:2',
            'available_quantity' => 'sometimes|integer',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'image' => 'sometimes|image'
        ];
    
    }
}
