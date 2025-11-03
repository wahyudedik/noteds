<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Tag;
use App\Services\NoteActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $notes = auth()->user()->notes()->with(['tags', 'reviews'])->latest()->paginate(15);

        return view('notes.index', compact('notes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $tags = Tag::orderBy('name')->get();
        $user = auth()->user();
        
        // Get folders and workspaces for premium users
        $folders = [];
        $workspaces = [];
        
        if ($user->hasPremium()) {
            $folders = $user->allFolders()->get();
            $workspaces = $user->allWorkspaces();
        }

        return view('notes.create', compact('tags', 'folders', 'workspaces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        // Check if user can create more notes
        if (!auth()->user()->canCreateMoreNotes()) {
            $limit = auth()->user()->getNoteCreationLimit();
            return redirect()->route('notes.create')
                ->with('error', "Note creation limit reached! You can only create {$limit} notes on the Basic plan. Upgrade to Premium for unlimited notes.");
        }

        // Check file sizes before validation (for better error handling)
        $user = auth()->user();
        if ($request->hasFile('attachments') && !$user->hasPremium()) {
            $maxSize = 5242880; // 5MB
            $largeFiles = [];
            
            foreach ($request->file('attachments') as $file) {
                if ($file->getSize() > $maxSize) {
                    $sizeInMB = round($file->getSize() / 1048576, 2);
                    $largeFiles[] = $file->getClientOriginalName() . ' (' . $sizeInMB . 'MB)';
                }
            }
            
            if (!empty($largeFiles)) {
                return redirect()->route('notes.create')
                    ->withInput()
                    ->withErrors([
                        'attachments' => 'File size exceeds 5MB limit: ' . implode(', ', $largeFiles) . '. Please upgrade to Premium to upload larger files (up to 50MB).'
                    ])
                    ->with('upgrade_message', 'Upgrade to Premium to upload files up to 50MB per file.');
            }
        }

        $validated = $request->validated();
        $validated['user_id'] = auth()->id();
        $validated['price'] = $validated['price'] ?? 0;
        $validated['is_public'] = $request->has('is_public');
        $validated['status'] = $validated['status'] ?? 'active';

        // Handle preview content - auto-generate if not provided
        if (empty($validated['preview_content'])) {
            $content = strip_tags($validated['content']);
            $validated['preview_content'] = Str::limit($content, 300);
        }

        // Handle file uploads
        $attachments = $this->handleFileUploads($request);
        $validated['attachments'] = $attachments;
        $validated['file_count'] = count($attachments);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $note = Note::create($validated);
        $this->syncTags($note, $tags);

        // Log activity
        app(NoteActivityService::class)->logCreated($note, auth()->user());

        return redirect()->route('notes.index')->with('success', 'Note created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): View
    {
        $this->authorize('view', $note);

        $note->load('tags', 'user', 'reviews');

        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note): View
    {
        $this->authorize('update', $note);

        $tags = Tag::orderBy('name')->get();
        $note->load('tags', 'folder', 'workspace');
        $user = auth()->user();
        
        // Get folders and workspaces for premium users
        $folders = [];
        $workspaces = [];
        
        if ($user->hasPremium()) {
            $folders = $user->allFolders()->get();
            $workspaces = $user->allWorkspaces();
        }

        return view('notes.edit', compact('note', 'tags', 'folders', 'workspaces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        // Check file sizes before validation (for better error handling)
        $user = auth()->user();
        if ($request->hasFile('attachments') && !$user->hasPremium()) {
            $maxSize = 5242880; // 5MB
            $largeFiles = [];
            
            foreach ($request->file('attachments') as $file) {
                if ($file->getSize() > $maxSize) {
                    $sizeInMB = round($file->getSize() / 1048576, 2);
                    $largeFiles[] = $file->getClientOriginalName() . ' (' . $sizeInMB . 'MB)';
                }
            }
            
            if (!empty($largeFiles)) {
                return redirect()->route('notes.edit', $note)
                    ->withInput()
                    ->withErrors([
                        'attachments' => 'File size exceeds 5MB limit: ' . implode(', ', $largeFiles) . '. Please upgrade to Premium to upload larger files (up to 50MB).'
                    ])
                    ->with('upgrade_message', 'Upgrade to Premium to upload files up to 50MB per file.');
            }
        }

        $validated = $request->validated();
        $validated['is_public'] = $request->has('is_public');

        // Handle preview content - auto-generate if not provided
        if (empty($validated['preview_content'])) {
            $content = strip_tags($validated['content']);
            $validated['preview_content'] = Str::limit($content, 300);
        }

        // Handle file uploads (merge with existing)
        $newAttachments = $this->handleFileUploads($request);
        $existingAttachments = $note->attachments ?? [];
        
        // Keep existing attachments unless explicitly removed
        $removedAttachments = $request->input('removed_attachments', []);
        $existingAttachments = array_filter($existingAttachments, function($attachment) use ($removedAttachments) {
            $filename = is_array($attachment) ? ($attachment['filename'] ?? '') : basename($attachment);
            return !in_array($filename, $removedAttachments);
        });

        // Merge new and existing attachments
        $validated['attachments'] = array_merge(array_values($existingAttachments), $newAttachments);
        $validated['file_count'] = count($validated['attachments']);

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Track changes for activity log
        $oldData = $note->only(['title', 'content', 'summary', 'price', 'is_public', 'status']);
        $oldTags = $note->tags->pluck('name')->toArray();

        $note->update($validated);
        $newTags = $this->syncTags($note, $tags);

        // Log activity
        $activityService = app(NoteActivityService::class);
        $activityService->logUpdated($note, auth()->user(), $oldData, $note->fresh()->only(['title', 'content', 'summary', 'price', 'is_public', 'status']));
        
        // Log tag changes if any
        $addedTags = array_diff($newTags, $oldTags);
        $removedTags = array_diff($oldTags, $newTags);
        if (!empty($addedTags) || !empty($removedTags)) {
            $activityService->logTagged($note, auth()->user(), $addedTags, $removedTags);
        }

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }

    /**
     * Sync tags for a note.
     * @return array Array of tag names that were synced
     */
    protected function syncTags(Note $note, array $tagNames): array
    {
        $tagIds = [];
        $syncedTagNames = [];

        foreach ($tagNames as $tagName) {
            if (empty(trim($tagName))) {
                continue;
            }

            $tag = Tag::firstOrCreate(
                ['name' => trim($tagName)],
                ['slug' => Str::slug(trim($tagName))]
            );

            $tagIds[] = $tag->id;
            $syncedTagNames[] = $tag->name;
        }

        $note->tags()->sync($tagIds);

        return $syncedTagNames;
    }

    /**
     * Handle file uploads for notes
     */
    protected function handleFileUploads($request): array
    {
        if (!$request->hasFile('attachments')) {
            return [];
        }

        $files = $request->file('attachments');
        $attachments = [];

        // Ensure private storage directory exists
        if (!Storage::disk('private')->exists('notes')) {
            Storage::disk('private')->makeDirectory('notes');
        }

        foreach ($files as $file) {
            // Validate MIME type
            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain',
                'application/zip',
                'application/x-rar-compressed',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ];

            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedMimes)) {
                continue; // Skip invalid files
            }

            // Generate unique filename
            $filename = Str::uuid() . '_' . Str::slug($file->getClientOriginalName());
            $path = $file->storeAs('notes/' . auth()->id(), $filename, 'private');

            $attachments[] = [
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
                'mime' => $mimeType,
            ];
        }

        return $attachments;
    }
}
