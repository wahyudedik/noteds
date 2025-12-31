<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    /**
     * Display a listing of published FAQs grouped by category.
     */
    public function index()
    {
        $faqs = Faq::published()
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('category');

        return Inertia::render('Faqs/Index', [
            'faqs' => $faqs,
        ]);
    }

    /**
     * Display the specified FAQ.
     */
    public function show(string $id)
    {
        $faq = Faq::published()->findOrFail($id);
        
        // Increment views
        $faq->incrementViews();

        // Get related FAQs by category
        $relatedFaqs = Faq::published()
            ->where('category', $faq->category)
            ->where('id', '!=', $faq->id)
            ->orderBy('order')
            ->limit(5)
            ->get();

        return Inertia::render('Faqs/Show', [
            'faq' => $faq,
            'relatedFaqs' => $relatedFaqs,
        ]);
    }
}
