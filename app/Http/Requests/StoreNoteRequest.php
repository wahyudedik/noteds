<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $maxFileSize = auth()->user() && auth()->user()->hasPremium() ? 52428800 : 5242880; // 50MB for premium, 5MB for basic
        
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'summary' => ['nullable', 'string', 'max:500'],
            'preview_content' => ['nullable', 'string', 'max:300'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_public' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:active,sold,inactive'],
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
                'attachments.*.max' => 'File size exceeds 5MB limit for Basic users. Upgrade to Premium to upload files up to 50MB.',
                'attachments.*.mimes' => 'File type not allowed. Allowed: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, XLS, XLSX, PPT, PPTX',
            ];
        }
        
        return [
            'attachments.*.max' => 'File size exceeds 50MB limit.',
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
                            "File '{$file->getClientOriginalName()}' ({$sizeInMB}MB) exceeds 5MB limit for Basic users. <a href='" . route('subscription.create') . "' class='font-semibold text-blue-600 hover:text-blue-700 underline'>Upgrade to Premium</a> to upload files up to 50MB."
                        );
                    }
                }
            }
        });
    }
}
