<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle avatar file upload
        if ($request->hasFile('avatar_file')) {
            $file = $request->file('avatar_file');
            
            // Delete old avatar if it's a stored file (not URL)
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Ensure avatars directory exists
            if (!Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
            }
            
            // Generate unique filename
            $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars/' . $user->id, $filename, 'public');
            
            // Store the path (relative to public storage)
            $validated['avatar'] = $path;
        } elseif ($request->filled('avatar') && str_starts_with($request->input('avatar'), 'http')) {
            // If URL is provided and it's a valid URL, use it
            // Delete old stored avatar if exists
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->input('avatar');
        } elseif (!$request->filled('avatar') && $user->avatar && !str_starts_with($user->avatar, 'http')) {
            // If avatar URL is cleared and we have a stored file, keep the stored file
            unset($validated['avatar']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
