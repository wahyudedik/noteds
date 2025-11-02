<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSocialMediaLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $socialMediaId = $this->route('social_media')->id;

        return [
            'platform' => ['required', 'string', 'max:50', Rule::unique('social_media_links', 'platform')->ignore($socialMediaId)],
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:500'],
            'icon' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:7'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
