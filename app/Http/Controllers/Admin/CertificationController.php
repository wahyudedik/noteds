<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\UserCertification;
use App\Services\CertificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CertificationController extends Controller
{
    public function __construct(
        private CertificationService $certificationService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * List all certifications
     */
    public function index(): View
    {
        $certifications = Certification::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.certifications.index', [
            'certifications' => $certifications,
        ]);
    }

    /**
     * Show certification form
     */
    public function create(): View
    {
        return view('admin.certifications.create');
    }

    /**
     * Store new certification
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:certifications,slug',
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'requirements_text' => 'nullable|string',
            'benefits' => 'nullable|string',
            'requires_application' => 'boolean',
            'requires_approval' => 'boolean',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Convert requirements_text to array
        if ($request->has('requirements_text') && $request->requirements_text) {
            $requirements = array_filter(
                array_map('trim', explode("\n", $request->requirements_text))
            );
            $validated['requirements'] = array_values($requirements);
        }

        unset($validated['requirements_text']);

        Certification::create($validated);

        return redirect()->route('admin.certifications.index')
            ->with('success', 'Certification created successfully.');
    }

    /**
     * Show certification edit form
     */
    public function edit(Certification $certification): View
    {
        return view('admin.certifications.edit', [
            'certification' => $certification,
        ]);
    }

    /**
     * Update certification
     */
    public function update(Request $request, Certification $certification): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:certifications,slug,' . $certification->id,
            'description' => 'required|string',
            'category' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'requirements_text' => 'nullable|string',
            'benefits' => 'nullable|string',
            'requires_application' => 'boolean',
            'requires_approval' => 'boolean',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        // Convert requirements_text to array
        if ($request->has('requirements_text') && $request->requirements_text) {
            $requirements = array_filter(
                array_map('trim', explode("\n", $request->requirements_text))
            );
            $validated['requirements'] = array_values($requirements);
        }

        unset($validated['requirements_text']);

        $certification->update($validated);

        return redirect()->route('admin.certifications.index')
            ->with('success', 'Certification updated successfully.');
    }

    /**
     * Delete certification
     */
    public function destroy(Certification $certification): RedirectResponse
    {
        $certification->delete();

        return redirect()->route('admin.certifications.index')
            ->with('success', 'Certification deleted successfully.');
    }

    /**
     * List certification applications
     */
    public function applications(): View
    {
        $applications = UserCertification::with(['user', 'certification', 'approver'])
            ->where('status', 'pending')
            ->orderBy('applied_at', 'desc')
            ->paginate(20);

        return view('admin.certifications.applications', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show application details
     */
    public function showApplication(UserCertification $userCertification): View
    {
        $userCertification->load(['user', 'certification', 'approver']);

        return view('admin.certifications.application-show', [
            'application' => $userCertification,
        ]);
    }

    /**
     * Approve application
     */
    public function approveApplication(Request $request, UserCertification $userCertification): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $this->certificationService->approveCertification(
            $userCertification,
            auth()->user(),
            $validated['admin_notes'] ?? null,
            isset($validated['expires_at']) ? \Carbon\Carbon::parse($validated['expires_at']) : null
        );

        return redirect()->route('admin.certifications.applications')
            ->with('success', 'Certification application approved successfully.');
    }

    /**
     * Reject application
     */
    public function rejectApplication(Request $request, UserCertification $userCertification): RedirectResponse
    {
        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $this->certificationService->rejectCertification(
            $userCertification,
            auth()->user(),
            $validated['admin_notes']
        );

        return redirect()->route('admin.certifications.applications')
            ->with('success', 'Certification application rejected.');
    }
}

