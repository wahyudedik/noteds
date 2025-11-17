<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Setting;
use Illuminate\Support\Str;

class UpdateNoteRequest extends FormRequest
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
        $maxFileSize = 10485760; // 10MB for all users
        
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'preview_content' => ['nullable', 'string', 'max:300'],
            'ecosystem_category' => ['nullable', 'in:design,code,photo,audio,video,theme,3d,elements'],
            // Ecosystem-specific fields
            'code_language' => ['nullable', 'string', 'max:50'], // Programming language (PHP, JS, Python, etc.)
            'code_framework' => ['nullable', 'string', 'max:50'], // Framework (Laravel, React, Vue, etc.)
            'code_type' => ['nullable', 'string', 'in:plugin,script,library,component'],
            'photo_resolution' => ['nullable', 'string', 'max:50'], // e.g., "1920x1080", "4K", etc.
            'photo_type' => ['nullable', 'string', 'in:stock,portrait,landscape,product,event'],
            'photo_format' => ['nullable', 'string', 'in:jpeg,jpg,png,raw'],
            'design_type' => ['nullable', 'string', 'in:logo,flyer,icon,illustration,print,branding'],
            'design_format' => ['nullable', 'string', 'in:ai,psd,eps,pdf,svg'],
            'audio_duration' => ['nullable', 'integer', 'min:1'], // Duration in seconds
            'audio_format' => ['nullable', 'string', 'in:mp3,wav,flac,aac'],
            'audio_genre' => ['nullable', 'string', 'max:50'], // Music genre
            'video_duration' => ['nullable', 'integer', 'min:1'], // Duration in seconds
            'video_resolution' => ['nullable', 'string', 'max:50'], // e.g., "1920x1080", "4K", etc.
            'video_format' => ['nullable', 'string', 'in:mp4,mov,avi,webm'],
            'theme_platform' => ['nullable', 'string', 'in:wordpress,shopify,html,drupal,magento'],
            'theme_type' => ['nullable', 'string', 'in:business,ecommerce,blog,portfolio'],
            'three_d_format' => ['nullable', 'string', 'in:obj,fbx,blend,dae,3ds'],
            'three_d_type' => ['nullable', 'string', 'in:model,texture,rig,animation'],
            'language' => ['nullable', 'in:en,id,ar'],
            'scheduled_publish_at' => ['nullable', 'date'],
            'preview_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'thumbnails' => ['nullable', 'array', 'max:5'],
            'thumbnails.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB per image
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'is_public' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,sold,inactive'],
            'workspace_id' => ['nullable', 'exists:workspaces,id'],
            'folder_id' => ['nullable', 'exists:folders,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'attachments' => ['nullable', 'array', 'max:10'], // Maximum 10 files per note
            'attachments.*' => [
                'file',
                'max:' . ($maxFileSize / 1024), // in KB (10MB)
                'mimes:pdf,doc,docx,txt,zip,rar,jpg,jpeg,png,gif,xls,xlsx,ppt,pptx'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.max' => 'Maximum 10 files allowed per note.',
            'attachments.*.max' => 'File size exceeds 10MB limit.',
            'attachments.*.mimes' => 'File type not allowed. Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, XLS, XLSX, PPT, PPTX',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check file sizes manually for better error handling
            if ($this->hasFile('attachments')) {
                $files = $this->file('attachments');
                $maxSize = 10485760; // 10MB in bytes
                
                // Check maximum number of files
                if (count($files) > 10) {
                    $validator->errors()->add(
                        'attachments',
                        'Maximum 10 files allowed per note.'
                    );
                }
                
                foreach ($files as $index => $file) {
                    if ($file->getSize() > $maxSize) {
                        $sizeInMB = round($file->getSize() / 1048576, 2);
                        $validator->errors()->add(
                            "attachments.{$index}",
                            "File '{$file->getClientOriginalName()}' ({$sizeInMB}MB) exceeds 10MB limit."
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
