<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Services\CertificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CertificationController extends Controller
{
    public function __construct(
        private CertificationService $certificationService
    ) {
        $this->middleware('auth');
    }

    /**
     * Show available certifications
     */
    public function index(): View
    {
        $certifications = Certification::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $userCertifications = auth()->user()->userCertifications()
            ->with('certification')
            ->get()
            ->keyBy('certification_id');

        return view('certifications.index', [
            'certifications' => $certifications,
            'userCertifications' => $userCertifications,
        ]);
    }

    /**
     * Show certification details
     */
    public function show(Certification $certification): View
    {
        $userCertification = auth()->user()->userCertifications()
            ->where('certification_id', $certification->id)
            ->first();

        return view('certifications.show', [
            'certification' => $certification,
            'userCertification' => $userCertification,
        ]);
    }

    /**
     * Apply for certification
     */
    public function apply(Request $request, Certification $certification): RedirectResponse
    {
        $request->validate([
            'application_notes' => 'nullable|string|max:1000',
            'evidence' => 'nullable|array',
            'evidence.*' => 'url',
        ]);

        try {
            $this->certificationService->applyForCertification(
                auth()->user(),
                $certification,
                $request->input('application_notes'),
                $request->input('evidence')
            );

            return redirect()->route('certifications.show', $certification)
                ->with('success', 'Your certification application has been submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

