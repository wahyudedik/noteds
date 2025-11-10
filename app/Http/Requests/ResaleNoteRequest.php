<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Setting;

class ResaleNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $note = $this->route('note');
        $user = auth()->user();
        
        // Only buyer who owns the note and note is in scarcity mode can resell
        if (!$user || $user->role !== 'buyer') {
            return false;
        }
        
        if (!$note || $note->user_id !== $user->id) {
            return false;
        }
        
        if (!$note->isScarcityMode()) {
            return false; // Only scarcity mode allows resale
        }
        
        // Check if user has purchased this note
        $purchasedNote = \App\Models\PurchasedNote::where('user_id', $user->id)
            ->where('note_id', $note->id)
            ->first();
        
        return $purchasedNote !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resale_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $note = $this->route('note');
            $resalePrice = (float) $this->input('resale_price', 0);
            
            if ($resalePrice <= 0) {
                $validator->errors()->add('resale_price', 'Harga resale harus lebih dari 0.');
                return;
            }
            
            // Get original purchase price
            $user = auth()->user();
            $purchaseTransaction = \App\Models\Transaction::where('buyer_id', $user->id)
                ->where('note_id', $note->id)
                ->where('status', 'success')
                ->first();
            
            if ($purchaseTransaction) {
                $originalPrice = (float) $purchaseTransaction->amount;
                
                // Optional: Warn if resale price is too low compared to original
                // But allow it (buyer might want to sell quickly)
                if ($resalePrice < $originalPrice * 0.5) {
                    // Just a warning, not an error
                    // Could add to session flash message
                }
            }
            
            // Validate minimum price based on settings
            $minPrice = Setting::getDefaultMinPrice();
            $categoryRules = Setting::getCategoryMinPrices();
            $tags = $note->tags->pluck('slug')->toArray();
            
            foreach ($tags as $slug) {
                if (isset($categoryRules[$slug])) {
                    $minPrice = max($minPrice, $categoryRules[$slug]);
                }
            }
            
            if ($resalePrice < $minPrice) {
                $formattedMinPrice = 'Rp ' . number_format($minPrice, 0, ',', '.');
                $validator->errors()->add('resale_price', __('messages.price_below_minimum', [
                    'amount' => $formattedMinPrice,
                ]));
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'resale_price.required' => 'Harga resale wajib diisi.',
            'resale_price.numeric' => 'Harga resale harus berupa angka.',
            'resale_price.min' => 'Harga resale tidak boleh negatif.',
        ];
    }
}
