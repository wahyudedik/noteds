<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\Tag;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Folder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sellers to create notes for them
        $sellers = User::role('seller')->get();
        $tags = Tag::all();
        
        if ($sellers->isEmpty()) {
            return;
        }

        // Create sample content for notes
        $noteTemplates = [
            [
                'title' => 'Complete Laravel 12 Guide',
                'content' => 'A comprehensive guide to Laravel 12 covering all the new features, best practices, and real-world examples. Learn about the latest improvements in routing, database queries, and authentication.',
                'price' => 99000,
            ],
            [
                'title' => 'Advanced PHP Design Patterns',
                'content' => 'Deep dive into design patterns in PHP. Learn Singleton, Factory, Observer, and Strategy patterns with practical examples and use cases.',
                'price' => 125000,
            ],
            [
                'title' => 'Mastering MySQL Performance',
                'content' => 'Optimize your MySQL databases for better performance. Learn about indexing strategies, query optimization, and database architecture.',
                'price' => 150000,
            ],
            [
                'title' => 'Building RESTful APIs',
                'content' => 'Create robust RESTful APIs using Laravel. Learn about API design principles, authentication, rate limiting, and documentation.',
                'price' => 110000,
            ],
            [
                'title' => 'JavaScript ES6+ Fundamentals',
                'content' => 'Modern JavaScript development with ES6+. Arrow functions, destructuring, async/await, and more advanced features.',
                'price' => 85000,
            ],
            [
                'title' => 'Vue.js 3 Component Architecture',
                'content' => 'Build scalable applications with Vue.js 3. Component composition, state management, and best practices.',
                'price' => 120000,
            ],
            [
                'title' => 'DevOps CI/CD Pipeline',
                'content' => 'Set up continuous integration and deployment pipelines. Learn Docker, GitHub Actions, and deployment strategies.',
                'price' => 180000,
            ],
            [
                'title' => 'Database Normalization Guide',
                'content' => 'Understand database normalization from first to fifth normal form. Practical examples and common pitfalls.',
                'price' => 75000,
            ],
            [
                'title' => 'Laravel Testing Best Practices',
                'content' => 'Write effective tests for your Laravel applications. Unit tests, feature tests, and test-driven development.',
                'price' => 95000,
            ],
            [
                'title' => 'Security Best Practices',
                'content' => 'Secure your web applications from common vulnerabilities. OWASP Top 10, authentication, and encryption.',
                'price' => 140000,
            ],
        ];

        // Assign notes to sellers
        $tagList = [
            'Laravel', 'PHP', 'JavaScript', 'Database', 'Vue.js',
            'API', 'Testing', 'DevOps', 'Security', 'Tutorial',
            'Advanced', 'Beginner', 'Best Practices', 'Design Patterns',
            'Web Development', 'Backend', 'Frontend'
        ];

        // Ensure tags exist
        foreach ($tagList as $tagName) {
            Tag::firstOrCreate(
                ['name' => $tagName],
                ['slug' => Str::slug($tagName)]
            );
        }

        $allTags = Tag::all();

        // Get workspaces and folders for premium users
        $workspaces = Workspace::all();
        $folders = Folder::all();

        // Create notes for sellers
        foreach ($sellers as $index => $seller) {
            $notesPerSeller = rand(3, 6);
            
            // Check if seller has premium (has workspaces)
            $sellerWorkspaces = $workspaces->where('owner_id', $seller->id);
            $hasPremium = $sellerWorkspaces->isNotEmpty();
            
            for ($i = 0; $i < $notesPerSeller; $i++) {
                $templateIndex = ($index * $notesPerSeller + $i) % count($noteTemplates);
                $template = $noteTemplates[$templateIndex];

                // 30% chance to assign to workspace/folder if premium user
                $workspaceId = null;
                $folderId = null;
                
                if ($hasPremium && rand(1, 10) <= 3) {
                    $selectedWorkspace = $sellerWorkspaces->random();
                    $workspaceId = $selectedWorkspace->id;
                    
                    // 50% chance to assign to folder
                    $workspaceFolders = $folders->where('workspace_id', $selectedWorkspace->id);
                    if ($workspaceFolders->isNotEmpty() && rand(1, 2) === 1) {
                        $folderId = $workspaceFolders->random()->id;
                    }
                }

                $content = $template['content'];
                $normalized = Str::of(strip_tags($content))
                    ->lower()
                    ->replaceMatches('/\s+/u', ' ')
                    ->trim();

                $note = Note::create([
                    'user_id' => $seller->id,
                    'original_creator_id' => $seller->id, // Set original creator
                    'workspace_id' => $workspaceId,
                    'folder_id' => $folderId,
                    'title' => $template['title'] . ($i > 0 ? ' ' . ($i + 1) : ''),
                    'content' => $content,
                    'content_hash' => hash('sha256', (string) $normalized),
                    'price' => $template['price'] + rand(-20000, 20000),
                    'is_public' => rand(0, 100) > 20, // 80% public
                    'status' => ['active', 'active', 'active', 'active', 'inactive'][rand(0, 4)],
                    'is_sold' => false, // New notes are not sold yet
                ]);

                // Attach random tags (2-4 tags per note)
                $randomTags = $allTags->random(rand(2, 4));
                $note->tags()->attach($randomTags);
            }
        }
    }
}
