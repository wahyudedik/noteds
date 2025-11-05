<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get workspaces
        $workspaces = Workspace::all();

        if ($workspaces->isEmpty()) {
            return;
        }

        $folderNames = [
            'Projects',
            'Documents',
            'Templates',
            'Archive',
            'Drafts',
            'Research',
            'Ideas',
            'Meeting Notes',
            'Client Files',
            'Resources',
            'Backup',
            'Personal',
            'Work',
            'Education',
            'Inspiration',
        ];

        $colors = [
            '#3b82f6', // Blue
            '#10b981', // Green
            '#f59e0b', // Amber
            '#ef4444', // Red
            '#8b5cf6', // Purple
            '#ec4899', // Pink
            '#06b6d4', // Cyan
            null, // No color
        ];

        foreach ($workspaces as $workspace) {
            $folderCount = rand(3, 8);
            $rootFolders = [];

            // Create root folders
            for ($i = 0; $i < $folderCount; $i++) {
                $name = $folderNames[array_rand($folderNames)] . ($i > 0 ? ' ' . ($i + 1) : '');
                $color = $colors[array_rand($colors)];

                $folder = Folder::create([
                    'user_id' => $workspace->owner_id,
                    'workspace_id' => $workspace->id,
                    'name' => $name,
                    'parent_id' => null,
                    'order' => $i,
                    'color' => $color,
                    'description' => "Folder for organizing {$name} related content.",
                ]);

                $rootFolders[] = $folder;
            }

            // Create some nested folders (subfolders)
            foreach ($rootFolders as $rootFolder) {
                if (rand(1, 3) === 1) { // 33% chance of having subfolders
                    $subfolderCount = rand(1, 3);
                    for ($j = 0; $j < $subfolderCount; $j++) {
                        $subName = $folderNames[array_rand($folderNames)] . ' Subfolder';
                        $subColor = $colors[array_rand($colors)];

                        Folder::create([
                            'user_id' => $workspace->owner_id,
                            'workspace_id' => $workspace->id,
                            'name' => $subName . ' ' . ($j + 1),
                            'parent_id' => $rootFolder->id,
                            'order' => $j,
                            'color' => $subColor,
                            'description' => "Subfolder in {$rootFolder->name}.",
                        ]);
                    }
                }
            }
        }

        $this->command->info('Created folders for workspaces.');
    }
}

