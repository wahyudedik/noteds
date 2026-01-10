<?php

namespace Database\Seeders;

use App\Constants\Categories;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryData = Categories::all();
        $icons = Categories::icons();
        $descriptions = Categories::descriptions();

        $sortOrder = 0;
        foreach ($categoryData as $slug => $name) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $descriptions[$slug] ?? null,
                    'icon' => $icons[$slug] ?? null,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );
        }
    }
}
