<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:documentations,slug'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'in:wiki,screenshot_guide,link_reference,troubleshooting,api_documentation,video_tutorial'],
            'icon' => ['nullable', 'string', 'max:100'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'links' => ['nullable', 'array'],
            'links.*.title' => ['required_with:links', 'string', 'max:255'],
            'links.*.url' => ['required_with:links', 'url'],
            'screenshots' => ['nullable', 'array'],
            'screenshots.*' => ['string'],
            'video_urls' => ['nullable', 'array'],
            'video_urls.*' => ['url'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Documentation title is required.',
            'content.required' => 'Documentation content is required.',
            'category.required' => 'Please select a category.',
            'slug.unique' => 'This slug is already in use. Please choose a different one.',
        ];
    }
}
