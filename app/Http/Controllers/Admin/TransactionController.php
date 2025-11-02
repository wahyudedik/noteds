<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::with(['buyer', 'seller', 'note'])
            ->when($request->search, function ($query) use ($request) {
                return $query->whereHas('buyer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                })->orWhereHas('seller', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                return $query->where('status', $request->status);
            })
            ->when($request->payment_method, function ($query) use ($request) {
                return $query->where('payment_method', $request->payment_method);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalRevenue = Transaction::where('status', 'success')->sum('commission');
        $totalTransactions = Transaction::where('status', 'success')->sum('amount');

        return view('admin.transactions.index', compact('transactions', 'totalRevenue', 'totalTransactions'));
    }
}
