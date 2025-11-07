<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class ContentExtractorService
{
    protected string $ollamaBaseUrl;
    protected string $ollamaModel;

    public function __construct()
    {
        $this->ollamaBaseUrl = config('services.ollama.url', 'http://localhost:11434');
        $this->ollamaModel = config('services.ollama.model', 'llama3.2');
    }

    /**
     * Extract text from PDF file
     */
    public function extractPdfText(string $filePath): ?array
    {
        try {
            $fullPath = Storage::disk('private')->path($filePath);
            
            if (!file_exists($fullPath)) {
                return null;
            }

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
    }

    /**
     * Extract text from image using OCR (via Ollama vision model or Tesseract)
     */
    public function extractImageText(string $filePath): ?array
    {
        try {
            $fullPath = Storage::disk('private')->path($filePath);
            
            if (!file_exists($fullPath)) {
                return null;
            }

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
     */
    protected function extractImageTextWithOllama(string $imagePath, string $model): ?array
    {
        try {
            // Read image as base64
            $imageData = file_get_contents($imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = mime_content_type($imagePath);

            $response = Http::timeout(120)
                ->post("{$this->ollamaBaseUrl}/api/generate", [
                    'model' => $model,
                    'prompt' => 'Extract all text from this image. Return only the extracted text, no explanations.',
                    'images' => ["data:{$mimeType};base64,{$base64Image}"],
                    'stream' => false,
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
     */
    protected function extractTablesWithAI(string $text): ?array
    {
        try {
            $prompt = "Extract all tables from the following text. Return each table as a JSON array with headers and rows. Format: [{\"headers\": [...], \"rows\": [[...], [...]]}]\n\nText:\n" . substr($text, 0, 5000);

            $response = Http::timeout(60)
                ->post("{$this->ollamaBaseUrl}/api/generate", [
                    'model' => $this->ollamaModel,
                    'prompt' => $prompt,
                    'stream' => false,
                    'format' => 'json',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $responseText = $data['response'] ?? '';
                
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

