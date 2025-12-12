<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandingPageSectionRequest extends FormRequest
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
            'section_type' => ['required', 'in:hero,features,how_it_works,premium_benefits,trust_indicators,testimonials,promo,cms_pages,custom'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'array'], // JSON content structure
            'image_url' => ['nullable', 'url', 'max:500'],
            'background_color' => ['nullable', 'string', 'max:50'],
            'text_color' => ['nullable', 'string', 'max:50'],
            'alignment' => ['nullable', 'in:left,center,right'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'section_type.required' => 'Please select a section type.',
            'content.required' => 'Section content is required.',
            'valid_until.after_or_equal' => 'Valid until date must be after or equal to valid from date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert JSON string content to array
        if ($this->has('content') && is_string($this->input('content'))) {
            try {
                $content = json_decode($this->input('content'), true);
                $this->merge(['content' => $content ?: []]);
            } catch (\Exception $e) {
                $this->merge(['content' => []]);
            }
        }
    }
}
