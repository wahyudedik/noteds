<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => ['nullable', 'string', 'max:255'],
            'avatar_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:100'],
            'document_type' => ['nullable', 'in:ktp,kartu_pelajar'],
            'ktp_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Enhanced file upload security validation
            if ($this->hasFile('avatar_file')) {
                $securityService = app(\App\Services\FileUploadSecurityService::class);
                $validation = $securityService->validateFile($this->file('avatar_file'), 'image');
                
                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $error) {
                        $validator->errors()->add('avatar_file', $error);
                    }
                }
            }
            
            if ($this->hasFile('ktp_file')) {
                $securityService = app(\App\Services\FileUploadSecurityService::class);
                $validation = $securityService->validateFile($this->file('ktp_file'), 'document');
                
                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $error) {
                        $validator->errors()->add('ktp_file', $error);
                    }
                }
            }
            
            if ($this->hasFile('selfie_file')) {
                $securityService = app(\App\Services\FileUploadSecurityService::class);
                $validation = $securityService->validateFile($this->file('selfie_file'), 'image');
                
                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $error) {
                        $validator->errors()->add('selfie_file', $error);
                    }
                }
            }
        });
    }
}
