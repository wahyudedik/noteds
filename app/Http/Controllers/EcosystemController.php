<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EcosystemController extends Controller
{
    public function index(): View
    {
        return view('ecosystem.index');
    }

    public function audio(): View
    {
        return view('ecosystem.audio');
    }

    public function code(): View
    {
        return view('ecosystem.code');
    }

    public function graphics(): View
    {
        return view('ecosystem.graphics');
    }

    public function photos(): View
    {
        return view('ecosystem.photos');
    }

    public function themes(): View
    {
        return view('ecosystem.themes');
    }

    public function videos(): View
    {
        return view('ecosystem.videos');
    }

    public function threeD(): View
    {
        return view('ecosystem.3d');
    }
}


