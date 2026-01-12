<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToWatchlistRequest extends FormRequest
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
            'stock_id' => ['required', 'uuid', 'exists:stocks,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'alert_price_above' => ['nullable', 'numeric', 'min:0'],
            'alert_price_below' => ['nullable', 'numeric', 'min:0'],
            'notify_on_signal' => ['boolean'],
        ];
    }
}

