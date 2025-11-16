<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TutsController extends Controller
{
    public function index(): View
    {
        return view('tuts.index');
    }
}


