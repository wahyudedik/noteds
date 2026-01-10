<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductBundleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'bundle_price' => 'nullable|numeric|min:0',
            'bundle_discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:2',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'nullable|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.min' => 'A bundle must contain at least 2 products',
            'bundle_price.required_without' => 'Either bundle price or discount percentage must be provided',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->bundle_price && !$this->bundle_discount_percentage) {
                $validator->errors()->add('bundle_price', 'Either bundle price or discount percentage must be provided');
            }
        });
    }
}
