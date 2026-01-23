<?php

namespace App\Http\Requests;

use App\Services\ModerationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'purpose_type' => ['required', 'in:idea_business,ask_question,share_experience,find_partner,find_tools,validate_idea'],
            'business_type' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'content' => ['required', 'string', 'min:50'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:2048', 'mimes:jpeg,jpg,png,gif,webp'],
            'link_url' => ['nullable', 'url', 'max:2048'],
            'link_preview_title' => ['nullable', 'string', 'max:255'],
            'link_preview_description' => ['nullable', 'string', 'max:1000'],
            'link_preview_image' => ['nullable', 'string', 'max:2048'],
            'link_preview_site_name' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'timezone' => ['nullable', 'timezone'],
            'publish_status' => ['nullable', 'in:draft,scheduled,published'],
            'template_id' => ['nullable', 'exists:post_templates,id'],
            'series_id' => ['nullable', 'exists:posts,id'],
            'collaborators' => ['nullable', 'array'],
            'collaborators.*.user_id' => ['required_with:collaborators', 'exists:users,id'],
            'collaborators.*.role' => ['nullable', 'in:co_author,editor,contributor'],
            'collaborators.*.can_edit' => ['nullable', 'boolean'],
            'collaborators.*.can_publish' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $moderationService = new ModerationService();
            
            $titleIssues = $moderationService->checkContent($this->title);
            $contentIssues = $moderationService->checkContent($this->content);

            if (!empty($titleIssues)) {
                $validator->errors()->add('title', 'Content must be business-focused and relevant. Personal drama or unrelated content is not allowed.');
            }

            if (!empty($contentIssues)) {
                $validator->errors()->add('content', 'Content must be business-focused and relevant. Personal drama or unrelated content is not allowed.');
            }
        });
    }
}
