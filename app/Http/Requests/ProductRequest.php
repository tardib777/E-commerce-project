<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'name' => 'required|string|min:3|max:30',
            'description' => 'required|string|min:30|max:1000',
            'price' => 'required|decimal:2',
            'available_quantity' => 'required|integer',
            'category_id' => 'required|integer|exists:categories,id',
            'image' => 'required|image'
        ];
    }
}
