<?php

namespace App\Services;

use App\Models\PostDraft;
use Illuminate\Support\Facades\Auth;

class DraftService
{
    /**
     * Auto-save a draft.
     *
     * @param array $data
     * @param string|null $draftId
     * @return PostDraft
     */
    public function autoSave(array $data, ?string $draftId = null): PostDraft
    {
        $user = Auth::user();
        
        if ($draftId) {
            $draft = PostDraft::where('id', $draftId)
                ->where('user_id', $user->id)
                ->first();
            
            if ($draft) {
                $draft->update([
                    'title' => $data['title'] ?? '',
                    'content' => $data['content'] ?? '',
                    'purpose_type' => $data['purpose_type'] ?? null,
                    'images_data' => $data['images'] ?? null,
                    'link_data' => $data['link_url'] ? [
                        'url' => $data['link_url'],
                        'title' => $data['link_preview_title'] ?? null,
                        'description' => $data['link_preview_description'] ?? null,
                        'image' => $data['link_preview_image'] ?? null,
                        'site_name' => $data['link_preview_site_name'] ?? null,
                    ] : null,
                    'auto_saved_at' => now(),
                ]);
                
                return $draft;
            }
        }
        
        // Create new draft
        return PostDraft::create([
            'user_id' => $user->id,
            'title' => $data['title'] ?? '',
            'content' => $data['content'] ?? '',
            'purpose_type' => $data['purpose_type'] ?? null,
            'images_data' => $data['images'] ?? null,
            'link_data' => $data['link_url'] ? [
                'url' => $data['link_url'],
                'title' => $data['link_preview_title'] ?? null,
                'description' => $data['link_preview_description'] ?? null,
                'image' => $data['link_preview_image'] ?? null,
                'site_name' => $data['link_preview_site_name'] ?? null,
            ] : null,
            'auto_saved_at' => now(),
        ]);
    }

    /**
     * Publish a draft as a post.
     *
     * @param PostDraft $draft
     * @return \App\Models\Post
     */
    public function publish(PostDraft $draft): \App\Models\Post
    {
        $post = \App\Models\Post::create([
            'user_id' => $draft->user_id,
            'title' => $draft->title,
            'content' => $draft->content,
            'purpose_type' => $draft->purpose_type,
            'link_url' => $draft->link_data['url'] ?? null,
            'link_preview_title' => $draft->link_data['title'] ?? null,
            'link_preview_description' => $draft->link_data['description'] ?? null,
            'link_preview_image' => $draft->link_data['image'] ?? null,
            'link_preview_site_name' => $draft->link_data['site_name'] ?? null,
        ]);

        // Handle images if any
        if ($draft->images_data && is_array($draft->images_data)) {
            foreach ($draft->images_data as $imageData) {
                // Copy image from draft storage to post storage
                // This would need to be implemented based on your storage setup
            }
        }

        // Delete draft after publishing
        $draft->delete();

        return $post;
    }
}


