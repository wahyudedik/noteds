<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScreenStocksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Both authenticated and anonymous users can screen stocks
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isPremium = $user && $this->isPremiumUser($user);
        $maxLimit = $isPremium ? 100 : 20;

        return [
            'sector' => ['nullable', 'array'],
            'sector.*' => ['string', 'max:100'],
            'category' => ['nullable', 'array'],
            'category.*' => ['in:LQ45,IDX30,IDX80,Kompas100,others'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'gt:price_min'],
            'volume_min' => ['nullable', 'integer', 'min:0'],
            'rsi_min' => ['nullable', 'numeric', 'between:0,100'],
            'rsi_max' => ['nullable', 'numeric', 'between:0,100', 'gt:rsi_min'],
            'macd_bullish' => ['nullable', 'boolean'],
            'signal_type' => ['nullable', 'in:buy,sell,hold'],
            'signal_strength_min' => ['nullable', 'numeric', 'between:0,1'],
            'risk_level' => ['nullable', 'array'],
            'risk_level.*' => ['in:low,medium,high,very_high'],
            'prediction_horizon' => ['nullable', 'in:1,7,30'],
            'prediction_confidence_min' => ['nullable', 'numeric', 'between:0,1'],
            'limit' => ['nullable', 'integer', 'max:' . $maxLimit],
        ];
    }

    /**
     * Check if user is premium.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    protected function isPremiumUser($user): bool
    {
        // Check if user has premium role
        if (isset($user->role) && in_array($user->role, ['premium', 'admin'])) {
            return true;
        }

        // You can add subscription check here
        // if ($user->hasActiveSubscription('premium')) {
        //     return true;
        // }

        return false;
    }
}

