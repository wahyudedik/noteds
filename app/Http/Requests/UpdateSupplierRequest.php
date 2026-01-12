<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
            'supplier_name' => ['sometimes', 'required', 'string', 'max:255'],
            'supplier_category' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['sometimes', 'required', 'string', 'min:50', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['sometimes', 'required', 'array'],
            'contact_info.phone' => ['required_with:contact_info', 'string', 'max:20'],
            'contact_info.email' => ['nullable', 'email', 'max:255'],
            'contact_info.whatsapp' => ['nullable', 'string', 'max:20'],
            'contact_info.address' => ['nullable', 'string', 'max:500'],
            'specialties' => ['nullable', 'array'],
            'specialties.*' => ['string', 'max:100'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'delivery_scope' => ['nullable', 'in:lokal,nasional,internasional'],
        ];
    }
}
