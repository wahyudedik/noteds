<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceQuote;
use Illuminate\View\View;

class StudioController extends Controller
{
    public function index(): View
    {
        $vendorId = auth()->id();

        $assignedOrders = ServiceOrder::where('assigned_user_id', $vendorId)
            ->with(['user', 'serviceQuotes', 'attachments'])
            ->latest()
            ->paginate(10);

        $myQuotes = ServiceQuote::where('vendor_id', $vendorId)
            ->with(['user', 'order', 'attachments'])
            ->latest()
            ->paginate(10);

        return view('40-shared/vendor/index', compact('assignedOrders', 'myQuotes'));
    }
}
