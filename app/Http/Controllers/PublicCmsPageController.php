<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicCmsPageController extends Controller
{
    /**
     * Display the specified CMS page.
     */
    public function show(CmsPage $cmsPage): View
    {
        if (!$cmsPage->is_active) {
            abort(404);
        }

        return view('cms.show', compact('cmsPage'));
    }
}

