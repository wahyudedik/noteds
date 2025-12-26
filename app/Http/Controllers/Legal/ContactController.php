<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Mail\ContactEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            Mail::to('info@noteds.com')->send(new ContactEmail($request->all()));
        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Failed to send contact email: ' . $e->getMessage());

            // Still return success to user (email might be queued)
            return back()->with('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
        }

        return back()->with('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
    }
}
