<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaignRecipient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    /**
     * Track email open (via tracking pixel)
     */
    public function trackOpen(string $token): Response
    {
        $recipient = EmailCampaignRecipient::where('tracking_token', $token)->first();
        
        if ($recipient) {
            $recipient->trackOpen();
        }
        
        // Return 1x1 transparent pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Track email click
     */
    public function trackClick(string $token, Request $request)
    {
        $recipient = EmailCampaignRecipient::where('tracking_token', $token)->first();
        
        $url = $request->get('url');
        
        if ($recipient && $url) {
            $recipient->trackClick($url);
        }
        
        // Redirect to actual URL
        return redirect($url ?? route('marketplace.index'));
    }
}

