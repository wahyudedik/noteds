<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class EcosystemController extends Controller
{
    public function index(): View
    {
        return view('40-shared/ecosystem/index');
    }

    public function audio(): View
    {
        return view('40-shared/ecosystem/audio');
    }

    public function code(): View
    {
        return view('40-shared/ecosystem/code');
    }

    public function graphics(): View
    {
        return view('40-shared/ecosystem/graphics');
    }

    public function photos(): View
    {
        return view('40-shared/ecosystem/photos');
    }

    public function themes(): View
    {
        return view('40-shared/ecosystem/themes');
    }

    public function videos(): View
    {
        return view('40-shared/ecosystem/videos');
    }

    public function threeD(): View
    {
        return view('40-shared/ecosystem/3d');
    }
}


