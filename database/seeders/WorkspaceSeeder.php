<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users with premium subscription
        $premiumUsers = User::whereHas('subscription', function ($query) {
            $query->where('status', 'active')
                ->where('plan', 'premium')
                ->where('expired_at', '>', now());
        })->get();

        // Also get admin users (they have premium access)
        $adminUsers = User::role('admin')->get();

        $allUsers = $premiumUsers->merge($adminUsers)->unique('id');

        if ($allUsers->isEmpty()) {
            // Create some workspaces for regular users too (for testing)
            $allUsers = User::take(5)->get();
        }

        $workspaceTypes = ['personal', 'team', 'organization'];
        $workspaceNames = [
            'My Personal Workspace',
            'Team Collaboration',
            'Project Alpha',
            'Development Hub',
            'Marketing Team',
            'Design Studio',
            'Research Lab',
            'Content Creation',
            'Client Projects',
            'Internal Docs',
        ];

        foreach ($allUsers as $user) {
            $workspaceCount = rand(1, 3);

            for ($i = 0; $i < $workspaceCount; $i++) {
                $name = $workspaceNames[array_rand($workspaceNames)] . ($i > 0 ? ' ' . ($i + 1) : '');
                $type = $workspaceTypes[array_rand($workspaceTypes)];
                
                // 10% chance of being for sale
                $isForSale = rand(1, 10) === 1;
                $price = $isForSale ? rand(50000, 500000) : null;

                Workspace::create([
                    'owner_id' => $user->id,
                    'name' => $name,
                    'slug' => Str::slug($name . '-' . Str::random(5)),
                    'type' => $type,
                    'description' => "This is a {$type} workspace for managing notes and documents.",
                    'is_active' => true,
                    'is_for_sale' => $isForSale,
                    'price' => $price,
                    'marketplace_description' => $isForSale ? "Well-organized {$type} workspace with notes and folders." : null,
                ]);
            }
        }

        $this->command->info('Created workspaces for users.');
    }
}

