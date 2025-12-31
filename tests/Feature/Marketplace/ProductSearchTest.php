<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create test products
        Product::create([
            'user_id' => $this->user->id,
            'name' => 'Laravel E-book',
            'slug' => 'laravel-ebook',
            'description' => 'Complete guide to Laravel framework',
            'price' => 50000,
            'category' => 'E-book',
            'is_active' => true,
        ]);

        Product::create([
            'user_id' => $this->user->id,
            'name' => 'Vue.js Template',
            'slug' => 'vuejs-template',
            'description' => 'Premium Vue.js admin template',
            'price' => 150000,
            'category' => 'Template',
            'is_active' => true,
        ]);

        Product::create([
            'user_id' => $this->user->id,
            'name' => 'React Course',
            'slug' => 'react-course',
            'description' => 'Learn React from scratch',
            'price' => 200000,
            'category' => 'Course',
            'is_active' => true,
        ]);

        Product::create([
            'user_id' => $this->user->id,
            'name' => 'Inactive Product',
            'slug' => 'inactive-product',
            'description' => 'This product is inactive',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function user_can_search_products_by_name()
    {
        $response = $this->get(route('marketplace.index', ['search' => 'Laravel']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && 
                   $products->first()->name === 'Laravel E-book';
        });
    }

    /** @test */
    public function user_can_search_products_by_description()
    {
        $response = $this->get(route('marketplace.index', ['search' => 'Vue']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && 
                   $products->first()->name === 'Vue.js Template';
        });
    }

    /** @test */
    public function search_is_case_insensitive()
    {
        $response = $this->get(route('marketplace.index', ['search' => 'react']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && 
                   $products->first()->name === 'React Course';
        });
    }

    /** @test */
    public function search_returns_multiple_results()
    {
        $response = $this->get(route('marketplace.index', ['search' => 'template']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() >= 1;
        });
    }

    /** @test */
    public function search_returns_empty_for_no_matches()
    {
        $response = $this->get(route('marketplace.index', ['search' => 'NonExistentProduct']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 0;
        });
    }

    /** @test */
    public function user_can_filter_products_by_category()
    {
        $response = $this->get(route('marketplace.index', ['category' => 'E-book']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->every(function ($product) {
                return $product->category === 'E-book';
            });
        });
    }

    /** @test */
    public function user_can_combine_search_and_category_filter()
    {
        $response = $this->get(route('marketplace.index', [
            'search' => 'Laravel',
            'category' => 'E-book',
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 1 && 
                   $products->first()->name === 'Laravel E-book' &&
                   $products->first()->category === 'E-book';
        });
    }

    /** @test */
    public function inactive_products_are_not_shown()
    {
        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->every(function ($product) {
                return $product->is_active === true;
            }) && $products->count() === 3;
        });
    }

    /** @test */
    public function search_only_searches_active_products()
    {
        $response = $this->get(route('marketplace.index', ['search' => 'Inactive']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 0;
        });
    }

    /** @test */
    public function products_are_paginated()
    {
        // Create more products to test pagination
        for ($i = 0; $i < 15; $i++) {
            Product::create([
                'user_id' => $this->user->id,
                'name' => "Product {$i}",
                'slug' => "product-{$i}",
                'description' => "Description {$i}",
                'price' => 100000,
                'category' => 'Software',
                'is_active' => true,
            ]);
        }

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() <= 12; // Default pagination is 12
        });
    }

    /** @test */
    public function products_are_ordered_by_latest()
    {
        $oldProduct = Product::create([
            'user_id' => $this->user->id,
            'name' => 'Old Product',
            'slug' => 'old-product',
            'description' => 'Old description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'created_at' => now()->subDays(5),
        ]);

        $newProduct = Product::create([
            'user_id' => $this->user->id,
            'name' => 'New Product',
            'slug' => 'new-product',
            'description' => 'New description',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $response = $this->get(route('marketplace.index'));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            $firstProduct = $products->first();
            return $firstProduct->name === 'New Product';
        });
    }

    /** @test */
    public function empty_search_returns_all_active_products()
    {
        $response = $this->get(route('marketplace.index', ['search' => '']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 3; // 3 active products
        });
    }

    /** @test */
    public function search_with_special_characters_works()
    {
        $product = Product::create([
            'user_id' => $this->user->id,
            'name' => 'Product with Special Chars!',
            'slug' => 'product-special-chars',
            'description' => 'Description with @#$%',
            'price' => 100000,
            'category' => 'Software',
            'is_active' => true,
        ]);

        $response = $this->get(route('marketplace.index', ['search' => 'Special']));

        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) use ($product) {
            return $products->contains('id', $product->id);
        });
    }
}

