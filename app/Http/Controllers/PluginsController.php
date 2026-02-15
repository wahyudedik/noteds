<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PluginsController extends Controller
{
    public function index(): Response
    {
        $plugins = Plugin::where('enabled', true)
            ->orderBy('is_paid', 'desc') // Show paid/featured first if desired
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Plugins/Index', [
            'plugins' => $plugins,
        ]);
    }

    public function show(Plugin $plugin): Response
    {
        if (!$plugin->enabled) {
            abort(404);
        }

        $bankAccounts = \App\Models\BankAccount::where('is_active', true)->get();

        return Inertia::render('Plugins/Show', [
            'plugin' => $plugin,
            'bankAccounts' => $bankAccounts,
            'adminWhatsapp' => \App\Models\PlatformSetting::get('admin_whatsapp', ''),
        ]);
    }
}
