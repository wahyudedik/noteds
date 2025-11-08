<?php

namespace App\Services;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class HashtagMentionService
{
    /**
     * Extract hashtags from text.
     */
    public function extractHashtags(string $text): array
    {
        $plainText = strip_tags($text);
        preg_match_all('/#(\w+)/u', $plainText, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Extract mentions from text.
     */
    public function extractMentions(string $text): array
    {
        $plainText = strip_tags($text);
        preg_match_all('/@(\w+)/u', $plainText, $matches);
        return array_unique($matches[1] ?? []);
    }

    /**
     * Process and attach hashtags to a post.
     */
    public function processHashtags(Post $post, string $content): void
    {
        $hashtags = $this->extractHashtags($content);
        
        $hashtagIds = [];
        foreach ($hashtags as $hashtagName) {
            $hashtag = Hashtag::findOrCreateByName($hashtagName);
            $hashtagIds[] = $hashtag->id;
        }
        
        // Sync hashtags (remove old, add new)
        $post->hashtags()->sync($hashtagIds);
        
        // Update posts_count for affected hashtags
        foreach ($hashtagIds as $hashtagId) {
            $hashtag = Hashtag::find($hashtagId);
            if ($hashtag) {
                $hashtag->update(['posts_count' => $hashtag->posts()->count()]);
            }
        }
    }

    /**
     * Process and attach mentions to a post.
     */
    public function processMentions(Post $post, string $content): void
    {
        $usernames = $this->extractMentions($content);
        
        $userIds = [];
        foreach ($usernames as $username) {
            $user = User::where('username', $username)->first();
            if ($user && $user->id !== $post->user_id) { // Don't mention yourself
                $userIds[] = $user->id;
            }
        }
        
        // Sync mentions
        $post->mentions()->sync($userIds);
    }

    /**
     * Format content with clickable hashtags and mentions.
     */
    public function formatContent(string $content): string
    {
        $document = new \DOMDocument();
        libxml_use_internal_errors(true);

        $wrappedHtml = '<div>' . $content . '</div>';
        $document->loadHTML(
            mb_convert_encoding($wrappedHtml, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $xpath = new \DOMXPath($document);
        foreach ($xpath->query('//text()') as $textNode) {
            /** @var \DOMText $textNode */
            $original = $textNode->nodeValue;
            $replaced = $this->linkifyText($original);

            if ($replaced !== htmlspecialchars($original, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) {
                $fragment = $document->createDocumentFragment();
                $fragment->appendXML($replaced);
                $textNode->parentNode->replaceChild($fragment, $textNode);
            }
        }

        $formatted = '';
        foreach ($document->documentElement->childNodes as $child) {
            $formatted .= $document->saveHTML($child);
        }

        libxml_clear_errors();

        return $formatted;
    }

    protected function linkifyText(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $escaped = preg_replace_callback('/#(\w+)/u', function ($matches) {
            $hashtag = $matches[1];
            $slug = Hashtag::generateSlug($hashtag);
            $label = htmlspecialchars($hashtag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return '<a href="' . route('forum.hashtag', $slug) . '" class="text-blue-600 hover:text-blue-800 font-medium">#' . $label . '</a>';
        }, $escaped);

        $escaped = preg_replace_callback('/@(\w+)/u', function ($matches) {
            $username = $matches[1];
            $user = User::where('username', $username)->first();

            if ($user) {
                $label = htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                return '<a href="' . route('public.profile.show', $username) . '" class="text-blue-600 hover:text-blue-800 font-medium">@' . $label . '</a>';
            }

            return '@' . htmlspecialchars($username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $escaped);

        return $escaped;
    }
}

