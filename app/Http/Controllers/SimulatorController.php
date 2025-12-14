<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SimulatorController extends Controller
{
    /**
     * Display the simulators landing page.
     */
    public function index(): View
    {
        return view('40-shared/simulators/index');
    }
}
