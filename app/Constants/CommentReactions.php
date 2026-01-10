<?php

namespace App\Constants;

class CommentReactions
{
    /**
     * Allowed emoji reactions for comments.
     */
    public const ALLOWED_EMOJIS = [
        '👍', // Thumbs up
        '❤️', // Heart
        '😂', // Laughing
        '🎉', // Party
        '🔥', // Fire
        '💡', // Light bulb
        '👏', // Clapping
        '🙌', // Raising hands
    ];

    /**
     * Check if emoji is allowed.
     */
    public static function isAllowed(string $emoji): bool
    {
        return in_array($emoji, self::ALLOWED_EMOJIS, true);
    }

    /**
     * Get all allowed emojis.
     */
    public static function getAllowed(): array
    {
        return self::ALLOWED_EMOJIS;
    }
}

