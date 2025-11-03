<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Display the contact form.
     */
    public function index(): View
    {
        return view('contact.index');
    }

    /**
     * Handle the contact form submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        try {
            // Log the contact message
            Log::info('Contact form submission', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ]);

            // Get support email from settings or use default
            $supportEmail = Setting::getSetting('support_email', 'general', config('mail.from.address', 'support@noteds.test'));

            // Send email to support
            try {
                Mail::to($supportEmail)->send(new ContactMail(
                    $validated['name'],
                    $validated['email'],
                    $validated['subject'],
                    $validated['message']
                ));
                
                Log::info('Contact email sent successfully', ['to' => $supportEmail]);
            } catch (\Exception $mailException) {
                // Log email error but don't fail the request
                Log::error('Failed to send contact email', [
                    'error' => $mailException->getMessage(),
                    'to' => $supportEmail,
                ]);
                // Continue - message is still logged
            }

            return redirect()->route('contact.index')
                ->with('success', 'Thank you for your message! We\'ll get back to you as soon as possible.');
        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data' => $validated,
            ]);

            return redirect()->route('contact.index')
                ->with('error', 'Sorry, there was an error sending your message. Please try again or email us directly at support@noteds.test');
        }
    }
}
