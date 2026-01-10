<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductReview;

class UpdateProductReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $review = $this->route('productReview');
        
        if (!$review instanceof ProductReview) {
            return false;
        }

        // Check if user owns the review
        if ($review->user_id !== $this->user()->id) {
            return false;
        }

        // Check if review is locked
        if ($review->isLocked()) {
            return false;
        }

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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'order_id' => ['nullable', 'uuid', 'exists:orders,id'],
            'media' => ['nullable', 'array', 'max:5'],
            'media.*' => ['file', 'mimes:jpeg,jpg,png,gif,mp4,mov,avi', 'max:5120'], // 5MB max
        ];
    }
}
