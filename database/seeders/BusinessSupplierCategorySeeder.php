<?php

namespace Database\Seeders;

use App\Models\BusinessSupplierCategory;
use Illuminate\Database\Seeder;

class BusinessSupplierCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'business_type' => 'motor_listrik',
                'business_name' => 'Produksi Motor Listrik',
                'description' => 'Bisnis pembuatan atau produksi motor listrik',
                'keywords' => ['motor listrik', 'electric motor', 'e-bike', 'scooter listrik', 'kendaraan listrik', 'ev'],
                'is_active' => true,
            ],
            [
                'business_type' => 'toko_kelontong',
                'business_name' => 'Toko Kelontong',
                'description' => 'Bisnis toko kelontong atau warung sembako',
                'keywords' => ['toko kelontong', 'warung', 'mini market', 'toko sembako', 'toko kebutuhan sehari-hari'],
                'is_active' => true,
            ],
            [
                'business_type' => 'cafe',
                'business_name' => 'Cafe / Kedai Kopi',
                'description' => 'Bisnis cafe atau kedai kopi',
                'keywords' => ['cafe', 'kedai kopi', 'coffee shop', 'warung kopi', 'kafe'],
                'is_active' => true,
            ],
            [
                'business_type' => 'resto',
                'business_name' => 'Restoran',
                'description' => 'Bisnis restoran atau rumah makan',
                'keywords' => ['restoran', 'rumah makan', 'restaurant', 'warung makan'],
                'is_active' => true,
            ],
            [
                'business_type' => 'fashion',
                'business_name' => 'Fashion / Pakaian',
                'description' => 'Bisnis fashion, pakaian, atau apparel',
                'keywords' => ['fashion', 'pakaian', 'baju', 'apparel', 'clothing', 'garmen'],
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            BusinessSupplierCategory::create($category);
        }
    }
}
