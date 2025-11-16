<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StudioController extends Controller
{
    public function index(): View
    {
        return view('studio.index');
    }
}


