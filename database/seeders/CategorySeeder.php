<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Parent Categories
        $business = Category::create([
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'Business and entrepreneurship notes',
            'icon' => 'briefcase',
            'order' => 1,
            'is_active' => true,
        ]);

        $education = Category::create([
            'name' => 'Education',
            'slug' => 'education',
            'description' => 'Educational content and study materials',
            'icon' => 'book',
            'order' => 2,
            'is_active' => true,
        ]);

        $technology = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Technology and programming notes',
            'icon' => 'laptop',
            'order' => 3,
            'is_active' => true,
        ]);

        $personal = Category::create([
            'name' => 'Personal Development',
            'slug' => 'personal-development',
            'description' => 'Personal growth and self-improvement',
            'icon' => 'user',
            'order' => 4,
            'is_active' => true,
        ]);

        $creative = Category::create([
            'name' => 'Creative',
            'slug' => 'creative',
            'description' => 'Creative writing and art notes',
            'icon' => 'palette',
            'order' => 5,
            'is_active' => true,
        ]);

        // Subcategories for Business
        Category::create([
            'parent_id' => $business->id,
            'name' => 'Marketing',
            'slug' => 'marketing',
            'description' => 'Marketing strategies and tips',
            'order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $business->id,
            'name' => 'Finance',
            'slug' => 'finance',
            'description' => 'Financial planning and management',
            'order' => 2,
            'is_active' => true,
        ]);

        // Subcategories for Education
        Category::create([
            'parent_id' => $education->id,
            'name' => 'Study Notes',
            'slug' => 'study-notes',
            'description' => 'Academic study notes',
            'order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $education->id,
            'name' => 'Tutorials',
            'slug' => 'tutorials',
            'description' => 'Step-by-step tutorials',
            'order' => 2,
            'is_active' => true,
        ]);

        // Subcategories for Technology
        Category::create([
            'parent_id' => $technology->id,
            'name' => 'Programming',
            'slug' => 'programming',
            'description' => 'Programming and coding notes',
            'order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $technology->id,
            'name' => 'Web Development',
            'slug' => 'web-development',
            'description' => 'Web development resources',
            'order' => 2,
            'is_active' => true,
        ]);
    }
}

