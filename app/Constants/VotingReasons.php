<?php

namespace App\Constants;

class VotingReasons
{
    /**
     * Upvote reasons
     */
    public const HELPFUL = 'helpful';
    public const ACCURATE = 'accurate';
    public const WELL_WRITTEN = 'well_written';
    public const INFORMATIVE = 'informative';

    /**
     * Downvote reasons
     */
    public const MISLEADING = 'misleading';
    public const INACCURATE = 'inaccurate';
    public const SPAM = 'spam';
    public const OFF_TOPIC = 'off_topic';

    /**
     * Get all upvote reasons.
     */
    public static function upvoteReasons(): array
    {
        return [
            self::HELPFUL => 'Helpful',
            self::ACCURATE => 'Accurate',
            self::WELL_WRITTEN => 'Well Written',
            self::INFORMATIVE => 'Informative',
        ];
    }

    /**
     * Get all downvote reasons.
     */
    public static function downvoteReasons(): array
    {
        return [
            self::MISLEADING => 'Misleading',
            self::INACCURATE => 'Inaccurate',
            self::SPAM => 'Spam',
            self::OFF_TOPIC => 'Off Topic',
        ];
    }

    /**
     * Get all reasons.
     */
    public static function all(): array
    {
        return array_merge(self::upvoteReasons(), self::downvoteReasons());
    }

    /**
     * Get reasons for a specific vote type.
     */
    public static function forVoteType(string $voteType): array
    {
        return $voteType === 'upvote' ? self::upvoteReasons() : self::downvoteReasons();
    }

    /**
     * Check if a reason is valid for a vote type.
     */
    public static function isValidForVoteType(string $reason, string $voteType): bool
    {
        $validReasons = self::forVoteType($voteType);
        return array_key_exists($reason, $validReasons);
    }

    /**
     * Get all valid reason keys.
     */
    public static function allKeys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get upvote reason keys.
     */
    public static function upvoteKeys(): array
    {
        return array_keys(self::upvoteReasons());
    }

    /**
     * Get downvote reason keys.
     */
    public static function downvoteKeys(): array
    {
        return array_keys(self::downvoteReasons());
    }
}

