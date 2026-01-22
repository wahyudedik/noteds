<?php

namespace App\Http\Controllers;

use App\Models\GroupInvite;
use Illuminate\Http\Request;

class EmailTrackingController extends Controller
{
    public function open(Request $request, GroupInvite $invite)
    {
        $invite->increment('open_count');
        $invite->last_opened_at = now();
        $invite->save();

        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8Xw8AAt8BboHkYqYAAAAASUVORK5CYII=');
        return response($pixel, 200)->header('Content-Type', 'image/png');
    }

    public function click(Request $request, GroupInvite $invite)
    {
        $invite->increment('click_count');
        $invite->last_clicked_at = now();
        $invite->save();
        return redirect()->back();
    }
}
