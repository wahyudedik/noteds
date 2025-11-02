<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exchangeRates = ExchangeRate::orderBy('from_currency')
            ->orderBy('to_currency')
            ->get();
        
        return view('admin.exchange-rates.index', compact('exchangeRates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.exchange-rates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_currency' => ['required', 'in:IDR,USD'],
            'to_currency' => ['required', 'in:IDR,USD', 'different:from_currency'],
            'rate' => ['required', 'numeric', 'min:0.0001'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Check if combination already exists
        $exists = ExchangeRate::where('from_currency', $validated['from_currency'])
            ->where('to_currency', $validated['to_currency'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Exchange rate for this currency pair already exists.');
        }

        ExchangeRate::create($validated);

        return redirect()->route('admin.exchange-rates.index')
            ->with('success', 'Exchange rate created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExchangeRate $exchangeRate)
    {
        return view('admin.exchange-rates.edit', compact('exchangeRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExchangeRate $exchangeRate)
    {
        $validated = $request->validate([
            'rate' => ['required', 'numeric', 'min:0.0001'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $exchangeRate->update($validated);

        return redirect()->route('admin.exchange-rates.index')
            ->with('success', 'Exchange rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExchangeRate $exchangeRate)
    {
        $exchangeRate->delete();

        return redirect()->route('admin.exchange-rates.index')
            ->with('success', 'Exchange rate deleted successfully.');
    }
}
