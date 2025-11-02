<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocialMediaLinkRequest;
use App\Http\Requests\UpdateSocialMediaLinkRequest;
use App\Models\SocialMediaLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SocialMediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $socialMediaLinks = SocialMediaLink::ordered()->latest()->paginate(20);

        return view('admin.social-media.index', compact('socialMediaLinks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter/X',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'discord' => 'Discord',
            'github' => 'GitHub',
            'custom' => 'Custom',
        ];

        return view('admin.social-media.create', compact('platforms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSocialMediaLinkRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        SocialMediaLink::create($validated);

        return redirect()->route('admin.social-media.index')
            ->with('success', 'Social media link created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SocialMediaLink $socialMedia): View
    {
        $platforms = [
            'facebook' => 'Facebook',
            'twitter' => 'Twitter/X',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'whatsapp' => 'WhatsApp',
            'telegram' => 'Telegram',
            'discord' => 'Discord',
            'github' => 'GitHub',
            'custom' => 'Custom',
        ];

        return view('admin.social-media.edit', compact('socialMedia', 'platforms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSocialMediaLinkRequest $request, SocialMediaLink $socialMedia): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->has('is_active');

        $socialMedia->update($validated);

        return redirect()->route('admin.social-media.index')
            ->with('success', 'Social media link updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SocialMediaLink $socialMedia): RedirectResponse
    {
        $socialMedia->delete();

        return redirect()->route('admin.social-media.index')
            ->with('success', 'Social media link deleted successfully.');
    }
}
