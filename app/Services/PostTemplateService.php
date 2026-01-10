<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostTemplate;
use App\Models\User;
use Carbon\Carbon;

class PostTemplateService
{
    /**
     * Process template and replace placeholders with actual values.
     *
     * @param string $template
     * @param User $user
     * @return string
     */
    public function processTemplate(string $template, User $user): string
    {
        $placeholders = $this->getPlaceholderValues($user);
        
        $processed = $template;
        foreach ($placeholders as $key => $value) {
            $processed = str_replace('{{' . $key . '}}', $value, $processed);
        }
        
        return $processed;
    }

    /**
     * Get available placeholders and their values.
     *
     * @param User $user
     * @return array
     */
    private function getPlaceholderValues(User $user): array
    {
        return [
            'date' => Carbon::now()->format('Y-m-d'),
            'datetime' => Carbon::now()->format('Y-m-d H:i:s'),
            'time' => Carbon::now()->format('H:i:s'),
            'user_name' => $user->name,
            'user_email' => $user->email,
            'business_name' => $user->business_name ?? '',
            'business_field' => $user->business_field ?? '',
        ];
    }

    /**
     * Get list of available placeholders.
     *
     * @return array
     */
    public function getAvailablePlaceholders(): array
    {
        return [
            'date' => 'Current date (Y-m-d)',
            'datetime' => 'Current date and time (Y-m-d H:i:s)',
            'time' => 'Current time (H:i:s)',
            'user_name' => 'User name',
            'user_email' => 'User email',
            'business_name' => 'Business name',
            'business_field' => 'Business field',
        ];
    }

    /**
     * Create a template from an existing post.
     *
     * @param Post $post
     * @param string $name
     * @param bool $isPublic
     * @return PostTemplate
     */
    public function createFromPost(Post $post, string $name, bool $isPublic = false): PostTemplate
    {
        return PostTemplate::create([
            'user_id' => $post->user_id,
            'name' => $name,
            'purpose_type' => $post->purpose_type,
            'title_template' => $post->title,
            'content_template' => $post->content,
            'is_public' => $isPublic,
            'usage_count' => 0,
        ]);
    }

    /**
     * Apply template to form data.
     *
     * @param PostTemplate $template
     * @param User $user
     * @return array
     */
    public function applyTemplate(PostTemplate $template, User $user): array
    {
        $title = $this->processTemplate($template->title_template, $user);
        $content = $this->processTemplate($template->content_template, $user);

        // Increment usage count
        $template->incrementUsage();

        return [
            'purpose_type' => $template->purpose_type,
            'title' => $title,
            'content' => $content,
        ];
    }
}

