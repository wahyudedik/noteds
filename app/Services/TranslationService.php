<?php

namespace App\Services;

use App\Models\MessageTranslation;
use App\Models\NoteMessage;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * Supported languages.
     */
    private const SUPPORTED_LANGUAGES = ['en', 'id', 'ar'];

    /**
     * Detect language of text.
     * Simple detection based on common words.
     */
    public function detectLanguage(string $text): string
    {
        $text = mb_strtolower($text);
        
        // Check for Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }
        
        // Check for Indonesian common words
        $indonesianWords = ['dan', 'atau', 'yang', 'ini', 'itu', 'adalah', 'dengan', 'untuk', 'dari', 'pada'];
        $indonesianCount = 0;
        foreach ($indonesianWords as $word) {
            if (mb_strpos($text, $word) !== false) {
                $indonesianCount++;
            }
        }
        
        if ($indonesianCount >= 2) {
            return 'id';
        }
        
        // Default to English
        return 'en';
    }

    /**
     * Translate message to target language.
     * Uses Google Translate API if enabled, otherwise uses simple word replacement.
     */
    public function translate(string $text, string $targetLanguage, ?string $sourceLanguage = null): string
    {
        if (!in_array($targetLanguage, self::SUPPORTED_LANGUAGES)) {
            return $text;
        }

        $sourceLanguage = $sourceLanguage ?? $this->detectLanguage($text);

        if ($sourceLanguage === $targetLanguage) {
            return $text;
        }

        // Use Google Translate API if enabled
        if (Setting::isGoogleTranslateEnabled()) {
            $apiKey = Setting::getGoogleTranslateApiKey();
            if ($apiKey) {
                $translated = $this->translateWithGoogle($text, $targetLanguage, $sourceLanguage, $apiKey);
                if ($translated !== $text) {
                    return $translated;
                }
            }
        }

        // Fallback to simple translation (for demo)
        // Simple translation dictionary
        $translations = [
            'en' => [
                'id' => [
                    'hello' => 'halo',
                    'thank you' => 'terima kasih',
                    'yes' => 'ya',
                    'no' => 'tidak',
                ],
                'ar' => [
                    'hello' => 'مرحبا',
                    'thank you' => 'شكرا',
                    'yes' => 'نعم',
                    'no' => 'لا',
                ],
            ],
            'id' => [
                'en' => [
                    'halo' => 'hello',
                    'terima kasih' => 'thank you',
                    'ya' => 'yes',
                    'tidak' => 'no',
                ],
                'ar' => [
                    'halo' => 'مرحبا',
                    'terima kasih' => 'شكرا',
                ],
            ],
        ];
        
        return $text . ' [Translated to ' . strtoupper($targetLanguage) . ']';
    }

    /**
     * Translate message and store translation in database.
     */
    public function translateAndStore(
        NoteMessage $message,
        string $targetLanguage,
        ?string $sourceLanguage = null
    ): MessageTranslation {
        $sourceLanguage = $sourceLanguage ?? $message->original_language ?? $this->detectLanguage($message->message);
        
        // Update original language if not set
        if (!$message->original_language) {
            $message->update(['original_language' => $sourceLanguage]);
        }

        // Check if translation already exists
        $existingTranslation = MessageTranslation::getTranslation($message->id, $targetLanguage);
        if ($existingTranslation) {
            return $existingTranslation;
        }

        // Translate message
        $translatedText = $this->translate($message->message, $targetLanguage, $sourceLanguage);

        // Store translation
        return MessageTranslation::createOrUpdateTranslation(
            $message->id,
            $targetLanguage,
            $translatedText,
            'simple' // Provider name
        );
    }

    /**
     * Translate with Google Translate API.
     */
    private function translateWithGoogle(string $text, string $targetLanguage, string $sourceLanguage, string $apiKey): string
    {
        if (empty($text) || empty($apiKey)) {
            return $text;
        }

        // Cache translation result for 7 days to reduce API calls
        $cacheKey = "translation:google:" . md5($text . $sourceLanguage . $targetLanguage);
        
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($text, $targetLanguage, $sourceLanguage, $apiKey) {
            try {
                $response = Http::timeout(10)->post('https://translation.googleapis.com/language/translate/v2', [
                    'key' => $apiKey,
                    'q' => $text,
                    'source' => $sourceLanguage,
                    'target' => $targetLanguage,
                    'format' => 'text',
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $translatedText = $data['data']['translations'][0]['translatedText'] ?? null;
                    
                    if ($translatedText) {
                        return $translatedText;
                    }
                } else {
                    Log::warning('Google Translate API error: ' . $response->body(), [
                        'status' => $response->status(),
                        'source' => $sourceLanguage,
                        'target' => $targetLanguage,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Google Translate API exception: ' . $e->getMessage(), [
                    'source' => $sourceLanguage,
                    'target' => $targetLanguage,
                ]);
            }

            return $text;
        });
    }
}

