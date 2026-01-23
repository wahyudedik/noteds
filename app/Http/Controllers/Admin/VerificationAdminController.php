<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserVerification;
use App\Models\VerificationRequest;
use App\Models\VerificationType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationAdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $types = VerificationType::orderBy('name')->get();
        $pending = VerificationRequest::where('status', 'pending')->with(['user','type'])->orderBy('submitted_at','asc')->get()->map(function ($r) {
            $svc = app(\App\Services\VerificationCriteriaService::class);
            $r->recommendation = $svc->evaluate($r);
            return $r;
        });
        $approved = VerificationRequest::where('status', 'approved')->with(['user','type'])->orderBy('reviewed_at','desc')->limit(50)->get();
        $rejected = VerificationRequest::where('status', 'rejected')->with(['user','type'])->orderBy('reviewed_at','desc')->limit(50)->get();
        return Inertia::render('Admin/VerificationDashboard', [
            'types' => $types,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ]);
    }

    public function approve(Request $request, VerificationRequest $verificationRequest)
    {
        $this->authorize('admin');
        $verificationRequest->status = 'approved';
        $verificationRequest->reviewer_id = $request->user()->id;
        $verificationRequest->review_note = $request->input('note');
        $verificationRequest->reviewed_at = now();
        $verificationRequest->save();

        UserVerification::firstOrCreate([
            'user_id' => $verificationRequest->user_id,
            'type_id' => $verificationRequest->type_id,
        ], [
            'verified_at' => now(),
        ]);

        \DB::table('verification_audits')->insert([
            'request_id' => $verificationRequest->id,
            'user_id' => $verificationRequest->user_id,
            'action' => 'approve',
            'meta' => json_encode(['note' => $request->input('note')]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $type = \App\Models\VerificationType::find($verificationRequest->type_id);
        $user = $verificationRequest->user;
        if ($type && $user) {
            $user->notify(new \App\Notifications\VerificationApprovedNotification($type));
        }

        return back()->with('success', 'Verification approved.');
    }

    public function reject(Request $request, VerificationRequest $verificationRequest)
    {
        $this->authorize('admin');
        $request->validate(['note' => 'required|string|min:5']);
        $verificationRequest->status = 'rejected';
        $verificationRequest->reviewer_id = $request->user()->id;
        $verificationRequest->review_note = $request->input('note');
        $verificationRequest->reviewed_at = now();
        $verificationRequest->save();

        \DB::table('verification_audits')->insert([
            'request_id' => $verificationRequest->id,
            'user_id' => $verificationRequest->user_id,
            'action' => 'reject',
            'meta' => json_encode(['note' => $request->input('note')]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $type = \App\Models\VerificationType::find($verificationRequest->type_id);
        $user = $verificationRequest->user;
        if ($type && $user) {
            $user->notify(new \App\Notifications\VerificationRejectedNotification($type, $request->input('note')));
        }

        return back()->with('success', 'Verification rejected.');
    }
}
