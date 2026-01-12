<?php

namespace Database\Seeders;

use App\Models\BusinessSupplierMapping;
use Illuminate\Database\Seeder;

class BusinessSupplierMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mappings = [
            // Motor Listrik
            [
                'business_type' => 'motor_listrik',
                'supplier_category' => 'spare_part',
                'category_label' => 'Spare Part Motor Listrik',
                'priority_order' => 1,
                'recommendation_note' => 'Tempat jualan sparepart motor listrik yang bagus',
                'is_active' => true,
            ],
            [
                'business_type' => 'motor_listrik',
                'supplier_category' => 'ban',
                'category_label' => 'Ban Motor',
                'priority_order' => 2,
                'recommendation_note' => 'Tempat jualan ban yang bagus dan berkualitas',
                'is_active' => true,
            ],
            [
                'business_type' => 'motor_listrik',
                'supplier_category' => 'sticker',
                'category_label' => 'Sticker Custom',
                'priority_order' => 3,
                'recommendation_note' => 'Tempat jualan stiker custom untuk motor',
                'is_active' => true,
            ],
            [
                'business_type' => 'motor_listrik',
                'supplier_category' => 'baterai',
                'category_label' => 'Baterai Lithium',
                'priority_order' => 4,
                'recommendation_note' => 'Supplier baterai lithium untuk motor listrik',
                'is_active' => true,
            ],
            [
                'business_type' => 'motor_listrik',
                'supplier_category' => 'controller',
                'category_label' => 'Controller Motor',
                'priority_order' => 5,
                'recommendation_note' => 'Supplier controller dan komponen elektronik',
                'is_active' => true,
            ],

            // Toko Kelontong
            [
                'business_type' => 'toko_kelontong',
                'supplier_category' => 'beras',
                'category_label' => 'Beras',
                'priority_order' => 1,
                'recommendation_note' => 'Tempat beli beras bagus dan murah',
                'is_active' => true,
            ],
            [
                'business_type' => 'toko_kelontong',
                'supplier_category' => 'susu',
                'category_label' => 'Susu',
                'priority_order' => 2,
                'recommendation_note' => 'Tempat beli susu yang bagus dan fresh',
                'is_active' => true,
            ],
            [
                'business_type' => 'toko_kelontong',
                'supplier_category' => 'kebutuhan_dapur',
                'category_label' => 'Kebutuhan Dapur',
                'priority_order' => 3,
                'recommendation_note' => 'Tempat beli kebutuhan dapur murah dan lengkap',
                'is_active' => true,
            ],
            [
                'business_type' => 'toko_kelontong',
                'supplier_category' => 'minuman',
                'category_label' => 'Minuman',
                'priority_order' => 4,
                'recommendation_note' => 'Supplier minuman kemasan',
                'is_active' => true,
            ],
            [
                'business_type' => 'toko_kelontong',
                'supplier_category' => 'sembako',
                'category_label' => 'Sembako',
                'priority_order' => 5,
                'recommendation_note' => 'Supplier sembako lengkap dan murah',
                'is_active' => true,
            ],

            // Cafe
            [
                'business_type' => 'cafe',
                'supplier_category' => 'kopi',
                'category_label' => 'Biji Kopi',
                'priority_order' => 1,
                'recommendation_note' => 'Supplier biji kopi berkualitas',
                'is_active' => true,
            ],
            [
                'business_type' => 'cafe',
                'supplier_category' => 'peralatan_kopi',
                'category_label' => 'Peralatan Kopi',
                'priority_order' => 2,
                'recommendation_note' => 'Supplier peralatan kopi dan mesin espresso',
                'is_active' => true,
            ],
            [
                'business_type' => 'cafe',
                'supplier_category' => 'snack',
                'category_label' => 'Snack & Kue',
                'priority_order' => 3,
                'recommendation_note' => 'Supplier snack dan kue untuk cafe',
                'is_active' => true,
            ],

            // Resto
            [
                'business_type' => 'resto',
                'supplier_category' => 'bahan_makanan',
                'category_label' => 'Bahan Makanan',
                'priority_order' => 1,
                'recommendation_note' => 'Supplier bahan makanan fresh dan berkualitas',
                'is_active' => true,
            ],
            [
                'business_type' => 'resto',
                'supplier_category' => 'peralatan_dapur',
                'category_label' => 'Peralatan Dapur',
                'priority_order' => 2,
                'recommendation_note' => 'Supplier peralatan dapur restoran',
                'is_active' => true,
            ],
            [
                'business_type' => 'resto',
                'supplier_category' => 'furniture',
                'category_label' => 'Furniture Restoran',
                'priority_order' => 3,
                'recommendation_note' => 'Supplier meja, kursi, dan furniture restoran',
                'is_active' => true,
            ],

            // Fashion
            [
                'business_type' => 'fashion',
                'supplier_category' => 'kain',
                'category_label' => 'Kain & Textil',
                'priority_order' => 1,
                'recommendation_note' => 'Supplier kain dan bahan tekstil',
                'is_active' => true,
            ],
            [
                'business_type' => 'fashion',
                'supplier_category' => 'aksesoris',
                'category_label' => 'Aksesoris Fashion',
                'priority_order' => 2,
                'recommendation_note' => 'Supplier aksesoris fashion',
                'is_active' => true,
            ],
            [
                'business_type' => 'fashion',
                'supplier_category' => 'produksi',
                'category_label' => 'Jasa Produksi',
                'priority_order' => 3,
                'recommendation_note' => 'Jasa produksi pakaian dan konveksi',
                'is_active' => true,
            ],
        ];

        foreach ($mappings as $mapping) {
            BusinessSupplierMapping::create($mapping);
        }
    }
}
