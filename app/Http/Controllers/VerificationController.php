<?php

namespace App\Http\Controllers;

use App\Models\VerificationRequest;
use App\Models\VerificationType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function requestPage(Request $request)
    {
        $types = VerificationType::where('enabled', true)->orderBy('name')->get();
        $requests = VerificationRequest::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->get();
        return Inertia::render('Verification/Request', [
            'types' => $types,
            'requests' => $requests,
        ]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'type_id' => 'required|exists:verification_types,id',
            'form' => 'nullable|array',
            'documents' => 'nullable|array',
        ]);
        $vr = new VerificationRequest([
            'user_id' => $request->user()->id,
            'type_id' => $data['type_id'],
            'status' => 'pending',
            'data' => $data['form'] ?? [],
            'documents' => $data['documents'] ?? [],
            'submitted_at' => now(),
        ]);
        $vr->save();
        \DB::table('verification_audits')->insert([
            'request_id' => $vr->id,
            'user_id' => $request->user()->id,
            'action' => 'submit',
            'meta' => json_encode(['type_id' => $data['type_id']]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return redirect()->route('verification.request')->with('success', 'Verification request submitted.');
    }
}
