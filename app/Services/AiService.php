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

    /**
     * Detect context links between notes - identify related notes based on content similarity.
     * This helps users understand relationships between their notes.
     *
     * @param array $notes Array of notes with 'id', 'title', 'content'
     * @param string|null $focusNoteId Optional: focus note ID to find links for
     * @return array Array of linked note pairs with relationship description
     */
    public function detectContextLinks(array $notes, ?string $focusNoteId = null): array
    {
        try {
            if (count($notes) < 2) {
                return [];
            }

            // Prepare note summaries for AI analysis
            $noteSummaries = [];
            foreach ($notes as $note) {
                $summary = $this->truncateContent(
                    ($note['title'] ?? '') . ' ' . ($note['content'] ?? ''),
                    800
                );
                $noteSummaries[] = [
                    'id' => $note['id'],
                    'summary' => $summary,
                ];
            }

            // Build context for AI
            $context = implode("\n\n", array_map(function($n, $idx) {
                return "Note " . ($idx + 1) . " (ID: {$n['id']}):\n{$n['summary']}";
            }, $noteSummaries, array_keys($noteSummaries)));

            $prompt = "Analyze these notes and identify relationships between them. " .
                     "Look for:\n" .
                     "1. Similar topics or themes\n" .
                     "2. Related people, projects, or events\n" .
                     "3. Sequential or chronological connections\n" .
                     "4. Complementary information\n\n" .
                     "Notes:\n{$context}\n\n" .
                     "List related note pairs in format: 'Note ID1 <-> Note ID2: relationship description'\n" .
                     "Example: 'abc-123 <-> def-456: Both discuss project timeline'\n" .
                     "Only list pairs that are clearly related:\n";

            $response = $this->callOllama($prompt, [
                'temperature' => 0.4,
                'num_predict' => 800,
            ]);

            if ($response && isset($response['response'])) {
                $links = $this->parseContextLinks($response['response'], $notes);
                
                // If focus note ID provided, filter to only show links involving that note
                if ($focusNoteId) {
                    $links = array_filter($links, function($link) use ($focusNoteId) {
                        return $link['note_id_1'] === $focusNoteId || $link['note_id_2'] === $focusNoteId;
                    });
                }

                return array_values($links);
            }

            return [];
        } catch (Exception $e) {
            Log::error('Context linking failed', [
                'error' => $e->getMessage(),
                'focus_note_id' => $focusNoteId,
            ]);

            return [];
        }
    }

    /**
     * Parse context links from AI response.
     *
     * @param string $response AI response text
     * @param array $notes Original notes array
     * @return array Array of parsed links
     */
    protected function parseContextLinks(string $response, array $notes): array
    {
        $links = [];
        $validIds = array_column($notes, 'id');

        // Pattern: UUID <-> UUID: description
        $pattern = '/\b([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\s*(?:<->|->|<-)\s*([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}):\s*(.+)/i';
        
        if (preg_match_all($pattern, $response, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $id1 = $match[1];
                $id2 = $match[2];
                $description = trim($match[3]);

                // Validate both IDs exist in our notes
                if (in_array($id1, $validIds) && in_array($id2, $validIds) && $id1 !== $id2) {
                    // Avoid duplicates (A->B and B->A)
                    $key = $id1 < $id2 ? "{$id1}:{$id2}" : "{$id2}:{$id1}";
                    
                    if (!isset($links[$key])) {
                        $links[$key] = [
                            'note_id_1' => $id1 < $id2 ? $id1 : $id2,
                            'note_id_2' => $id1 < $id2 ? $id2 : $id1,
                            'relationship' => $description,
                            'strength' => 'medium', // Could be enhanced with confidence scoring
                        ];
                    }
                }
            }
        }

        // Fallback: try simpler pattern without UUID format
        if (empty($links)) {
            $lines = explode("\n", $response);
            foreach ($lines as $line) {
                // Look for "Note X" patterns
                if (preg_match('/Note\s+(\d+).*?Note\s+(\d+).*?:\s*(.+)/i', $line, $match)) {
                    $idx1 = (int)$match[1] - 1;
                    $idx2 = (int)$match[2] - 1;
                    
                    if (isset($notes[$idx1]) && isset($notes[$idx2])) {
                        $id1 = $notes[$idx1]['id'];
                        $id2 = $notes[$idx2]['id'];
                        
                        if ($id1 !== $id2) {
                            $key = $id1 < $id2 ? "{$id1}:{$id2}" : "{$id2}:{$id1}";
                            if (!isset($links[$key])) {
                                $links[$key] = [
                                    'note_id_1' => $id1 < $id2 ? $id1 : $id2,
                                    'note_id_2' => $id1 < $id2 ? $id2 : $id1,
                                    'relationship' => trim($match[3]),
                                    'strength' => 'medium',
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $links;
    }

    /**
     * Generate content from a prompt (like LLM).
     * Used for creating note content based on user's prompt/question.
     *
     * @param string $prompt The user's prompt/question
     * @param int $maxLength Maximum length of generated content
     * @return string|null The generated content or null on failure
     */
    public function generateContent(string $prompt, int $maxLength = 2000): ?string
    {
        try {
            $systemPrompt = "You are a helpful writing assistant. Generate well-structured, informative content based on the user's request. " .
                          "Use proper formatting with headings, paragraphs, and lists when appropriate. " .
                          "Make the content engaging and useful.";

            $fullPrompt = "{$systemPrompt}\n\nUser request: {$prompt}\n\nGenerate comprehensive content:";

            $response = $this->callOllama($fullPrompt, [
                'temperature' => 0.8, // Higher temperature for more creative content
                'num_predict' => $maxLength,
            ]);

            if ($response && isset($response['response'])) {
                $content = trim($response['response']);
                
                // Convert to HTML if needed (basic markdown to HTML conversion)
                $content = $this->markdownToHtml($content);
                
                return $content;
            }

            return null;
        } catch (Exception $e) {
            Log::error('AI Content generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
            ]);

            return null;
        }
    }

    /**
     * Search for images using Unsplash API.
     * Falls back to basic web search if Unsplash is not configured.
     *
     * @param string $query Search query
     * @param int $limit Number of images to return
     * @return array Array of image URLs and metadata
     */
    public function searchImages(string $query, int $limit = 10): array
    {
        try {
            $unsplashKey = config('services.unsplash.access_key');
            
            if ($unsplashKey) {
                return $this->searchUnsplashImages($query, $limit, $unsplashKey);
            }

            // Fallback: Use AI to suggest image search terms or return empty
            Log::warning('Unsplash API key not configured');
            return [];
        } catch (Exception $e) {
            Log::error('Image search failed', [
                'error' => $e->getMessage(),
                'query' => $query,
            ]);

            return [];
        }
    }

    /**
     * Search images from Unsplash API.
     *
     * @param string $query Search query
     * @param int $limit Number of images
     * @param string $apiKey Unsplash API key
     * @return array Array of image data
     */
    protected function searchUnsplashImages(string $query, int $limit, string $apiKey): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => "Client-ID {$apiKey}",
                ])
                ->get('https://api.unsplash.com/search/photos', [
                    'query' => $query,
                    'per_page' => min($limit, 30), // Unsplash max is 30
                    'orientation' => 'landscape',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $images = [];

                if (isset($data['results']) && is_array($data['results'])) {
                    foreach ($data['results'] as $photo) {
                        $images[] = [
                            'id' => $photo['id'] ?? null,
                            'url' => $photo['urls']['regular'] ?? $photo['urls']['small'] ?? null,
                            'thumbnail' => $photo['urls']['thumb'] ?? $photo['urls']['small'] ?? null,
                            'full' => $photo['urls']['full'] ?? null,
                            'description' => $photo['description'] ?? $photo['alt_description'] ?? $query,
                            'author' => $photo['user']['name'] ?? 'Unknown',
                            'author_url' => $photo['user']['links']['html'] ?? null,
                            'unsplash_url' => $photo['links']['html'] ?? null,
                        ];
                    }
                }

                return $images;
            }

            return [];
        } catch (Exception $e) {
            Log::error('Unsplash API request failed', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Convert basic markdown to HTML for Quill editor.
     *
     * @param string $markdown Markdown text
     * @return string HTML content
     */
    protected function markdownToHtml(string $markdown): string
    {
        // Basic markdown to HTML conversion
        // Convert headers
        $html = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $markdown);
        $html = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $html);
        
        // Convert bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // Convert lists
        $html = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/^(\d+)\. (.*?)$/m', '<li>$2</li>', $html);
        
        // Wrap consecutive list items in ul tags
        $html = preg_replace('/(<li>.*?<\/li>\n?)+/s', '<ul>$0</ul>', $html);
        
        // Convert line breaks to paragraphs
        $html = preg_replace('/\n\n+/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';
        
        // Clean up empty paragraphs
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        
        return $html;
    }

    /**
     * Generate image from text prompt.
     * Uses Ollama with image generation model or external API.
     *
     * @param string $prompt The image description prompt
     * @param array $options Optional parameters (size, style, etc.)
     * @return array|null Array with 'url' or 'base64' image data, or null on failure
     */
    public function generateImage(string $prompt, array $options = []): ?array
    {
        try {
            // CATATAN: Ollama saat ini BELUM punya model image generation built-in
            // Model seperti "flux" perlu diinstall secara manual dan mungkin tidak tersedia di registry
            // Jadi kita prioritaskan Stability AI, dan Ollama sebagai fallback eksperimental
            
            // PRIORITAS 1: Gunakan Stability AI (lebih reliable)
            $stabilityKey = config('services.stability.api_key');
            if ($stabilityKey) {
                return $this->generateImageWithStability($prompt, $options, $stabilityKey);
            }
            
            // PRIORITAS 2: Coba Ollama dengan model image generation (jika tersedia)
            // Hanya jika user sudah setup model image generation secara manual
            $imageModel = config('services.ollama.image_model', 'flux');
            if ($this->isOllamaImageModelAvailable($imageModel)) {
                return $this->generateImageWithOllama($prompt, $options, $imageModel);
            }
            
            // Jika tidak ada yang tersedia
            Log::warning('Image generation API not configured. Setup Stability AI API key or install image generation model in Ollama manually.');
            return null;
        } catch (Exception $e) {
            Log::error('Image generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
            ]);

            return null;
        }
    }

    /**
     * Check if Ollama image generation model is available.
     *
     * @param string $model Model name (e.g., 'flux', 'stable-diffusion-xl')
     * @return bool
     */
    protected function isOllamaImageModelAvailable(string $model): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");
            
            if ($response->successful()) {
                $data = $response->json();
                $models = $data['models'] ?? [];
                
                // Check if image model exists
                foreach ($models as $modelData) {
                    if (isset($modelData['name']) && str_contains($modelData['name'], $model)) {
                        return true;
                    }
                }
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate image using Ollama with image generation model.
     * 
     * CATATAN PENTING: 
     * - Ollama saat ini BELUM punya model image generation built-in di registry
     * - Model seperti "flux" perlu diinstall secara manual (bukan via ollama pull)
     * - Lebih baik gunakan Stability AI untuk image generation yang lebih reliable
     * - Fitur ini hanya sebagai fallback eksperimental
     *
     * @param string $prompt Image description
     * @param array $options Generation options
     * @param string $model Model name
     * @return array|null Image data
     */
    protected function generateImageWithOllama(string $prompt, array $options, string $model): ?array
    {
        try {
            // CATATAN: Implementasi ini adalah placeholder
            // Ollama belum punya model image generation yang mudah diakses
            // Jika user punya model image generation custom, bisa diintegrasikan di sini
            
            Log::info('Ollama image generation attempted', [
                'model' => $model,
                'note' => 'Ollama image generation requires custom model setup. Consider using Stability AI instead.',
            ]);
            
            // Return null karena Ollama belum support image generation dengan mudah
            return null;
        } catch (Exception $e) {
            Log::error('Ollama image generation failed', [
                'error' => $e->getMessage(),
                'model' => $model,
            ]);

            return null;
        }
    }

    /**
     * Generate image using Stability AI API.
     *
     * @param string $prompt Image description
     * @param array $options Generation options
     * @param string $apiKey Stability AI API key
     * @return array|null Image data
     */
    protected function generateImageWithStability(string $prompt, array $options, string $apiKey): ?array
    {
        try {
            $size = $options['size'] ?? '1024x1024';
            $style = $options['style'] ?? 'vivid';
            
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Accept' => 'application/json',
                ])
                ->post('https://api.stability.ai/v2beta/stable-image/generate/core', [
                    'text_prompts' => [
                        ['text' => $prompt]
                    ],
                    'cfg_scale' => $options['cfg_scale'] ?? 7,
                    'height' => (int)explode('x', $size)[1],
                    'width' => (int)explode('x', $size)[0],
                    'samples' => 1,
                    'steps' => $options['steps'] ?? 30,
                    'style_preset' => $style,
                ]);

            if ($response->successful()) {
                $imageData = $response->body();
                
                // Save to public storage
                $tempDir = storage_path('app/public/temp');
                \Illuminate\Support\Facades\File::ensureDirectoryExists($tempDir);
                
                $filename = uniqid('img_') . '.png';
                $tempPath = $tempDir . '/' . $filename;
                file_put_contents($tempPath, $imageData);
                
                return [
                    'url' => asset('storage/temp/' . $filename),
                    'path' => $tempPath,
                    'base64' => base64_encode($imageData),
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Stability AI image generation failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate video from text prompt.
     * 
     * CATATAN PENTING: Ollama saat ini BELUM support video generation.
     * Video generation memerlukan model khusus yang belum tersedia di Ollama.
     * Jadi kita tetap perlu API eksternal (RunwayML, dll) untuk video actual.
     * 
     * Namun, Ollama bisa digunakan untuk generate video script/storyboard
     * yang membantu user membuat konsep video.
     *
     * @param string $prompt The video description prompt
     * @param array $options Optional parameters (duration, style, etc.)
     * @return array|null Array with video URL or job ID, or null on failure
     */
    public function generateVideo(string $prompt, array $options = []): ?array
    {
        try {
            // PRIORITAS 1: Gunakan RunwayML atau service lain untuk video actual
            $runwayKey = config('services.runway.api_key');
            
            if ($runwayKey) {
                return $this->generateVideoWithRunway($prompt, $options, $runwayKey);
            }
            
            // PRIORITAS 2: Fallback - Generate video script/storyboard dengan Ollama
            // Ini membantu user membuat konsep video, bukan video itu sendiri
            if ($this->isAvailable()) {
                return $this->generateVideoScriptWithOllama($prompt, $options);
            }
            
            Log::warning('Video generation API not configured. Video generation requires external API (RunwayML, etc.) as Ollama does not support video generation yet.');
            return null;
        } catch (Exception $e) {
            Log::error('Video generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
            ]);

            return null;
        }
    }

    /**
     * Generate video script/storyboard using Ollama (bukan video actual).
     * Ini membantu user membuat konsep video, bukan video itu sendiri.
     *
     * @param string $prompt Video description
     * @param array $options Options
     * @return array|null Script data
     */
    protected function generateVideoScriptWithOllama(string $prompt, array $options): ?array
    {
        try {
            $duration = isset($options['duration']) ? $options['duration'] : 5;
            $scriptPrompt = "Buatkan script video untuk: {$prompt}\n\n" .
                          "Include:\n" .
                          "1. Scene-by-scene breakdown\n" .
                          "2. Visual descriptions\n" .
                          "3. Timing for each scene\n" .
                          "4. Suggested transitions\n\n" .
                          "Duration: {$duration} seconds";

            $response = $this->callOllama($scriptPrompt, [
                'temperature' => 0.8,
                'num_predict' => 2000,
            ]);

            if ($response && isset($response['response'])) {
                return [
                    'type' => 'script', // Bukan video actual, tapi script
                    'script' => $response['response'],
                    'message' => 'Video script generated. Untuk generate video actual, gunakan RunwayML API.',
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Video script generation failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate video using RunwayML API (placeholder).
     *
     * @param string $prompt Video description
     * @param array $options Generation options
     * @param string $apiKey RunwayML API key
     * @return array|null Video job data
     */
    protected function generateVideoWithRunway(string $prompt, array $options, string $apiKey): ?array
    {
        try {
            // This is a placeholder - actual RunwayML API integration
            // RunwayML typically returns a job ID that you poll for completion
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.runwayml.com/v1/image-to-video', [
                    'image_url' => $options['image_url'] ?? null,
                    'prompt' => $prompt,
                    'duration' => $options['duration'] ?? 5,
                    'ratio' => $options['ratio'] ?? '16:9',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'job_id' => $data['id'] ?? null,
                    'status' => 'processing',
                    'estimated_time' => $data['estimated_time'] ?? 60,
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('RunwayML video generation failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Edit video (trim, add effects, etc.).
     * This is a placeholder for video editing functionality.
     *
     * @param string $videoUrl Video URL or path
     * @param array $edits Edit instructions
     * @return array|null Edited video data
     */
    public function editVideo(string $videoUrl, array $edits): ?array
    {
        try {
            // Video editing typically requires FFmpeg or cloud service
            // This is a placeholder implementation
            Log::info('Video editing requested', [
                'video_url' => $videoUrl,
                'edits' => $edits,
            ]);

            // For now, return a placeholder response
            // In production, integrate with video editing service or FFmpeg
            return [
                'status' => 'not_implemented',
                'message' => 'Video editing will be available soon',
            ];
        } catch (Exception $e) {
            Log::error('Video editing failed', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate content ideas based on topic or prompt.
     * Uses AI to generate creative ideas for notes, articles, etc.
     *
     * @param string $topic The topic or theme
     * @param int $count Number of ideas to generate
     * @return array Array of generated ideas
     */
    public function generateIdeas(string $topic, int $count = 5): array
    {
        try {
            $prompt = "Generate {$count} creative and engaging content ideas about: {$topic}\n\n" .
                     "For each idea, provide:\n" .
                     "1. A catchy title\n" .
                     "2. A brief description (1-2 sentences)\n" .
                     "3. Key points or subtopics to cover\n\n" .
                     "Format as a numbered list. Make the ideas diverse and interesting.";

            $response = $this->callOllama($prompt, [
                'temperature' => 0.9, // Higher temperature for more creative ideas
                'num_predict' => 1500,
            ]);

            if ($response && isset($response['response'])) {
                $ideas = $this->parseIdeas($response['response'], $count);
                return $ideas;
            }

            return [];
        } catch (Exception $e) {
            Log::error('Idea generation failed', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);

            return [];
        }
    }

    /**
     * Parse AI response into structured ideas array.
     *
     * @param string $response AI response text
     * @param int $expectedCount Expected number of ideas
     * @return array Array of parsed ideas
     */
    protected function parseIdeas(string $response, int $expectedCount): array
    {
        $ideas = [];
        $lines = explode("\n", $response);
        
        $currentIdea = null;
        $currentSection = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if this is a new idea (starts with number)
            if (preg_match('/^(\d+)\.?\s*(.+)$/i', $line, $matches)) {
                if ($currentIdea) {
                    $ideas[] = $currentIdea;
                }
                
                $currentIdea = [
                    'title' => $matches[2],
                    'description' => '',
                    'key_points' => [],
                ];
                $currentSection = 'title';
            }
            // Check for description indicators
            elseif (preg_match('/^(description|desc|about):\s*(.+)$/i', $line, $matches)) {
                if ($currentIdea) {
                    $currentIdea['description'] = $matches[2];
                    $currentSection = 'description';
                }
            }
            // Check for key points
            elseif (preg_match('/^(key points?|points?|topics?):\s*(.+)$/i', $line, $matches)) {
                if ($currentIdea) {
                    $points = explode(',', $matches[2]);
                    $currentIdea['key_points'] = array_map('trim', $points);
                    $currentSection = 'key_points';
                }
            }
            // Continue description or key points
            elseif ($currentIdea) {
                if ($currentSection === 'description' && empty($currentIdea['description'])) {
                    $currentIdea['description'] = $line;
                } elseif ($currentSection === 'key_points' || preg_match('/^[-•]\s*(.+)$/', $line, $pointMatch)) {
                    $currentIdea['key_points'][] = preg_replace('/^[-•]\s*/', '', $line);
                }
            }
        }
        
        // Add last idea
        if ($currentIdea) {
            $ideas[] = $currentIdea;
        }
        
        // Ensure we have at least expected count
        while (count($ideas) < $expectedCount && count($ideas) > 0) {
            // Duplicate and modify last idea if needed
            $lastIdea = end($ideas);
            $ideas[] = array_merge($lastIdea, ['title' => $lastIdea['title'] . ' (Variation)']);
        }
        
        return array_slice($ideas, 0, $expectedCount);
    }
}
