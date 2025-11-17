<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceQuote;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServiceQuoteController extends Controller
{
    public function create(ServiceOrder $order): View|RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $isAdmin = $user->hasRole('admin');
        $isAssignedVendor = $user->hasRole('vendor') && $order->assigned_user_id === $user->id;
        if (!$isAdmin && !$isAssignedVendor) {
            return redirect()->route('studio.orders.show', $order)->with('error', 'Tidak diizinkan membuat quote.');
        }
        return view('studio.quotes.create', compact('order'));
    }

    public function store(Request $request, ServiceOrder $order): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $isAdmin = $user->hasRole('admin');
        $isAssignedVendor = $user->hasRole('vendor') && $order->assigned_user_id === $user->id;
        if (!$isAdmin && !$isAssignedVendor) {
            return redirect()->route('studio.orders.show', $order)->with('error', 'Tidak diizinkan mengirim quote.');
        }

        if ($isAdmin) {
            $data = $request->validate([
                'vendor_id' => ['required', 'exists:users,id'],
                'total_amount' => ['required', 'numeric', 'min:1'],
                'milestones' => ['nullable', 'array'],
                'milestones.*.title' => ['required_with:milestones', 'string', 'max:255'],
                'milestones.*.description' => ['nullable', 'string'],
                'milestones.*.amount' => ['required_with:milestones', 'numeric', 'min:0'],
            ]);
            $vendorId = $data['vendor_id'];
        } else {
            $data = $request->validate([
                'total_amount' => ['required', 'numeric', 'min:1'],
                'milestones' => ['nullable', 'array'],
                'milestones.*.title' => ['required_with:milestones', 'string', 'max:255'],
                'milestones.*.description' => ['nullable', 'string'],
                'milestones.*.amount' => ['required_with:milestones', 'numeric', 'min:0'],
            ]);
            $vendorId = $user->id;
        }

        $quote = ServiceQuote::create([
            'service_order_id' => $order->id,
            'vendor_id' => $vendorId,
            'total_amount' => (float) $data['total_amount'],
            'milestones' => $data['milestones'] ?? [],
            'status' => 'pending',
        ]);

        $order->status = 'submitted';
        $order->save();

        // Notify buyer
        \App\Models\AppNotification::create([
            'user_id' => $order->user_id,
            'type' => 'studio_quote',
            'title' => 'Quote baru untuk order Anda',
            'message' => 'Vendor mengirimkan quote untuk "' . $order->title . '".',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['quote_id' => $quote->id],
        ]);
        $userId = Auth::id();
        if ($userId) {
            \App\Models\OrderActivity::create([
                'service_order_id' => $order->id,
                'user_id' => $userId,
                'action' => 'quote_created',
                'description' => 'Quote dibuat oleh admin/vendor',
                'meta' => ['quote_id' => $quote->id],
            ]);
        }
        if ((bool) \App\Models\Setting::getSetting('studio_email_quote_created', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user)->queue(
                    new \App\Mail\StudioNotification(
                        'Quote baru untuk order Anda',
                        'Vendor mengirimkan quote untuk "' . $order->title . '".',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('studio.orders.show', $order)->with('success', 'Quote dikirim.');
    }

    public function accept(ServiceQuote $quote): RedirectResponse
    {
        $order = $quote->order;
        $userId = Auth::id();
        // Only order owner can accept
        if (!$userId || $order->user_id !== $userId) {
            return redirect()->route('studio.orders.show', $order)->with('error', 'Tidak diizinkan.');
        }

        $quote->status = 'accepted';
        $quote->save();

        // Reject other pending quotes
        ServiceQuote::where('service_order_id', $order->id)
            ->where('id', '!=', $quote->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        // Apply to order
        $order->assigned_user_id = $quote->vendor_id;
        $order->milestones = $quote->milestones ?? [];
        $order->status = 'quoted';
        $order->save();

        // Notify vendor
        \App\Models\AppNotification::create([
            'user_id' => $quote->vendor_id,
            'type' => 'studio_quote',
            'title' => 'Quote Anda diterima',
            'message' => 'Quote untuk "' . $order->title . '" telah diterima. Order siap didanai (escrow).',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['quote_id' => $quote->id],
        ]);
        if ($userId) {
            \App\Models\OrderActivity::create([
                'service_order_id' => $order->id,
                'user_id' => $userId,
                'action' => 'quote_accepted',
                'description' => 'Buyer menerima quote',
                'meta' => ['quote_id' => $quote->id],
            ]);
        }
        if ((bool) \App\Models\Setting::getSetting('studio_email_quote_accepted', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($quote->vendor)->queue(
                    new \App\Mail\StudioNotification(
                        'Quote Anda diterima',
                        'Quote untuk "' . $order->title . '" telah diterima. Order siap didanai (escrow).',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('studio.orders.show', $order)->with('success', 'Quote diterima. Silakan fund escrow untuk memulai.');
    }

    public function reject(ServiceQuote $quote): RedirectResponse
    {
        $order = $quote->order;
        /** @var User|null $user */
        $user = Auth::user();
        $userId = Auth::id();
        if (!$user || ($order->user_id !== $userId && !$user->hasRole('admin'))) {
            return redirect()->route('studio.orders.show', $order)->with('error', 'Tidak diizinkan.');
        }
        $quote->status = 'rejected';
        $quote->save();

        // Notify vendor
        \App\Models\AppNotification::create([
            'user_id' => $quote->vendor_id,
            'type' => 'studio_quote',
            'title' => 'Quote Anda ditolak',
            'message' => 'Quote untuk "' . $order->title . '" ditolak.',
            'link' => route('studio.orders.show', $order),
            'is_read' => false,
            'data' => ['quote_id' => $quote->id],
        ]);
        if ($userId) {
            \App\Models\OrderActivity::create([
                'service_order_id' => $order->id,
                'user_id' => $userId,
                'action' => 'quote_rejected',
                'description' => 'Buyer menolak quote',
                'meta' => ['quote_id' => $quote->id],
            ]);
        }
        if ((bool) \App\Models\Setting::getSetting('studio_email_quote_rejected', 'studio', true)) {
            try {
                \Illuminate\Support\Facades\Mail::to($quote->vendor)->queue(
                    new \App\Mail\StudioNotification(
                        'Quote Anda ditolak',
                        'Quote untuk "' . $order->title . '" ditolak.',
                        route('studio.orders.show', $order)
                    )
                );
            } catch (\Throwable $e) {
            }
        }

        return redirect()->route('studio.orders.show', $order)->with('success', 'Quote ditolak.');
    }
}
