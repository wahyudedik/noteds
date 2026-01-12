<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePortfolioRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'risk_profile' => ['required', 'in:conservative,moderate,aggressive'],
            'investment_amount' => ['required', 'numeric', 'min:1000000'], // Minimum 1M IDR
            'investment_horizon' => ['required', 'integer', 'min:30', 'max:3650'], // 30 days to 10 years
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'risk_profile.required' => 'Please select a risk profile.',
            'risk_profile.in' => 'Invalid risk profile selected.',
            'investment_amount.required' => 'Investment amount is required.',
            'investment_amount.min' => 'Minimum investment amount is 1,000,000 IDR.',
            'investment_horizon.required' => 'Investment horizon is required.',
            'investment_horizon.min' => 'Minimum investment horizon is 30 days.',
            'investment_horizon.max' => 'Maximum investment horizon is 10 years (3650 days).',
        ];
    }
}

