<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $vendors = User::role('vendor')->orderBy('name')->paginate(20);
        $unassignedOrders = \App\Models\ServiceOrder::whereNull('assigned_user_id')->latest()->paginate(20, ['*'], 'orders_page');
        return view('admin.vendors.index', compact('vendors', 'unassignedOrders'));
    }

    public function assign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'uuid', 'exists:service_orders,id'],
            'vendor_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $order = ServiceOrder::findOrFail($data['order_id']);
        $vendor = User::findOrFail($data['vendor_id']);
        if (!$vendor->hasRole('vendor')) {
            return back()->with('error', 'User terpilih bukan vendor.');
        }
        $order->assigned_user_id = $vendor->id;
        if ($order->status === 'submitted') {
            $order->status = 'quoted';
        }
        $order->save();

        \App\Models\AppNotification::create([
            'user_id' => $vendor->id,
            'type' => 'studio_order',
            'title' => 'Order ditugaskan ke Anda',
            'message' => 'Anda ditugaskan sebagai vendor untuk order "' . $order->title . '".',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['order_id' => $order->id],
        ]);
        try {
            \Illuminate\Support\Facades\Mail::to($vendor)->queue(
                new \App\Mail\StudioNotification(
                    'Order ditugaskan ke Anda',
                    'Anda ditugaskan sebagai vendor untuk order "' . $order->title . '".',
                    route('studio.orders.show', $order)
                )
            );
        } catch (\Throwable $e) {}

        \App\Models\OrderActivity::create([
            'service_order_id' => $order->id,
            'user_id' => auth()->id(),
            'action' => 'vendor_assigned',
            'description' => 'Admin menetapkan vendor ke order (via admin panel)',
            'meta' => ['vendor_id' => $vendor->id],
        ]);

        return back()->with('success', 'Vendor berhasil ditetapkan ke order.');
    }

    public function bulkAssign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['uuid', 'exists:service_orders,id'],
            'vendor_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $vendor = User::findOrFail($data['vendor_id']);
        if (!$vendor->hasRole('vendor')) {
            return back()->with('error', 'User terpilih bukan vendor.');
        }

        $orders = \App\Models\ServiceOrder::whereIn('id', $data['order_ids'])->get();
        foreach ($orders as $order) {
            $order->assigned_user_id = $vendor->id;
            if ($order->status === 'submitted') {
                $order->status = 'quoted';
            }
            $order->save();

            \App\Models\AppNotification::create([
                'user_id' => $vendor->id,
                'type' => 'studio_order',
                'title' => 'Order ditugaskan ke Anda',
                'message' => 'Anda ditugaskan sebagai vendor untuk order "' . $order->title . '".',
                'link' => route('studio.orders.show', $order),
                'is_read' => false,
                'data' => ['order_id' => $order->id],
            ]);

            \App\Models\OrderActivity::create([
                'service_order_id' => $order->id,
                'user_id' => auth()->id(),
                'action' => 'vendor_assigned',
                'description' => 'Admin menetapkan vendor (bulk) ke order',
                'meta' => ['vendor_id' => $vendor->id],
            ]);
        }

        return back()->with('success', 'Bulk assign berhasil: ' . count($orders) . ' orders ditetapkan ke ' . $vendor->name . '.');
    }
}


