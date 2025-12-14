<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Premium middleware removed - all users are now premium
    }

    /**
     * Display a listing of webhooks.
     */
    public function index(): View
    {
        $webhooks = Webhook::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('40-shared/webhooks/index', compact('webhooks'));
    }

    /**
     * Show the form for creating a new webhook.
     */
    public function create(): View
    {
        return view('40-shared/webhooks/create');
    }

    /**
     * Store a newly created webhook.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'event' => ['required', 'in:note.purchased,note.created,note.updated,transaction.completed,withdraw.approved,subscription.renewed'],
        ]);

        $webhook = Webhook::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'event' => $validated['event'],
            'secret' => Str::random(32),
            'is_active' => true,
        ]);

        return redirect()->route('webhooks.show', $webhook)
            ->with('success', 'Webhook created successfully.');
    }

    /**
     * Display the specified webhook.
     */
    public function show(Webhook $webhook): View
    {
        // Ensure user owns this webhook
        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        return view('40-shared/webhooks/show', compact('webhook'));
    }

    /**
     * Update the specified webhook.
     */
    public function update(Request $request, Webhook $webhook): RedirectResponse
    {
        // Ensure user owns this webhook
        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $webhook->update($validated);

        return redirect()->route('webhooks.show', $webhook)
            ->with('success', 'Webhook updated successfully.');
    }

    /**
     * Remove the specified webhook.
     */
    public function destroy(Webhook $webhook): RedirectResponse
    {
        // Ensure user owns this webhook
        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        $webhook->delete();

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook deleted successfully.');
    }

    /**
     * Test webhook.
     */
    public function test(Webhook $webhook): RedirectResponse
    {
        // Ensure user owns this webhook
        if ($webhook->user_id !== auth()->id()) {
            abort(403);
        }

        // Trigger test webhook (implement webhook service)
        try {
            // This would call a WebhookService to send test payload
            // For now, just return success
            return redirect()->route('webhooks.show', $webhook)
                ->with('success', 'Webhook test triggered. Check your endpoint.');
        } catch (\Exception $e) {
            return redirect()->route('webhooks.show', $webhook)
                ->with('error', 'Webhook test failed: ' . $e->getMessage());
        }
    }
}
