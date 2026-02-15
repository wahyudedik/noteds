<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Services\PluginManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Mail\PluginUpdated;

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
            'archive' => ['required', 'file', 'mimes:zip', 'max:512000'], // Max 500MB
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

    public function update(Plugin $plugin, Request $request)
    {
        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'demo_url' => ['nullable', 'url'],
            'thumbnail_url' => ['nullable', 'string'],
            'is_paid' => ['boolean'],
            'description' => ['nullable', 'string'],
            'category' => ['required', 'in:web,desktop,mobile'],
            'screenshots' => ['nullable', 'array'],
            'system_requirements' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string'],
            'file_size' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $plugin->update($validated);

        return redirect()->back()->with('success', 'Plugin details updated successfully.');
    }

    public function updateVersion(Plugin $plugin, Request $request, PluginManager $pluginManager)
    {
        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:512000', // Max 500MB
        ]);

        try {
            // Upload archive
            $archivePath = $pluginManager->upload($request->file('plugin_file'));

            // Install/Update (this handles version detection and DB update)
            $updatedPlugin = $pluginManager->installFromArchive($archivePath);

            // Ensure we updated the correct plugin
            if ($updatedPlugin->id !== $plugin->id) {
                // In case slug changed or mismatch, though install() uses slug from manifest.
                // If slug is different, it might create a new plugin.
                // Ideally, we should check slug before install, but install() extracts and reads manifest.
            }

            // Send notification to buyers
            $this->notifyBuyersOfUpdate($updatedPlugin);

            return redirect()->back()->with('success', 'Plugin updated to version ' . $updatedPlugin->version);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['plugin_file' => 'Update failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Upload a downloadable product file for this plugin/product.
     * This does not install/extract, it simply stores the file for buyers to download.
     */
    public function uploadDownload(Plugin $plugin, Request $request)
    {
        $validated = $request->validate([
            'download_file' => ['required', 'file', 'max:512000'], // up to ~500MB
            'version' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $request->file('download_file');
        $path = $file->store('products', config('filesystems.default')); // use default disk (local)
        $size = $file->getSize();

        $plugin->file_path = $path;
        $plugin->file_size = (string) $size;
        if (!empty($validated['version'])) {
            $plugin->version = $validated['version'];
        }
        $plugin->save();

        return redirect()->back()->with('success', 'Download file uploaded successfully.');
    }

    protected function notifyBuyersOfUpdate(Plugin $plugin)
    {
        // Collect buyers via orders to ensure we pass a proper User model instance
        $orders = \App\Models\PluginOrder::with('user')
            ->where('plugin_id', $plugin->id)
            ->where('payment_status', 'paid')
            ->get();

        foreach ($orders as $order) {
            if ($order->user) {
                \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new PluginUpdated($plugin, $order->user));
            }
        }
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
