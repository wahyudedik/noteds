<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Setting;
use Illuminate\Support\Str;

class StoreNoteRequest extends FormRequest
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
        $maxFileSize = auth()->user() && auth()->user()->hasPremium() ? 20971520 : 5242880; // 20MB for premium, 5MB for basic
        
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'preview_content' => ['nullable', 'string', 'max:300'],
            'preview_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'thumbnails' => ['nullable', 'array', 'max:5'],
            'thumbnails.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB per image
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'is_public' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,sold,inactive'],
            'sale_mode' => ['nullable', 'in:scarcity,standard'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'relist_price_multiplier' => ['nullable', 'numeric', 'min:1', 'max:10'],
            'workspace_id' => ['nullable', 'exists:workspaces,id'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'attachments.*' => [
                'file',
                'max:' . ($maxFileSize / 1024), // in KB
                'mimes:pdf,doc,docx,txt,zip,rar,jpg,jpeg,png,gif,xls,xlsx,ppt,pptx'
            ],
        ];
    }

    public function messages(): array
    {
        $user = auth()->user();
        $isPremium = $user && $user->hasPremium();
        
        if (!$isPremium) {
            return [
                'attachments.*.max' => 'File size exceeds 5MB limit for Basic users. Upgrade to Premium to upload files up to 20MB.',
                'attachments.*.mimes' => 'File type not allowed. Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, XLS, XLSX, PPT, PPTX',
            ];
        }
        
        return [
            'attachments.*.max' => 'File size exceeds 20MB limit.',
            'attachments.*.mimes' => 'File type not allowed. Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, XLS, XLSX, PPT, PPTX',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
            if (!$user || $user->hasPremium()) {
                return;
            }

            // Check file sizes manually for better error handling
            if ($this->hasFile('attachments')) {
                $files = $this->file('attachments');
                $maxSize = 5242880; // 5MB in bytes
                
                foreach ($files as $index => $file) {
                    if ($file->getSize() > $maxSize) {
                        $sizeInMB = round($file->getSize() / 1048576, 2);
                        $validator->errors()->add(
                            "attachments.{$index}",
                            "File '{$file->getClientOriginalName()}' ({$sizeInMB}MB) exceeds 5MB limit for Basic users. <a href='" . route('subscription.create') . "' class='font-semibold text-blue-600 hover:text-blue-700 underline'>Upgrade to Premium</a> to upload files up to 20MB."
                        );
                    }
                }
            }

            // Validate discount_price
            $price = $this->input('price', 0);
            $discountPrice = $this->input('discount_price');
            
            if ($discountPrice !== null && $discountPrice !== '') {
                $discountPrice = (float) $discountPrice;
                if ($price <= 0) {
                    $validator->errors()->add('discount_price', 'Harga diskon hanya bisa diatur jika harga normal lebih dari 0.');
                } elseif ($discountPrice >= $price) {
                    $validator->errors()->add('discount_price', 'Harga diskon harus lebih murah dari harga normal.');
                } elseif ($discountPrice < 0) {
                    $validator->errors()->add('discount_price', 'Harga diskon tidak boleh negatif.');
                }
            }

            $price = (float) $this->input('price', 0);
            if ($price > 0) {
                $minPrice = Setting::getDefaultMinPrice();
                $categoryRules = Setting::getCategoryMinPrices();
                $tags = collect($this->input('tags', []))
                    ->map(fn ($tag) => Str::slug($tag))
                    ->filter()
                    ->all();

                foreach ($tags as $slug) {
                    if (isset($categoryRules[$slug])) {
                        $minPrice = max($minPrice, $categoryRules[$slug]);
                    }
                }

                if ($price < $minPrice) {
                    $formattedMinPrice = 'Rp ' . number_format($minPrice, 0, ',', '.');
                    $validator->errors()->add('price', __('messages.price_below_minimum', [
                        'amount' => $formattedMinPrice,
                    ]));
                }
            }
        });
    }
}
