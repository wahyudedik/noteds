<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\WatermarkSetting;
use App\Models\DrmSetting;
use App\Models\DrmAccessLog;
use App\Models\DrmLicenseKey;
use App\Services\DrmService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ContentProtectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Show content protection settings for a note
     */
    public function show(Note $note): View
    {
        $note->load(['watermarkSetting', 'drmSetting', 'drmSetting.accessLogs', 'drmSetting.licenseKeys']);
        
        $watermarkSetting = $note->watermarkSetting ?? new WatermarkSetting(['note_id' => $note->id]);
        $drmSetting = $note->drmSetting ?? new DrmSetting(['note_id' => $note->id]);

        // Get DRM statistics
        $drmStats = [
            'total_access' => DrmAccessLog::where('note_id', $note->id)->count(),
            'unique_users' => DrmAccessLog::where('note_id', $note->id)->distinct('user_id')->count('user_id'),
            'unique_devices' => DrmAccessLog::where('note_id', $note->id)->distinct('device_id')->count('device_id'),
            'total_licenses' => DrmLicenseKey::where('note_id', $note->id)->count(),
            'active_licenses' => DrmLicenseKey::where('note_id', $note->id)->where('is_active', true)->count(),
        ];

        return view('admin.content-protection.show', compact('note', 'watermarkSetting', 'drmSetting', 'drmStats'));
    }

    /**
     * Update watermark settings
     */
    public function updateWatermark(Request $request, Note $note): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'type' => 'required|in:text,image,invisible',
            'text' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'text_size' => 'nullable|integer|min:8|max:200',
            'text_font' => 'nullable|string|max:255',
            'position' => 'required|in:top-left,top-right,center,bottom-left,bottom-right,tile',
            'opacity' => 'nullable|integer|min:0|max:100',
            'image_path' => 'nullable|string',
            'image_size' => 'nullable|integer|min:1|max:100',
            'margin' => 'nullable|integer|min:0|max:100',
            'apply_to_images' => 'boolean',
            'apply_to_pdfs' => 'boolean',
        ]);

        WatermarkSetting::updateOrCreate(
            ['note_id' => $note->id],
            $validated
        );

        return redirect()->route('admin.content-protection.show', $note)
            ->with('success', 'Watermark settings updated successfully.');
    }

    /**
     * Update DRM settings
     */
    public function updateDrm(Request $request, Note $note): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'encrypt_files' => 'boolean',
            'time_limited_access' => 'boolean',
            'access_duration_days' => 'nullable|integer|min:1|max:3650',
            'device_limit_enabled' => 'boolean',
            'max_devices' => 'nullable|integer|min:1|max:100',
            'license_key_enabled' => 'boolean',
            'license_key_type' => 'required|in:per_user,per_device,per_download',
        ]);

        DrmSetting::updateOrCreate(
            ['note_id' => $note->id],
            $validated
        );

        return redirect()->route('admin.content-protection.show', $note)
            ->with('success', 'DRM settings updated successfully.');
    }

    /**
     * Generate license keys for users
     */
    public function generateLicenseKeys(Request $request, Note $note): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|uuid|exists:users,id',
            'key_type' => 'required|in:per_user,per_device,per_download',
        ]);

        $drmService = app(DrmService::class);
        $generated = 0;

        foreach ($validated['user_ids'] as $userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                try {
                    $drmService->generateLicenseKey($note, $user, null, $validated['key_type']);
                    $generated++;
                } catch (\Exception $e) {
                    \Log::error('Failed to generate license key', [
                        'note_id' => $note->id,
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('admin.content-protection.show', $note)
            ->with('success', "Generated {$generated} license key(s).");
    }

    /**
     * View DRM access logs
     */
    public function accessLogs(Note $note, Request $request): View
    {
        $logs = DrmAccessLog::where('note_id', $note->id)
            ->with(['user', 'transaction'])
            ->latest()
            ->paginate(50);

        return view('admin.content-protection.access-logs', compact('note', 'logs'));
    }

    /**
     * View license keys
     */
    public function licenseKeys(Note $note, Request $request): View
    {
        $keys = DrmLicenseKey::where('note_id', $note->id)
            ->with(['user', 'transaction'])
            ->latest()
            ->paginate(50);

        return view('admin.content-protection.license-keys', compact('note', 'keys'));
    }
}

