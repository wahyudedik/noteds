<?php

namespace App\Services;

use App\Constants\Categories;
use App\Models\Category;
use App\Models\User;
use App\Models\UserCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CategoryInferenceService
{
    /**
     * Infer categories from user profile.
     */
    public function inferCategoriesFromProfile(User $user): Collection
    {
        $categories = collect();

        // Infer from business_field
        if ($user->business_field) {
            $businessCategories = $this->matchBusinessFieldToCategories($user->business_field);
            $categories = $categories->merge($businessCategories);
        }

        // Infer from skills
        if ($user->skills && is_array($user->skills)) {
            $skillCategories = $this->matchSkillsToCategories($user->skills);
            $categories = $categories->merge($skillCategories);
        }

        // Infer from posts
        $postCategories = $this->analyzePostsForCategories($user);
        $categories = $categories->merge($postCategories);

        // Get unique categories with highest confidence
        return $categories->groupBy('id')
            ->map(function ($group) {
                // Return category with highest confidence
                return $group->sortByDesc('confidence')->first();
            })
            ->values();
    }

    /**
     * Match business_field to categories.
     */
    public function matchBusinessFieldToCategories(string $businessField): Collection
    {
        $businessFieldLower = Str::lower($businessField);
        $mappings = Categories::businessFieldMappings();
        $categories = collect();

        foreach ($mappings as $keyword => $categorySlugs) {
            if (Str::contains($businessFieldLower, $keyword)) {
                foreach ($categorySlugs as $slug) {
                    $category = Category::where('slug', $slug)->first();
                    if ($category && !$categories->contains('id', $category->id)) {
                        $categories->push([
                            'id' => $category->id,
                            'category' => $category,
                            'confidence' => 0.8, // High confidence for business_field match
                            'source' => 'business_field',
                        ]);
                    }
                }
            }
        }

        return $categories;
    }

    /**
     * Match skills array to categories.
     */
    public function matchSkillsToCategories(array $skills): Collection
    {
        $mappings = Categories::skillMappings();
        $categories = collect();

        foreach ($skills as $skill) {
            $skillLower = Str::lower($skill);
            foreach ($mappings as $keyword => $categorySlugs) {
                if (Str::contains($skillLower, $keyword)) {
                    foreach ($categorySlugs as $slug) {
                        $category = Category::where('slug', $slug)->first();
                        if ($category && !$categories->contains('id', $category->id)) {
                            $existing = $categories->firstWhere('id', $category->id);
                            if ($existing) {
                                // Increase confidence if already found
                                $existing['confidence'] = min(1.0, $existing['confidence'] + 0.2);
                            } else {
                                $categories->push([
                                    'id' => $category->id,
                                    'category' => $category,
                                    'confidence' => 0.6, // Medium confidence for skills
                                    'source' => 'skills',
                                ]);
                            }
                        }
                    }
                }
            }
        }

        return $categories;
    }

    /**
     * Analyze posts to determine categories.
     */
    public function analyzePostsForCategories(User $user): Collection
    {
        $posts = $user->posts()->with('hashtags')->get();
        $categories = collect();

        if ($posts->isEmpty()) {
            return $categories;
        }

        // Analyze purpose_type distribution
        $purposeTypes = $posts->groupBy('purpose_type');
        $purposeTypeCategoryMap = [
            'idea_business' => Categories::BUSINESS,
            'ask_question' => Categories::EDUCATION,
            'share_experience' => Categories::OTHER,
            'find_partner' => Categories::BUSINESS,
            'find_tools' => Categories::TECHNOLOGY,
            'validate_idea' => Categories::BUSINESS,
        ];

        foreach ($purposeTypeCategoryMap as $purposeType => $categorySlug) {
            if (isset($purposeTypes[$purposeType]) && $purposeTypes[$purposeType]->count() > 0) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category && !$categories->contains('id', $category->id)) {
                    $count = $purposeTypes[$purposeType]->count();
                    $total = $posts->count();
                    $confidence = min(0.7, ($count / $total) * 0.9); // Based on post frequency

                    $categories->push([
                        'id' => $category->id,
                        'category' => $category,
                        'confidence' => $confidence,
                        'source' => 'posts',
                    ]);
                }
            }
        }

        // Analyze hashtags (if any)
        $allHashtags = $posts->pluck('hashtags')->flatten()->pluck('name');
        // Simple keyword matching on hashtags
        $hashtagCategoryMap = [
            'tech' => Categories::TECHNOLOGY,
            'business' => Categories::BUSINESS,
            'design' => Categories::DESIGN,
            'marketing' => Categories::MARKETING,
            'finance' => Categories::FINANCE,
        ];

        foreach ($hashtagCategoryMap as $keyword => $categorySlug) {
            $matches = $allHashtags->filter(fn($tag) => Str::contains(Str::lower($tag), $keyword));
            if ($matches->count() > 0) {
                $category = Category::where('slug', $categorySlug)->first();
                if ($category && !$categories->contains('id', $category->id)) {
                    $confidence = min(0.65, ($matches->count() / max($allHashtags->count(), 1)) * 0.8);
                    $categories->push([
                        'id' => $category->id,
                        'category' => $category,
                        'confidence' => $confidence,
                        'source' => 'hashtags',
                    ]);
                }
            }
        }

        return $categories;
    }

    /**
     * Update user categories (inferred only, preserves manual).
     */
    public function updateUserCategories(User $user): void
    {
        // Get inferred categories
        $inferredCategories = $this->inferCategoriesFromProfile($user);

        // Get existing manual categories (preserve them)
        $manualCategoryIds = $user->manualCategories()->pluck('categories.id')->toArray();

        // Delete existing inferred categories
        UserCategory::where('user_id', $user->id)
            ->where('source', 'inferred')
            ->delete();

        // Create new inferred categories
        foreach ($inferredCategories as $inferred) {
            if (!in_array($inferred['id'], $manualCategoryIds)) {
                UserCategory::create([
                    'user_id' => $user->id,
                    'category_id' => $inferred['id'],
                    'source' => 'inferred',
                    'confidence' => $inferred['confidence'],
                ]);
            }
        }
    }
}

