<?php

namespace Database\Seeders;

use App\Models\TaxRule;
use Illuminate\Database\Seeder;

class TaxRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            [
                'country_code' => 'ID',
                'country_name' => 'Indonesia',
                'tax_percent' => 11,
                'is_inclusive' => true,
                'is_active' => true,
            ],
            [
                'country_code' => 'US',
                'country_name' => 'United States',
                'tax_percent' => 0,
                'is_inclusive' => false,
                'is_active' => true,
            ],
        ];

        foreach ($defaults as $rule) {
            TaxRule::updateOrCreate(
                [
                    'country_code' => $rule['country_code'],
                    'note_category' => $rule['note_category'] ?? null,
                ],
                [
                    'country_name' => $rule['country_name'],
                    'tax_percent' => $rule['tax_percent'],
                    'is_inclusive' => $rule['is_inclusive'],
                    'is_active' => $rule['is_active'],
                ]
            );
        }
    }
}

