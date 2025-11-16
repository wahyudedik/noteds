<?php

namespace Database\Seeders;

use App\Models\NoteTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('role', ['seller', 'user_workspaces'])->take(5)->get();

        if ($users->isEmpty()) {
            return;
        }

        $templates = [
            [
                'name' => 'Meeting Notes Template',
                'description' => 'Template for structured meeting notes',
                'content_template' => '<h2>Meeting: [Title]</h2><p><strong>Date:</strong> [Date]</p><p><strong>Attendees:</strong> [List]</p><h3>Agenda</h3><ul><li>[Item 1]</li><li>[Item 2]</li></ul><h3>Discussion</h3><p>[Notes]</p><h3>Action Items</h3><ul><li>[Action 1] - [Owner]</li><li>[Action 2] - [Owner]</li></ul>',
                'category' => 'Business',
                'is_public' => true,
            ],
            [
                'name' => 'Project Plan Template',
                'description' => 'Template for project planning',
                'content_template' => '<h2>Project: [Name]</h2><p><strong>Objective:</strong> [Objective]</p><h3>Timeline</h3><ul><li>[Phase 1] - [Date]</li><li>[Phase 2] - [Date]</li></ul><h3>Resources</h3><p>[Resources needed]</p><h3>Milestones</h3><ul><li>[Milestone 1]</li><li>[Milestone 2]</li></ul>',
                'category' => 'Business',
                'is_public' => true,
            ],
            [
                'name' => 'Study Notes Template',
                'description' => 'Template for academic study notes',
                'content_template' => '<h2>Subject: [Subject]</h2><h3>Chapter: [Chapter Name]</h3><h4>Key Concepts</h4><ul><li>[Concept 1]</li><li>[Concept 2]</li></ul><h4>Important Points</h4><p>[Points]</p><h4>Summary</h4><p>[Summary]</p>',
                'category' => 'Education',
                'is_public' => true,
            ],
            [
                'name' => 'Daily Journal Template',
                'description' => 'Template for daily journaling',
                'content_template' => '<h2>Date: [Date]</h2><h3>What I learned today</h3><p>[Learning]</p><h3>Challenges</h3><p>[Challenges]</p><h3>Gratitude</h3><ul><li>[Item 1]</li><li>[Item 2]</li></ul><h3>Tomorrow\'s Goals</h3><ul><li>[Goal 1]</li><li>[Goal 2]</li></ul>',
                'category' => 'Personal Development',
                'is_public' => true,
            ],
            [
                'name' => 'Code Snippet Template',
                'description' => 'Template for documenting code snippets',
                'content_template' => '<h2>[Function/Feature Name]</h2><p><strong>Language:</strong> [Language]</p><h3>Description</h3><p>[Description]</p><h3>Code</h3><pre><code>[Code here]</code></pre><h3>Usage</h3><p>[How to use]</p>',
                'category' => 'Technology',
                'is_public' => true,
            ],
        ];

        foreach ($templates as $index => $template) {
            NoteTemplate::create([
                'user_id' => $users[$index % $users->count()]->id,
                'name' => $template['name'],
                'description' => $template['description'],
                'content_template' => $template['content_template'],
                'category' => $template['category'],
                'is_public' => $template['is_public'],
                'usage_count' => rand(0, 50),
            ]);
        }

        // Create some private templates
        foreach ($users as $user) {
            NoteTemplate::create([
                'user_id' => $user->id,
                'name' => 'Personal Template ' . $user->name,
                'description' => 'Personal template for ' . $user->name,
                'content_template' => '<h2>Personal Note</h2><p>[Your content here]</p>',
                'category' => 'Personal',
                'is_public' => false,
                'usage_count' => rand(0, 20),
            ]);
        }
    }
}

