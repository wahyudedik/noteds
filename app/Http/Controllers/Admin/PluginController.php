<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PluginController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $plugins = Plugin::query()
            ->withCount('versions')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('Admin/Plugins/Index', [
            'plugins' => $plugins,
        ]);
    }

    public function upload(Request $request, PluginManager $manager)
    {
        $request->validate([
            'archive' => ['required', 'file', 'mimes:zip'],
        ]);

        $path = $manager->upload($request->file('archive'));
        return response()->json(['success' => true, 'path' => $path]);
    }

    public function install(Request $request, PluginManager $manager)
    {
        $request->validate([
            'archive_path' => ['required', 'string'],
        ]);
        $plugin = $manager->installFromArchive($request->input('archive_path'));
        return response()->json(['success' => true, 'plugin' => $plugin]);
    }

    public function activate(Plugin $plugin, PluginManager $manager)
    {
        $manager->activate($plugin);
        return response()->json(['success' => true]);
    }

    public function deactivate(Plugin $plugin, PluginManager $manager)
    {
        $manager->deactivate($plugin);
        return response()->json(['success' => true]);
    }

    public function show(Plugin $plugin)
    {
        $plugin->load(['versions' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }, 'logs' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(50);
        }]);

        return Inertia::render('Admin/Plugins/Show', [
            'plugin' => $plugin,
        ]);
    }

    public function rollback(Plugin $plugin, Request $request, PluginManager $manager)
    {
        $request->validate([
            'to_version' => ['required', 'string'],
        ]);
        $manager->rollback($plugin, $request->string('to_version'));
        return response()->json(['success' => true]);
    }

    public function updateConfig(Plugin $plugin, Request $request)
    {
        $data = $request->validate([
            'manifest' => ['array'],
            'permissions' => ['array'],
        ]);
        $plugin->update($data);
        return response()->json(['success' => true, 'plugin' => $plugin]);
    }
}

