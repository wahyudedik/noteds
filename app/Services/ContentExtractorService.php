<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class ContentExtractorService
{
    protected string $ollamaBaseUrl;
    protected string $ollamaModel;
    protected int $cacheDuration = 86400; // 24 hours
    protected AiService $aiService;

    public function __construct(AiService $aiService)
    {
        $this->ollamaBaseUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->ollamaModel = config('services.ollama.model', 'llama3.2');
        $this->aiService = $aiService; // Use AiService for CPU-optimized Ollama calls
    }

    /**
     * Extract text from PDF file (with caching)
     */
    public function extractPdfText(string $filePath): ?array
    {
        // Create cache key based on file path and modification time
        $fullPath = Storage::disk('private')->path($filePath);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $fileMtime = filemtime($fullPath);
        $cacheKey = 'pdf_extract_' . md5($filePath . '_' . $fileMtime);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($fullPath, $filePath) {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($fullPath);
                
                $text = $pdf->getText();
                $details = $pdf->getDetails();
                
                return [
                    'text' => $text,
                    'pages' => $details['Pages'] ?? null,
                    'title' => $details['Title'] ?? null,
                    'author' => $details['Author'] ?? null,
                    'subject' => $details['Subject'] ?? null,
                    'metadata' => $details,
                ];
            } catch (Exception $e) {
                Log::error('PDF extraction failed', [
                    'error' => $e->getMessage(),
                    'file_path' => $filePath,
                ]);
                return null;
            }
        });
    }

    /**
     * Extract text from image using OCR (via Ollama vision model or Tesseract) with caching
     */
    public function extractImageText(string $filePath): ?array
    {
        $fullPath = Storage::disk('private')->path($filePath);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        // Create cache key based on file path and modification time
        $fileMtime = filemtime($fullPath);
        $cacheKey = 'ocr_extract_' . md5($filePath . '_' . $fileMtime);

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($fullPath, $filePath) {
            try {
                // Try Ollama vision model first (if available)
                $visionModel = config('services.ollama.vision_model', 'llava');
                if ($this->isOllamaVisionModelAvailable($visionModel)) {
                    return $this->extractImageTextWithOllama($fullPath, $visionModel);
                }

                // Fallback: Try Tesseract OCR if available
                if ($this->isTesseractAvailable()) {
                    return $this->extractImageTextWithTesseract($fullPath);
                }

                Log::warning('No OCR service available. Install Tesseract OCR or setup Ollama vision model.');
                return null;
            } catch (Exception $e) {
                Log::error('Image OCR failed', [
                    'error' => $e->getMessage(),
                    'file_path' => $filePath,
                ]);
                return null;
            }
        });
    }

    /**
     * Extract tables from document (PDF or image)
     */
    public function extractTables(string $filePath, string $fileType = 'pdf'): ?array
    {
        try {
            if ($fileType === 'pdf') {
                $content = $this->extractPdfText($filePath);
                if (!$content) {
                    return null;
                }
                
                // Use AI to identify and extract tables from text
                return $this->extractTablesWithAI($content['text']);
            } elseif (in_array($fileType, ['image', 'jpg', 'jpeg', 'png', 'gif'])) {
                // For images, extract text first, then identify tables
                $content = $this->extractImageText($filePath);
                if (!$content) {
                    return null;
                }
                
                return $this->extractTablesWithAI($content['text']);
            }

            return null;
        } catch (Exception $e) {
            Log::error('Table extraction failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            return null;
        }
    }

    /**
     * Extract content from file (auto-detect type)
     */
    public function extractContent(string $filePath, string $mimeType): ?array
    {
        try {
            if ($mimeType === 'application/pdf') {
                return [
                    'type' => 'pdf',
                    'data' => $this->extractPdfText($filePath),
                ];
            } elseif (str_starts_with($mimeType, 'image/')) {
                return [
                    'type' => 'image',
                    'data' => $this->extractImageText($filePath),
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Content extraction failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
                'mime_type' => $mimeType,
            ]);
            return null;
        }
    }

    /**
     * Extract image text using Ollama vision model
     * Uses AiService for CPU-optimized inference
     */
    protected function extractImageTextWithOllama(string $imagePath, string $model): ?array
    {
        try {
            // Read image as base64
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = mime_content_type($imagePath);

            // Use AiService's callOllama method which includes CPU optimization
            // For vision models, we need to send images, so we use HTTP directly but with CPU-optimized options
            $response = Http::timeout(config('services.ollama.timeout', 120))
                ->post("{$this->ollamaBaseUrl}/api/generate", [
                    'model' => $model,
                    'prompt' => 'Extract all text from this image. Return only the extracted text, no explanations.',
                    'images' => ["data:{$mimeType};base64,{$base64Image}"],
                    'stream' => false,
                    // Add CPU optimization options
                    'options' => [
                        'num_thread' => config('services.ollama.num_threads') ?: $this->getOptimalThreadCount(),
                        'num_ctx' => config('services.ollama.num_ctx', 4096),
                        'num_gpu' => 0, // CPU only
                        'use_mmap' => true,
                        'batch_size' => config('services.ollama.batch_size', 512),
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['response'] ?? '';

                return [
                    'text' => trim($text),
                    'method' => 'ollama_vision',
                    'model' => $model,
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Ollama vision OCR failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
    
    /**
     * Get optimal CPU thread count (same logic as AiService)
     */
    protected function getOptimalThreadCount(): int
    {
        $configThreads = config('services.ollama.num_threads');
        if ($configThreads !== null && is_numeric($configThreads) && $configThreads > 0) {
            return (int)$configThreads;
        }
        
        // Auto-detect CPU cores
        if (PHP_OS_FAMILY !== 'Windows') {
            $output = @shell_exec('nproc 2>/dev/null');
            if ($output !== null && is_numeric(trim($output))) {
                $cpuCount = (int)trim($output);
                return max(2, min($cpuCount - 1, 64));
            }
        }
        
        return 4; // Fallback
    }

    /**
     * Extract image text using Tesseract OCR
     */
    protected function extractImageTextWithTesseract(string $imagePath): ?array
    {
        try {
            // Check if Tesseract is available
            $tesseractPath = config('services.tesseract.path', 'tesseract');
            
            $command = escapeshellcmd($tesseractPath) . ' ' . escapeshellarg($imagePath) . ' stdout 2>/dev/null';
            $text = shell_exec($command);

            if ($text) {
                return [
                    'text' => trim($text),
                    'method' => 'tesseract',
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Tesseract OCR failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Extract tables from text using AI
     * Uses AiService for CPU-optimized inference
     */
    protected function extractTablesWithAI(string $text): ?array
    {
        try {
            $prompt = "Extract all tables from the following text. Return each table as a JSON array with headers and rows. Format: [{\"headers\": [...], \"rows\": [[...], [...]]}]\n\nText:\n" . substr($text, 0, 5000);

            // Use AiService's callOllama method which includes CPU optimization
            $response = $this->aiService->callOllama($prompt, [
                'format' => 'json',
                'temperature' => 0.3, // Lower temperature for more structured output
                'num_predict' => 2000,
            ]);

            if ($response && isset($response['response'])) {
                $responseText = $response['response'];
                
                // Try to parse JSON from response
                $jsonStart = strpos($responseText, '[');
                $jsonEnd = strrpos($responseText, ']');
                
                if ($jsonStart !== false && $jsonEnd !== false) {
                    $jsonString = substr($responseText, $jsonStart, $jsonEnd - $jsonStart + 1);
                    $tables = json_decode($jsonString, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($tables)) {
                        return [
                            'tables' => $tables,
                            'count' => count($tables),
                        ];
                    }
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('AI table extraction failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if Ollama vision model is available
     */
    protected function isOllamaVisionModelAvailable(string $model): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->ollamaBaseUrl}/api/tags");
            
            if ($response->successful()) {
                $data = $response->json();
                $models = $data['models'] ?? [];
                
                foreach ($models as $m) {
                    if (isset($m['name']) && str_contains($m['name'], $model)) {
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
     * Check if Tesseract OCR is available
     */
    protected function isTesseractAvailable(): bool
    {
        $tesseractPath = config('services.tesseract.path', 'tesseract');
        $output = shell_exec("{$tesseractPath} --version 2>&1");
        return !empty($output) && str_contains($output, 'tesseract');
    }
}

