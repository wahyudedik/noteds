<?php

namespace App\Services;

use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class AiInsightService
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Generate weekly summary for user's notes.
     */
    public function generateWeeklySummary(User $user): ?array
    {
        try {
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();

            $notes = $user->notes()
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->orWhereBetween('updated_at', [$weekStart, $weekEnd])
                ->latest()
                ->limit(50)
                ->get(['id', 'title', 'content', 'created_at', 'updated_at']);

            if ($notes->isEmpty()) {
                return null;
            }

            // Prepare notes content for summary
            $notesContent = $notes->map(function ($note) {
                $content = strip_tags($note->content);
                return "Title: {$note->title}\nContent: " . substr($content, 0, 500);
            })->implode("\n\n");

            $prompt = "Based on the following notes from this week, provide a concise weekly summary (max 300 words) covering:\n1. Main topics/themes\n2. Key insights or takeaways\n3. Notable activities or updates\n\nNotes:\n{$notesContent}\n\nWeekly Summary:";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.7,
                'num_predict' => 800,
            ]);

            if ($response && isset($response['response'])) {
                $rawSummary = $response['response'];
                $parsedSummary = $this->parseWeeklySummary($rawSummary);
                
                return [
                    'summary' => $rawSummary, // Keep raw for display
                    'parsed' => $parsedSummary, // Structured data
                    'notes_count' => $notes->count(),
                    'period' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d, Y'),
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Weekly summary generation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return null;
        }
    }

    /**
     * Detect topics/themes from user's notes.
     */
    public function detectTopics(User $user, int $limit = 50): array
    {
        try {
            $notes = $user->notes()
                ->latest()
                ->limit($limit)
                ->get(['id', 'title', 'content']);

            if ($notes->isEmpty()) {
                return [];
            }

            $notesContent = $notes->map(fn($note) => "{$note->title}: " . substr(strip_tags($note->content), 0, 300))->implode("\n");

            $prompt = "Analyze the following notes and identify the main topics/themes (list 5-10 topics, comma-separated):\n\n{$notesContent}\n\nTopics:";

            $response = $this->aiService->callOllama($prompt, [
                'temperature' => 0.6,
                'num_predict' => 300,
            ]);

            if ($response && isset($response['response'])) {
                $topics = $this->extractTopics($response['response']);
                return array_slice($topics, 0, 10);
            }

            return [];
        } catch (Exception $e) {
            Log::error('Topic detection failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [];
        }
    }

    /**
     * Extract topics from AI response.
     */
    protected function extractTopics(string $response): array
    {
        $response = trim($response);
        
        // Remove common prefixes
        $response = preg_replace('/^(?:topics?:|themes?:|subjects?:)\s*/i', '', $response);
        
        // Split by comma or newline
        $topics = preg_split('/[,;\n]/', $response);
        
        return array_filter(
            array_map('trim', $topics),
            fn($topic) => !empty($topic) && strlen($topic) > 2 && strlen($topic) < 100
        );
    }

    /**
     * Clean text response.
     */
    protected function cleanText(string $text): string
    {
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text); // Normalize whitespace
        return $text;
    }

    /**
     * Parse weekly summary into structured sections.
     */
    protected function parseWeeklySummary(string $summary): array
    {
        $result = [
            'topics' => [],
            'insights' => [],
            'activities' => [],
            'intro' => '',
        ];

        // Split by common section markers
        $sections = preg_split('/\*\*(?:Main Topics|Key Insights|Notable Activities)/i', $summary);
        
        if (count($sections) > 0) {
            $result['intro'] = trim($sections[0]);
        }

        // Extract Main Topics
        if (preg_match('/\*\*Main Topics\/Themes:\*\*(.*?)(?=\*\*|\n\n|$)/is', $summary, $matches)) {
            $topicsText = trim($matches[1]);
            $result['topics'] = $this->extractListItems($topicsText);
        }

        // Extract Key Insights
        if (preg_match('/\*\*Key Insights\/Takeaways:\*\*(.*?)(?=\*\*|\n\n|$)/is', $summary, $matches)) {
            $insightsText = trim($matches[1]);
            $result['insights'] = $this->extractListItems($insightsText);
        }

        // Extract Notable Activities
        if (preg_match('/\*\*Notable Activities\/Updates:\*\*(.*?)(?=\*\*|\n\n|$)/is', $summary, $matches)) {
            $activitiesText = trim($matches[1]);
            $result['activities'] = $this->extractListItems($activitiesText);
        }

        return $result;
    }

    /**
     * Extract list items from text (supports numbered, bulleted, or comma-separated).
     */
    protected function extractListItems(string $text): array
    {
        $items = [];
        
        // Remove markdown bold markers
        $text = preg_replace('/\*\*/', '', $text);
        $text = trim($text);
        
        // Try to split by newlines with numbers or bullets
        $lines = preg_split('/\n(?:\d+\.|[-•*])\s*/', $text);
        
        if (count($lines) > 1) {
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && strlen($line) > 3) {
                    // Clean up line endings
                    $line = rtrim($line, '.,;');
                    $items[] = $line;
                }
            }
        } else {
            // Try comma-separated
            $parts = preg_split('/[,;]\s*/', $text);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part) && strlen($part) > 3) {
                    $part = rtrim($part, '.,;');
                    $items[] = $part;
                }
            }
        }

        // Filter out very short items and limit to reasonable count
        $items = array_filter($items, fn($item) => strlen($item) > 5);
        return array_slice(array_values($items), 0, 15);
    }

    /**
     * Get note statistics insights.
     */
    public function getNoteStatistics(User $user): array
    {
        $totalNotes = $user->notes()->count();
        $thisWeek = $user->notes()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $thisMonth = $user->notes()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count();
        
        $mostActiveDay = $user->notes()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderByDesc('count')
            ->first();

        return [
            'total' => $totalNotes,
            'this_week' => $thisWeek,
            'this_month' => $thisMonth,
            'most_active_day' => $mostActiveDay ? [
                'date' => $mostActiveDay->date,
                'count' => $mostActiveDay->count,
            ] : null,
        ];
    }

    /**
     * Check if AI service is available.
     */
    public function isAiServiceAvailable(): bool
    {
        return $this->aiService->isAvailable();
    }
}

