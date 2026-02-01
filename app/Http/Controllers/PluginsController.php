<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Inertia\Inertia;
use Inertia\Response;

class PluginsController extends Controller
{
    public function index(): Response
    {
        $plugins = Plugin::where('enabled', true)
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Plugins/Index', [
            'plugins' => $plugins,
        ]);
    }
}

