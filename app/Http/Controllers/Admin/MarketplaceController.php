<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PluginOrder;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MarketplaceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function orders()
    {
        $orders = PluginOrder::with(['user', 'plugin', 'bankAccount'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Plugins/Orders', [
            'orders' => $orders,
        ]);
    }

    public function updateOrder(PluginOrder $order, Request $request)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,rejected',
            'admin_note' => 'nullable|string',
        ]);

        $originalStatus = $order->payment_status;
        $order->update($validated);

        if ($originalStatus !== 'paid' && $validated['payment_status'] === 'paid') {
            // Send verified email
            \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\PaymentVerified($order));
        }

        return back()->with('success', 'Order updated successfully.');
    }

    public function exportOrders()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\OrdersExport, 'orders_' . date('Y-m-d_H-i') . '.xlsx');
    }

    public function settings()
    {
        $bankAccounts = PlatformSetting::get('bank_accounts', []);
        $adminWhatsapp = PlatformSetting::get('admin_whatsapp', '');

        return Inertia::render('Admin/Plugins/Settings', [
            'bank_accounts' => $bankAccounts,
            'admin_whatsapp' => $adminWhatsapp,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'bank_accounts' => 'array',
            'bank_accounts.*.bank_name' => 'required|string',
            'bank_accounts.*.account_number' => 'required|string',
            'bank_accounts.*.account_holder' => 'required|string',
            'admin_whatsapp' => 'nullable|string',
        ]);

        if (array_key_exists('bank_accounts', $validated)) {
            PlatformSetting::set('bank_accounts', $validated['bank_accounts'], 'json');
        }
        if (array_key_exists('admin_whatsapp', $validated)) {
            PlatformSetting::set('admin_whatsapp', $validated['admin_whatsapp'], 'string');
        }

        return back()->with('success', 'Marketplace settings updated.');
    }
}
