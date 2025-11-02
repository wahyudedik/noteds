<?php

namespace App\Http\Controllers;

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
            // For now, just log the contact message
            // In production, you can add email sending functionality
            Log::info('Contact form submission', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
            ]);

            // TODO: Send email to support@noteds.test
            // Mail::to('support@noteds.test')->send(new ContactMail($validated));

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
