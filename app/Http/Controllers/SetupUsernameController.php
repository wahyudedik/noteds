<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\View\View;

class SetupUsernameController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form for setting up username.
     */
    public function create(): View
    {
        // If user already has username, redirect to dashboard
        if (Auth::user()->username) {
            return redirect()->route('dashboard');
        }

        return view('setup-username.create');
    }

    /**
     * Store the username.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // If user already has username, redirect to dashboard
        if ($user->username) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash', // Only letters, numbers, dashes and underscores
                'lowercase',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ], [
            'username.required' => 'Username is required.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username must not exceed 30 characters.',
            'username.alpha_dash' => 'Username may only contain letters, numbers, dashes and underscores.',
            'username.lowercase' => 'Username must be lowercase.',
            'username.unique' => 'This username is already taken. Please choose another one.',
        ]);

        // Generate username from name if not provided (fallback)
        if (empty($validated['username'])) {
            $baseUsername = Str::slug($user->name, '');
            $username = $baseUsername;
            $counter = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $validated['username'] = $username;
        }

        $user->update([
            'username' => $validated['username'],
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Username setup successfully! Welcome to Noteds.');
    }
}
