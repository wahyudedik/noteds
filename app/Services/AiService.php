<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AiService
{
    protected string $baseUrl;

    protected string $model;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'llama3.2');
    }

    /**
     * Generate a summary for a note's content.
     *
     * @param string $content The note content
     * @param int $maxLength Maximum length of summary
     * @return string|null The generated summary or null on failure
     */
    public function generateSummary(string $content, int $maxLength = 200): ?string
    {
        try {
            $content = $this->truncateContent($content, 3000); // Limit input

            $prompt = "Generate a concise summary (max {$maxLength} characters) for the following note:\n\n{$content}\n\nSummary:";

            $response = $this->callOllama($prompt, [
                'temperature' => 0.7,
                'num_predict' => 500,
            ]);

            if ($response && isset($response['response'])) {
                return $this->cleanResponse($response['response'], $maxLength);
            }

            return null;
        } catch (Exception $e) {
            Log::error('AI Summary generation failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Suggest tags for a note based on its content.
     *
     * @param string $content The note content
     * @param int $maxTags Maximum number of tags to suggest
     * @return array Array of suggested tag names
     */
    public function suggestTags(string $content, int $maxTags = 5): array
    {
        try {
            $content = $this->truncateContent($content, 3000); // Limit input

            $prompt = "Analyze the following note and suggest {$maxTags} relevant tags (single words or short phrases, separated by commas):\n\n{$content}\n\nTags:";

            $response = $this->callOllama($prompt, [
                'temperature' => 0.7,
                'num_predict' => 500,
            ]);

            if ($response && isset($response['response'])) {
                $tags = $this->extractTags($response['response']);
                return array_slice($tags, 0, $maxTags);
            }

            return [];
        } catch (Exception $e) {
            Log::error('AI Tag suggestion failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }


    /**
     * Clean and truncate the AI response.
     *
     * @param string $response The raw AI response
     * @param int $maxLength Maximum length
     * @return string Cleaned response
     */
    protected function cleanResponse(string $response, int $maxLength): string
    {
        $cleaned = trim($response);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Normalize whitespace

        if (strlen($cleaned) > $maxLength) {
            $cleaned = substr($cleaned, 0, $maxLength);
            $lastSpace = strrpos($cleaned, ' ');
            if ($lastSpace !== false) {
                $cleaned = substr($cleaned, 0, $lastSpace);
            }
            $cleaned .= '...';
        }

        return $cleaned;
    }

    /**
     * Extract tags from a comma-separated string.
     *
     * @param string $response The raw AI response
     * @return array Array of tag names
     */
    protected function extractTags(string $response): array
    {
        // Clean up the response - remove explanatory text
        $response = trim($response);
        
        // Try to find tag list by looking for common patterns
        // Pattern 1: Line starting with numbers or bullets
        if (preg_match('/(?:\d+\.\s*|[-•]\s*|•\s*)([^\n]+)/', $response, $matches)) {
            $response = $matches[1];
        }
        
        // Pattern 2: Extract content after colon if it's a list format
        if (preg_match('/:\s*(.+)/s', $response, $matches)) {
            $response = trim($matches[1]);
        }
        
        // Pattern 3: Remove common prefixes
        $response = preg_replace('/^(?:tags?:|suggestions?:|recommendations?:)\s*/i', '', $response);
        
        // Split by comma and clean each tag
        $tags = explode(',', $response);
        $tags = array_map(function($tag) {
            return trim($tag);
        }, $tags);
        
        // Filter out empty tags and keep only meaningful ones
        $tags = array_filter($tags, function($tag) {
            return !empty($tag) && strlen($tag) > 1 && strlen($tag) < 50;
        });
        
        // Remove duplicates while preserving order
        return array_values(array_unique($tags));
    }

    /**
     * Truncate content to a maximum length.
     *
     * @param string $content The content to truncate
     * @param int $maxLength Maximum length
     * @return string Truncated content
     */
    protected function truncateContent(string $content, int $maxLength): string
    {
        if (strlen($content) <= $maxLength) {
            return $content;
        }

        $truncated = substr($content, 0, $maxLength);
        $lastSpace = strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return $truncated;
    }

    /**
     * Check if Ollama is available and accessible.
     *
     * @return bool True if Ollama is available
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");

            return $response->successful();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Answer a question based on provided notes content.
     * Premium feature - Q&A using natural language understanding.
     *
     * @param string $question The user's question
     * @param array $notes Array of note content strings
     * @param array $noteIds Optional array of note IDs corresponding to notes (for references)
     * @return array|null Array with 'answer' and 'referenced_note_ids'
     */
    public function answerQuestion(string $question, array $notes, array $noteIds = []): ?array
    {
        try {
            // Combine notes content with index markers
            $combinedContent = '';
            $noteIndexMap = []; // Map note number to note ID
            foreach ($notes as $index => $noteContent) {
                $noteNum = $index + 1;
                $combinedContent .= "Note {$noteNum}:\n" . $this->truncateContent($noteContent, 2000) . "\n\n";
                
                // Map note number to actual note ID if provided
                if (!empty($noteIds) && isset($noteIds[$index])) {
                    $noteIndexMap[$noteNum] = $noteIds[$index];
                }
            }

            // Limit total context to avoid token limits
            if (strlen($combinedContent) > 8000) {
                $combinedContent = substr($combinedContent, 0, 8000) . '...';
            }

            $prompt = "Based on the following notes, answer this question in a clear and concise way. When mentioning specific notes, use 'Note X' format:\n\nQuestion: {$question}\n\nNotes:\n{$combinedContent}\n\nAnswer:";

            $response = $this->callOllama($prompt, [
                'temperature' => 0.5, // Lower temperature for more factual answers
                'num_predict' => 1000,
            ]);

            if ($response && isset($response['response'])) {
                $answer = $this->cleanResponse($response['response'], 1500);
                
                // Extract referenced note numbers from answer
                $referencedNoteIds = [];
                if (!empty($noteIndexMap)) {
                    preg_match_all('/Note\s+(\d+)/i', $answer, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $noteNumStr) {
                            $noteNum = (int)$noteNumStr;
                            if (isset($noteIndexMap[$noteNum])) {
                                $referencedNoteIds[] = $noteIndexMap[$noteNum];
                            }
                        }
                    }
                }
                
                return [
                    'answer' => $answer,
                    'referenced_note_ids' => array_unique($referencedNoteIds),
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('AI Q&A failed', [
                'error' => $e->getMessage(),
                'question' => $question,
            ]);

            return null;
        }
    }

    /**
     * Find relevant notes based on semantic understanding of query.
     * This is a basic implementation - full semantic search will require embeddings.
     *
     * @param string $query The search query
     * @param array $notes Array of notes with 'id', 'title', 'content'
     * @return array Array of relevant note IDs sorted by relevance
     */
    public function semanticSearch(string $query, array $notes): array
    {
        try {
            // For now, use basic keyword matching + AI relevance scoring
            // Full implementation will use embeddings in future phase
            
            $noteSummaries = [];
            foreach ($notes as $note) {
                $summary = $this->truncateContent(
                    ($note['title'] ?? '') . ' ' . ($note['content'] ?? ''),
                    500
                );
                $noteSummaries[] = [
                    'id' => $note['id'],
                    'summary' => $summary,
                ];
            }

            // Use AI to score relevance (basic implementation)
            $context = implode("\n", array_map(fn($n) => "Note {$n['id']}: {$n['summary']}", $noteSummaries));
            $prompt = "Given this search query: '{$query}'\n\nRate the relevance of these notes (1-10) and list note IDs in order of relevance:\n\n{$context}\n\nRelevant note IDs (comma-separated, most relevant first):";

            $response = $this->callOllama($prompt, [
                'temperature' => 0.3,
                'num_predict' => 500,
            ]);

            if ($response && isset($response['response'])) {
                // Extract note IDs from response (UUID format)
                $responseText = $response['response'];
                // Match UUID pattern more precisely
                preg_match_all('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', $responseText, $matches);
                
                if (!empty($matches[0])) {
                    // Filter to only include valid note IDs from our dataset
                    $validIds = array_column($notes, 'id');
                    $foundIds = array_intersect($matches[0], $validIds);
                    
                    if (!empty($foundIds)) {
                        return array_values(array_unique($foundIds));
                    }
                }
            }

            // Fallback: return all note IDs if AI parsing fails
            return array_column($notes, 'id');
        } catch (Exception $e) {
            Log::error('Semantic search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);

            // Fallback: return all note IDs
            return array_column($notes, 'id');
        }
    }

    /**
     * Enhanced callOllama with custom options.
     * Made public for use in other services like AiInsightService.
     *
     * @param string $prompt The prompt to send
     * @param array $options Optional parameters (temperature, num_predict, etc.)
     * @return array|null The API response or null on failure
     */
    public function callOllama(string $prompt, array $options = []): ?array
    {
        try {
            $defaultOptions = [
                'temperature' => 0.7,
                'num_predict' => 500,
            ];

            $mergedOptions = array_merge($defaultOptions, $options);

            $response = Http::timeout(60)->post("{$this->baseUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
                'options' => $mergedOptions,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Ollama API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Ollama API request exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
