<?php

namespace App\Http\Requests;

use App\Services\UrlValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreClipRequest extends FormRequest
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
            'campaign_id' => 'required|exists:campaigns,id',
            'content_url' => 'required|url|max:2048',
            'platform' => 'required|in:tiktok,instagram,youtube,other',
            'platform_content_id' => 'nullable|string|max:255',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $urlValidationService = app(UrlValidationService::class);
            
            $url = $this->input('content_url');
            $platform = $this->input('platform');

            if ($url && $platform) {
                $validation = $urlValidationService->validateContentUrl($url, $platform);
                
                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $error) {
                        $validator->errors()->add('content_url', $error);
                    }
                }
            }
        });
    }

    /**
     * Get validated data with sanitized URL.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        
        // Sanitize URL
        if (isset($validated['content_url'])) {
            $urlValidationService = app(UrlValidationService::class);
            $validated['content_url'] = $urlValidationService->sanitizeUrl($validated['content_url']);
        }

        return $validated;
    }
}

