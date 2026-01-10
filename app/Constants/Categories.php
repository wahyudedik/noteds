<?php

namespace App\Constants;

class Categories
{
    /**
     * Category slugs and their display names.
     */
    public const TECHNOLOGY = 'technology';
    public const BUSINESS = 'business';
    public const DESIGN = 'design';
    public const MARKETING = 'marketing';
    public const FINANCE = 'finance';
    public const EDUCATION = 'education';
    public const HEALTH = 'health';
    public const FOOD = 'food';
    public const TRAVEL = 'travel';
    public const OTHER = 'other';

    /**
     * Get all categories with their display names.
     */
    public static function all(): array
    {
        return [
            self::TECHNOLOGY => 'Technology',
            self::BUSINESS => 'Business & Entrepreneurship',
            self::DESIGN => 'Design & Creative',
            self::MARKETING => 'Marketing & Sales',
            self::FINANCE => 'Finance & Investment',
            self::EDUCATION => 'Education & Learning',
            self::HEALTH => 'Health & Wellness',
            self::FOOD => 'Food & Beverage',
            self::TRAVEL => 'Travel & Lifestyle',
            self::OTHER => 'Other',
        ];
    }

    /**
     * Get category icon mappings.
     */
    public static function icons(): array
    {
        return [
            self::TECHNOLOGY => '💻',
            self::BUSINESS => '💼',
            self::DESIGN => '🎨',
            self::MARKETING => '📢',
            self::FINANCE => '💰',
            self::EDUCATION => '📚',
            self::HEALTH => '🏥',
            self::FOOD => '🍔',
            self::TRAVEL => '✈️',
            self::OTHER => '📦',
        ];
    }

    /**
     * Get category descriptions.
     */
    public static function descriptions(): array
    {
        return [
            self::TECHNOLOGY => 'Software development, programming, IT, and tech innovation',
            self::BUSINESS => 'Entrepreneurship, startups, business strategy, and management',
            self::DESIGN => 'Graphic design, UI/UX, creative arts, and visual communication',
            self::MARKETING => 'Digital marketing, advertising, brand promotion, and sales',
            self::FINANCE => 'Investing, financial planning, accounting, and wealth management',
            self::EDUCATION => 'Teaching, learning, courses, and educational content',
            self::HEALTH => 'Fitness, wellness, healthcare, and healthy living',
            self::FOOD => 'Cooking, restaurants, food trends, and culinary arts',
            self::TRAVEL => 'Travel guides, destinations, lifestyle, and adventures',
            self::OTHER => 'General topics and other interests',
        ];
    }

    /**
     * Get all category slugs.
     */
    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get category display name by slug.
     */
    public static function getName(string $slug): ?string
    {
        return self::all()[$slug] ?? null;
    }

    /**
     * Get category icon by slug.
     */
    public static function getIcon(string $slug): ?string
    {
        return self::icons()[$slug] ?? null;
    }

    /**
     * Get category description by slug.
     */
    public static function getDescription(string $slug): ?string
    {
        return self::descriptions()[$slug] ?? null;
    }

    /**
     * Check if slug is valid.
     */
    public static function isValid(string $slug): bool
    {
        return array_key_exists($slug, self::all());
    }

    /**
     * Business field to category mappings.
     */
    public static function businessFieldMappings(): array
    {
        return [
            'technology' => [self::TECHNOLOGY],
            'software' => [self::TECHNOLOGY],
            'it' => [self::TECHNOLOGY],
            'programming' => [self::TECHNOLOGY],
            'business' => [self::BUSINESS],
            'entrepreneurship' => [self::BUSINESS],
            'startup' => [self::BUSINESS],
            'design' => [self::DESIGN],
            'graphic design' => [self::DESIGN],
            'ui/ux' => [self::DESIGN],
            'marketing' => [self::MARKETING],
            'digital marketing' => [self::MARKETING],
            'sales' => [self::MARKETING],
            'finance' => [self::FINANCE],
            'investment' => [self::FINANCE],
            'education' => [self::EDUCATION],
            'health' => [self::HEALTH],
            'fitness' => [self::HEALTH],
            'food' => [self::FOOD],
            'restaurant' => [self::FOOD],
            'travel' => [self::TRAVEL],
            'lifestyle' => [self::TRAVEL],
        ];
    }

    /**
     * Skills to category mappings.
     */
    public static function skillMappings(): array
    {
        return [
            'javascript' => [self::TECHNOLOGY],
            'php' => [self::TECHNOLOGY],
            'python' => [self::TECHNOLOGY],
            'react' => [self::TECHNOLOGY],
            'vue' => [self::TECHNOLOGY],
            'laravel' => [self::TECHNOLOGY],
            'nodejs' => [self::TECHNOLOGY],
            'programming' => [self::TECHNOLOGY],
            'photoshop' => [self::DESIGN],
            'illustrator' => [self::DESIGN],
            'figma' => [self::DESIGN],
            'ui design' => [self::DESIGN],
            'ux design' => [self::DESIGN],
            'seo' => [self::MARKETING],
            'sem' => [self::MARKETING],
            'social media' => [self::MARKETING],
            'content marketing' => [self::MARKETING],
            'accounting' => [self::FINANCE],
            'financial planning' => [self::FINANCE],
            'teaching' => [self::EDUCATION],
            'coaching' => [self::EDUCATION],
        ];
    }
}

