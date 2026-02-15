<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $accounts = BankAccount::latest()->paginate(10);
        return Inertia::render('Admin/BankAccounts/Index', [
            'accounts' => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_holder' => 'required|string',
            'is_active' => 'boolean',
            'logo_url' => 'nullable|url'
        ]);

        BankAccount::create($validated);

        return redirect()->back()->with('success', 'Bank account added successfully.');
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'account_holder' => 'required|string',
            'is_active' => 'boolean',
            'logo_url' => 'nullable|url'
        ]);

        $bankAccount->update($validated);

        return redirect()->back()->with('success', 'Bank account updated successfully.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();
        return redirect()->back()->with('success', 'Bank account deleted successfully.');
    }
}
