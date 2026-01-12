<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePricingRuleRequest extends FormRequest
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
        $rules = [
            'product_id' => ['required', 'uuid', 'exists:products,id'],
            'rule_type' => ['required', 'in:time_based,stock_based,demand_based'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100'],
            'adjustment_type' => ['required', 'in:fixed,percentage'],
            'adjustment_value' => ['required', 'numeric'],
            'base_price_override' => ['nullable', 'numeric', 'min:0'],
            'max_applications' => ['nullable', 'integer', 'min:1'],
        ];

        // Time-based rule specific validation
        if ($this->rule_type === 'time_based') {
            $rules['start_date'] = ['nullable', 'date'];
            $rules['end_date'] = ['nullable', 'date', 'after_or_equal:start_date'];
            $rules['start_time'] = ['nullable', 'date_format:H:i:s'];
            $rules['end_time'] = ['nullable', 'date_format:H:i:s', 'after:start_time'];
            $rules['days_of_week'] = ['nullable', 'array'];
            $rules['days_of_week.*'] = ['integer', 'min:0', 'max:6'];
        }

        // Stock-based rule specific validation
        if ($this->rule_type === 'stock_based') {
            $rules['stock_threshold'] = ['required', 'integer', 'min:0'];
            $rules['stock_condition'] = ['required', 'in:below,above,equals'];
        }

        // Demand-based rule specific validation
        if ($this->rule_type === 'demand_based') {
            $rules['sales_period_days'] = ['required', 'integer', 'min:1'];
            $rules['sales_threshold'] = ['nullable', 'integer', 'min:0'];
            $rules['demand_condition'] = ['required', 'in:high,low'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required.',
            'rule_type.required' => 'Rule type is required.',
            'rule_type.in' => 'Invalid rule type.',
            'name.required' => 'Rule name is required.',
            'adjustment_type.required' => 'Adjustment type is required.',
            'adjustment_value.required' => 'Adjustment value is required.',
            'stock_threshold.required' => 'Stock threshold is required for stock-based rules.',
            'sales_period_days.required' => 'Sales period days is required for demand-based rules.',
            'demand_condition.required' => 'Demand condition is required for demand-based rules.',
        ];
    }
}
