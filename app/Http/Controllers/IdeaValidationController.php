<?php

namespace App\Http\Controllers;

use App\Models\IdeaValidation;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IdeaValidationController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        // Only allow validation for validate_idea posts
        if ($post->purpose_type !== 'validate_idea') {
            return back()->withErrors(['error' => 'This post is not for validation.']);
        }

        $validated = $request->validate([
            'validation_status' => ['required', 'in:layak,tidak_layak'],
            'estimated_capital' => ['nullable', 'numeric', 'min:0'],
            'estimated_bep' => ['nullable', 'numeric', 'min:0'],
            'feedback' => ['nullable', 'string', 'max:2000'],
            'risks' => ['nullable', 'array'],
            'risks.*' => ['string', 'max:500'],
        ]);

        IdeaValidation::updateOrCreate(
            [
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
            ],
            $validated
        );

        return back()->with('success', 'Validation submitted successfully.');
    }

    public function getStats(Post $post)
    {
        $validations = IdeaValidation::where('post_id', $post->id)->get();

        $layakCount = $validations->where('validation_status', 'layak')->count();
        $tidakLayakCount = $validations->where('validation_status', 'tidak_layak')->count();
        $total = $validations->count();
        $approvalPercentage = $total > 0 ? ($layakCount / $total) * 100 : 0;

        $avgCapital = $validations->whereNotNull('estimated_capital')->avg('estimated_capital');
        $avgBep = $validations->whereNotNull('estimated_bep')->avg('estimated_bep');

        $allRisks = $validations->whereNotNull('risks')
            ->pluck('risks')
            ->flatten()
            ->unique()
            ->values();

        return [
            'total' => $total,
            'layak' => $layakCount,
            'tidak_layak' => $tidakLayakCount,
            'approval_percentage' => round($approvalPercentage, 2),
            'avg_capital' => $avgCapital ? round($avgCapital, 2) : null,
            'avg_bep' => $avgBep ? round($avgBep, 2) : null,
            'common_risks' => $allRisks->take(5)->toArray(),
        ];
    }
}
