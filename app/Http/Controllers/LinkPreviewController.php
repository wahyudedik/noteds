<?php

namespace App\Http\Controllers;

use App\Services\LinkPreviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LinkPreviewController extends Controller
{
    public function __construct(
        private LinkPreviewService $linkPreviewService
    ) {}

    /**
     * Generate link preview from URL.
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'url' => ['required', 'string', 'max:2048'],
            ]);

            // Additional URL validation
            $url = $validated['url'];
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid URL format.',
                    'errors' => ['url' => ['The URL format is invalid.']],
                ], 422);
            }

            $preview = $this->linkPreviewService->generatePreview($url);

            // Always return a preview (even if basic) to avoid 422 errors
            // The service will return a basic preview as fallback
            if (!$preview) {
                // Last resort: create minimal preview
                $host = parse_url($url, PHP_URL_HOST);
                $host = str_replace('www.', '', $host ?? '');
                
                $preview = [
                    'url' => $url,
                    'title' => $host ? ucfirst($host) : 'Link',
                    'description' => 'Click to view this link',
                    'image' => null,
                    'site_name' => $host ?: 'Website',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $preview,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Link preview generation error', [
                'url' => $request->url ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating preview.',
            ], 500);
        }
    }
}
