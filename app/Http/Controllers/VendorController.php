<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use App\Models\ServiceQuote;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $vendorId = auth()->id();
        // Optimize: Add eager loading to prevent N+1 queries
        $assignedOrders = ServiceOrder::where('assigned_user_id', $vendorId)
            ->with(['user', 'serviceQuotes', 'attachments'])
            ->latest()
            ->paginate(10);
        
        $myQuotes = ServiceQuote::where('vendor_id', $vendorId)
            ->with(['user', 'order', 'attachments'])
            ->latest()
            ->paginate(10);
        
        return view('vendor.index', compact('assignedOrders', 'myQuotes'));
    }
}


