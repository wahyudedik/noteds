<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCmsPageController extends Controller
{
    /**
     * Display a listing of active CMS pages.
     */
    public function index(): View
    {
        $pages = CmsPage::active()
            ->latest()
            ->paginate(12);

        return view('40-shared/cms/index', compact('pages'));
    }

    /**
     * Display the specified CMS page.
     */
    public function show(CmsPage $cmsPage): View
    {
        if (!$cmsPage->is_active) {
            abort(404);
        }

        return view('40-shared/cms/show', compact('cmsPage'));
    }
}

