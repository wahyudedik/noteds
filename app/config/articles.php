<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RSS Feed Sources
    |--------------------------------------------------------------------------
    |
    | List of RSS feed URLs for business-related content.
    | These feeds will be parsed and synced to the articles table.
    |
    */
    'rss_feeds' => [
        [
            'url' => 'https://www.businessinsider.com/rss',
            'source' => 'Business Insider',
            'category' => 'Business',
            'language' => 'en',
        ],
        [
            'url' => 'https://www.entrepreneur.com/latest.rss',
            'source' => 'Entrepreneur',
            'category' => 'Entrepreneurship',
            'language' => 'en',
        ],
        [
            'url' => 'https://techcrunch.com/feed/',
            'source' => 'TechCrunch',
            'category' => 'Technology',
            'language' => 'en',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reddit Subreddits
    |--------------------------------------------------------------------------
    |
    | Reddit subreddits to fetch business-related content from.
    | These will be converted to articles.
    |
    */
    'reddit_subreddits' => [
        [
            'subreddit' => 'entrepreneur',
            'source' => 'Reddit - Entrepreneur',
            'category' => 'Entrepreneurship',
            'min_upvotes' => 10, // Minimum upvotes to include
        ],
        [
            'subreddit' => 'startups',
            'source' => 'Reddit - Startups',
            'category' => 'Startup',
            'min_upvotes' => 10,
        ],
        [
            'subreddit' => 'smallbusiness',
            'source' => 'Reddit - Small Business',
            'category' => 'Business',
            'min_upvotes' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for article synchronization.
    |
    */
    'sync' => [
        'max_articles_per_source' => 25,
        'deduplicate_by_url' => true,
        'auto_categorize' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Mapping
    |--------------------------------------------------------------------------
    |
    | Map categories from external sources to internal categories.
    |
    */
    'category_mapping' => [
        'business' => 'Business',
        'entrepreneurship' => 'Entrepreneurship',
        'startup' => 'Startup',
        'technology' => 'Technology',
        'finance' => 'Finance',
        'marketing' => 'Marketing',
        'strategy' => 'Strategy',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache configuration for articles.
    |
    */
    'cache' => [
        'articles_list_ttl' => 900, // 15 minutes
        'categories_ttl' => 3600, // 1 hour
    ],
];

