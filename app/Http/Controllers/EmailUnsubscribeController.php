<?php

namespace App\Http\Controllers;

use App\Models\EmailUnsubscribe;
use App\Models\UserEmailPreference;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmailUnsubscribeController extends Controller
{
    /**
     * Show unsubscribe form
     */
    public function show(string $token): View
    {
        $unsubscribe = EmailUnsubscribe::where('token', $token)->first();
        
        if (!$unsubscribe) {
            abort(404, 'Unsubscribe link not found');
        }
        
        return view('40-shared/emails/unsubscribe', [
            'unsubscribe' => $unsubscribe,
        ]);
    }

    /**
     * Process unsubscribe
     */
    public function unsubscribe(Request $request, string $token): RedirectResponse
    {
        $unsubscribe = EmailUnsubscribe::where('token', $token)->first();
        
        if (!$unsubscribe) {
            return redirect()->route('marketplace.index')
                ->with('error', 'Invalid unsubscribe link');
        }
        
        // Update unsubscribe record with reason and feedback
        $unsubscribe->update([
            'reason' => $request->input('reason'),
            'feedback' => $request->input('feedback'),
        ]);
        
        // Update user email preferences if user exists
        if ($unsubscribe->user_id) {
            UserEmailPreference::updateOrCreate(
                ['user_id' => $unsubscribe->user_id],
                [
                    'new_note_notifications' => false,
                    'weekly_digest' => false,
                    'abandoned_cart_emails' => false,
                    'marketing_emails' => false,
                    'sequence_emails' => false,
                ]
            );
        }
        
        return redirect()->route('marketplace.index')
            ->with('success', 'You have been successfully unsubscribed from marketing emails.');
    }

    /**
     * Unsubscribe by email (for users)
     */
    public function unsubscribeByEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $email = $request->input('email');
        
        // Check if already unsubscribed
        if (EmailUnsubscribe::isUnsubscribed($email)) {
            return back()->with('info', 'This email is already unsubscribed.');
        }
        
        $user = \App\Models\User::where('email', $email)->first();
        
        EmailUnsubscribe::unsubscribe(
            $email,
            $request->input('reason'),
            $request->input('feedback'),
            $user?->id
        );
        
        // Update user preferences if user exists
        if ($user) {
            UserEmailPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'new_note_notifications' => false,
                    'weekly_digest' => false,
                    'abandoned_cart_emails' => false,
                    'marketing_emails' => false,
                    'sequence_emails' => false,
                ]
            );
        }
        
        return back()->with('success', 'You have been successfully unsubscribed from marketing emails.');
    }
}

