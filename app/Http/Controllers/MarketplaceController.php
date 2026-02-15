<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Models\PluginOrder;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function orders(): Response
    {
        $orders = PluginOrder::with('plugin')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Plugins/Orders', [
            'orders' => $orders,
            'adminWhatsapp' => \App\Models\PlatformSetting::get('admin_whatsapp', ''),
        ]);
    }

    public function buy(Request $request, Plugin $plugin)
    {
        if (!$plugin->is_paid) {
            return redirect()->back()->with('error', 'This plugin is free.');
        }

        $request->validate([
            'proof_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'buyer_name' => ['required', 'string', 'max:255'],
            'buyer_whatsapp' => ['required', 'string', 'max:20'],
            'bank_id' => ['required', 'exists:bank_accounts,id'],
        ]);

        $path = $request->file('proof_file')->store('proofs', 'public');

        $order = PluginOrder::create([
            'id' => Str::uuid(),
            'user_id' => Auth::id(),
            'plugin_id' => $plugin->id,
            'price_paid' => $plugin->price,
            'payment_status' => 'pending',
            'proof_file' => $path,
            'buyer_name' => $request->buyer_name,
            'buyer_whatsapp' => $request->buyer_whatsapp,
            'bank_id' => $request->bank_id,
        ]);

        // Send confirmation email
        \Illuminate\Support\Facades\Mail::to(Auth::user()->email)->send(new \App\Mail\OrderSubmitted($order));

        return redirect()->route('marketplace.orders')->with('success', 'Order submitted. Please wait for admin approval.');
    }

    public function download(Plugin $plugin)
    {
        if (!$plugin->file_path) {
            abort(404);
        }

        // If paid, ensure current user has a paid order
        if ($plugin->is_paid) {
            $hasPaidOrder = \App\Models\PluginOrder::where('plugin_id', $plugin->id)
                ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->where('payment_status', 'paid')
                ->exists();
            if (!$hasPaidOrder) {
                abort(403);
            }
        }

        $filename = ($plugin->slug ?? 'product') . '-v' . ($plugin->version ?? 'latest');
        // Attempt to guess extension from path
        $ext = pathinfo($plugin->file_path, PATHINFO_EXTENSION);
        if (!empty($ext)) {
            $filename .= '.' . $ext;
        }

        return \Illuminate\Support\Facades\Storage::download($plugin->file_path, $filename);
    }
}
