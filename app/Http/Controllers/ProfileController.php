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

        // Track if both KTP and selfie are now uploaded for the first time
        $hadKtpBefore = (bool) $user->ktp_path;
        $hadSelfieBefore = (bool) $user->selfie_path;
        $bothUploadedForFirstTime = false;
        
        // Handle KTP/Kartu Pelajar file upload
        if ($request->hasFile('ktp_file')) {
            // Delete old KTP/Kartu Pelajar if exists
            if ($user->ktp_path) {
                Storage::disk('private')->delete($user->ktp_path);
            }
            
            // Determine document type and directory
            $documentType = $request->input('document_type', 'ktp'); // Default to ktp if not specified
            $directory = $documentType === 'kartu_pelajar' ? 'kyc/kartu_pelajar' : 'kyc/ktp';
            
            // Ensure kyc directory exists
            if (!Storage::disk('private')->exists($directory)) {
                Storage::disk('private')->makeDirectory($directory, 0755, true);
            }
            
            // Store in private disk
            $ktpPath = $request->file('ktp_file')->store($directory, 'private');
            $validated['ktp_path'] = $ktpPath;
            $validated['document_type'] = $documentType;
            
            // Reset verification status to pending if document is updated
            if ($user->verification_status === 'verified') {
                $validated['verification_status'] = 'pending';
            }
        } elseif ($request->filled('document_type') && $user->ktp_path) {
            // If document type is changed but file not re-uploaded, update document_type only
            $validated['document_type'] = $request->input('document_type');
        }

        // Handle selfie file upload
        if ($request->hasFile('selfie_file')) {
            // Delete old selfie if exists
            if ($user->selfie_path) {
                Storage::disk('private')->delete($user->selfie_path);
            }
            
            // Ensure kyc directory exists
            if (!Storage::disk('private')->exists('kyc/selfie')) {
                Storage::disk('private')->makeDirectory('kyc/selfie');
            }
            
            // Store in private disk
            $selfiePath = $request->file('selfie_file')->store('kyc/selfie', 'private');
            $validated['selfie_path'] = $selfiePath;
            
            // Reset verification status to pending if selfie is updated
            if ($user->verification_status === 'verified') {
                $validated['verification_status'] = 'pending';
            }
        }
        
        // Fill validated data
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Check if both KTP and selfie are now uploaded for the first time (after saving)
        if (!$hadKtpBefore && !$hadSelfieBefore && $user->ktp_path && $user->selfie_path) {
            $bothUploadedForFirstTime = true;
        }

        // Clear just_registered flag if profile is now complete
        if ($user->ktp_path && $user->selfie_path) {
            session()->forget('just_registered');
        }

        // Set verification status to pending if both KTP and selfie are uploaded
        if ($user->ktp_path && $user->selfie_path && !$user->verification_status) {
            $user->update(['verification_status' => 'pending']);
        }

        // Notify admin if both KTP and selfie are uploaded for the first time
        if ($bothUploadedForFirstTime && $user->ktp_path && $user->selfie_path) {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyAdminUserVerificationPending($user);
        }

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
