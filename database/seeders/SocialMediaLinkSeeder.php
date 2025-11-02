<?php

namespace Database\Seeders;

use App\Models\SocialMediaLink;
use Illuminate\Database\Seeder;

class SocialMediaLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $socialMediaLinks = [
            [
                'platform' => 'facebook',
                'name' => 'Facebook',
                'url' => 'https://facebook.com/yourpage',
                'icon' => null, // Will use default
                'color' => 'currentColor',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'platform' => 'twitter',
                'name' => 'Twitter/X',
                'url' => 'https://twitter.com/yourhandle',
                'icon' => null,
                'color' => 'currentColor',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'platform' => 'linkedin',
                'name' => 'LinkedIn',
                'url' => 'https://linkedin.com/company/yourcompany',
                'icon' => null,
                'color' => 'currentColor',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($socialMediaLinks as $link) {
            SocialMediaLink::firstOrCreate(
                ['platform' => $link['platform']],
                $link
            );
        }
    }
}
