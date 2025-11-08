<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}
    public function show(string $username): View|RedirectResponse
    {
        $user = User::where('username', $username)->firstOrFail();
        $viewer = auth()->user();
        
        $user->load(['notes' => function ($query) {
            $query->where('is_public', true)
                ->where('status', 'active')
                ->with(['tags', 'reviews'])
                ->latest();
        }]);

        // Get public notes with pagination
        $publicNotes = $user->notes()
            ->where('is_public', true)
            ->where('status', 'active')
            ->with(['tags', 'reviews'])
            ->latest()
            ->paginate(12);

        // Calculate seller statistics
        $stats = [
            'total_notes' => $user->notes()->where('is_public', true)->count(),
            'total_sales' => $user->transactionsAsSeller()->where('status', 'success')->count(),
            'total_revenue' => $user->transactionsAsSeller()->where('status', 'success')->sum('amount'),
            'average_rating' => $this->calculateAverageRating($user),
            'total_reviews' => $this->getTotalReviewsCount($user),
            'total_posts' => $user->posts()->whereNull('parent_id')->where('is_hidden', false)->published()->visibleTo($viewer)->count(),
        ];

        // Get user posts for posts tab
        $userPosts = $user->posts()
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->published()
            ->visibleTo($viewer)
            ->with(['note', 'likes'])
            ->withCount(['replies', 'allComments'])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(12);

        return view('public.profile.show', compact('user', 'publicNotes', 'stats', 'userPosts'));
    }

    /**
     * Calculate average rating from all notes by this user.
     */
    private function calculateAverageRating(User $user): float
    {
        $notes = $user->notes()->where('is_public', true)->pluck('id');
        
        if ($notes->isEmpty()) {
            return 0;
        }

        $avgRating = \App\Models\NoteReview::whereIn('note_id', $notes)->avg('rating');
        
        return round($avgRating ?? 0, 1);
    }

    /**
     * Get total number of reviews for all notes by this user.
     */
    private function getTotalReviewsCount(User $user): int
    {
        $notes = $user->notes()->where('is_public', true)->pluck('id');
        
        if ($notes->isEmpty()) {
            return 0;
        }

        return \App\Models\NoteReview::whereIn('note_id', $notes)->count();
    }

    /**
     * Show AI chat interface for seller profile.
     * All users can use this feature (not premium-only).
     */
    public function aiChat(string $username): View
    {
        $seller = User::where('username', $username)->firstOrFail();
        
        // Get public notes from this seller for AI context
        $publicNotes = $seller->notes()
            ->where('is_public', true)
            ->where('status', 'active')
            ->with(['tags'])
            ->latest()
            ->get(['id', 'title', 'content', 'summary', 'created_at']);

        // Check if AI service is available
        $aiAvailable = $this->aiService->isAvailable();

        return view('public.profile.ai-chat', compact('seller', 'publicNotes', 'aiAvailable'));
    }

    /**
     * Handle AI chat question for seller profile.
     * All users can use this feature (not premium-only).
     */
    public function askSeller(Request $request, string $username): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'required|string|max:500',
        ]);

        $seller = User::where('username', $username)->firstOrFail();
        $question = $validated['question'];

        // Get public notes from this seller for AI context
        $notes = $seller->notes()
            ->where('is_public', true)
            ->where('status', 'active')
            ->latest()
            ->limit(100) // Limit for performance
            ->get(['id', 'title', 'content', 'summary']);

        if ($notes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Seller belum memiliki notes public. Tidak ada data untuk ditanyakan.',
            ], 404);
        }

        // Check if Ollama is available
        if (!$this->aiService->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'AI service sedang tidak tersedia. Silakan coba lagi nanti.',
            ], 503);
        }

        try {
            // Prepare note contents for AI with note IDs
            $noteContents = $notes->map(function($note) {
                $content = strip_tags($note->content ?? '');
                $summary = $note->summary ?? '';
                return "Title: {$note->title}\n" . 
                       ($summary ? "Summary: {$summary}\n" : '') . 
                       "Content: " . \Illuminate\Support\Str::limit($content, 2000);
            })->toArray();
            
            $noteIds = $notes->pluck('id')->toArray();

            // Get answer from AI with note references
            $result = $this->aiService->answerQuestion($question, $noteContents, $noteIds);

            if ($result && isset($result['answer'])) {
                // Get referenced note details
                $referencedNotes = [];
                if (!empty($result['referenced_note_ids'])) {
                    $referencedNotesData = $notes->whereIn('id', $result['referenced_note_ids']);
                    
                    $referencedNotes = $referencedNotesData->map(function($note) {
                        return [
                            'id' => $note->id,
                            'title' => $note->title,
                        ];
                    })->values()->toArray();
                }

                return response()->json([
                    'success' => true,
                    'answer' => $result['answer'],
                    'referenced_notes' => $referencedNotes,
                    'seller_name' => $seller->name,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan jawaban dari AI. Silakan coba lagi.',
            ], 500);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Seller AI chat failed', [
                'error' => $e->getMessage(),
                'seller_id' => $seller->id,
                'question' => $question,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pertanyaan. Silakan coba lagi.',
            ], 500);
        }
    }
}
