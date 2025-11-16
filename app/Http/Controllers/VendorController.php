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
        $assignedOrders = ServiceOrder::where('assigned_user_id', $vendorId)->latest()->paginate(10);
        $myQuotes = ServiceQuote::where('vendor_id', $vendorId)->latest()->paginate(10);
        return view('vendor.index', compact('assignedOrders', 'myQuotes'));
    }
}


