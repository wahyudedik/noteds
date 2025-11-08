<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Models\Tag;
use App\Models\WorkspaceActivityLog;
use App\Services\NoteActivityService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function create(Request $request): View
    {
        $user = auth()->user();
        
        // Check if user is seller or workspace user (middleware already checks, but double check for security)
        if (!in_array($user->role, ['seller', 'user_workspaces']) && !$user->hasRole('admin')) {
            abort(403, 'Fitur ini hanya tersedia untuk Seller atau Workspace User. Buyer tidak dapat membuat note. Jika ingin membuat note, silakan buat akun Seller dengan email berbeda atau bergabung dengan workspace.');
        }
        
        $tags = Tag::orderBy('name')->get();
        
        // Get folders and workspaces for premium users
        $folders = [];
        $workspaces = [];
        $selectedWorkspace = null;
        $selectedFolder = null;
        
        if ($user->hasPremium()) {
            $folders = $user->allFolders()->get();
            $workspaces = $user->allWorkspaces();
            
            // Pre-select workspace if specified
            if ($request->has('workspace_id')) {
                $selectedWorkspace = \App\Models\Workspace::where('id', $request->workspace_id)
                    ->where(function($q) use ($user) {
                        $q->where('owner_id', $user->id)
                          ->orWhereHas('members', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                    })
                    ->first();
            }
            
            // Pre-select folder if specified
            if ($request->has('folder_id')) {
                $selectedFolder = \App\Models\Folder::where('id', $request->folder_id)
                    ->where('user_id', $user->id)
                    ->first();
                
                // If folder has workspace, use it
                if ($selectedFolder && $selectedFolder->workspace_id && !$selectedWorkspace) {
                    $selectedWorkspace = $selectedFolder->workspace;
                }
            }
        }

        return view('notes.create', compact('tags', 'folders', 'workspaces', 'selectedWorkspace', 'selectedFolder'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $user = auth()->user();
        
        // Check if user is seller or workspace user (middleware already checks, but double check for security)
        if (!in_array($user->role, ['seller', 'user_workspaces']) && !$user->hasRole('admin')) {
            return redirect()->route('dashboard')->with('error', 'Fitur ini hanya tersedia untuk Seller atau Workspace User. Buyer tidak dapat membuat note. Jika ingin membuat note, silakan buat akun Seller dengan email berbeda atau bergabung dengan workspace.');
        }
        
        // Check if user can create more notes
        if (!$user->canCreateMoreNotes()) {
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
        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['original_creator_id'] = $user->id; // Set original creator on creation
        $validated['price'] = $validated['price'] ?? 0;
        $validated['is_public'] = $request->has('is_public');
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['is_sold'] = false; // New notes are not sold yet
        
        // Handle workspace and folder
        $workspace = null;
        $folder = null;
        
        if (!empty($validated['workspace_id'])) {
            $workspace = \App\Models\Workspace::where('id', $validated['workspace_id'])
                ->where(function($q) use ($user) {
                    $q->where('owner_id', $user->id)
                      ->orWhereHas('members', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
                })
                ->first();
            
            if ($workspace) {
                $validated['workspace_id'] = $workspace->id;
            } else {
                unset($validated['workspace_id']);
            }
        }
        
        if (!empty($validated['folder_id'])) {
            $folder = \App\Models\Folder::where('id', $validated['folder_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if ($folder) {
                $validated['folder_id'] = $folder->id;
                // If folder has workspace, use it
                if ($folder->workspace_id && !$workspace) {
                    $validated['workspace_id'] = $folder->workspace_id;
                    $workspace = $folder->workspace;
                }
            } else {
                unset($validated['folder_id']);
            }
        }

        // Handle preview content - auto-generate if not provided
        if (empty($validated['preview_content'])) {
            $content = strip_tags($validated['content']);
            $validated['preview_content'] = Str::limit($content, 300);
        }

        // Handle file uploads
        $attachments = $this->handleFileUploads($request);
        $validated['attachments'] = $attachments;
        $validated['file_count'] = count($attachments);

        // Handle thumbnail uploads
        $thumbnails = $this->handleThumbnailUploads($request);
        $validated['thumbnails'] = $thumbnails;

        // Set default preview_percentage if not provided
        if (!isset($validated['preview_percentage'])) {
            $validated['preview_percentage'] = 0;
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        $note = Note::create($validated);
        $this->syncTags($note, $tags);

        if ($workspace) {
            WorkspaceActivityLog::record($workspace, 'note_added', $user, [
                'note_id' => $note->id,
                'note_title' => $note->title,
                'folder_id' => $folder?->id,
            ]);
        }

        // Create note history record
        \App\Models\NoteHistory::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'old_data' => null,
            'new_data' => $note->only(['title', 'content', 'summary', 'price', 'discount_price', 'is_public', 'status']),
            'changes' => 'Note created',
            'notes' => 'Note created by ' . auth()->user()->name,
        ]);

        // Log activity
        app(NoteActivityService::class)->logCreated($note, auth()->user());

        if ($note->is_public && $note->status === 'active' && !$note->notificationMeta('published_notified_at')) {
            app(NotificationService::class)->notifyNewNotePublished($note);
            $note->setNotificationMetaValue('published_notified_at', now()->toIso8601String());
        }

        // Redirect based on context
        if ($workspace) {
            $redirectParams = ['workspace' => $workspace->id];
            if ($folder) {
                $redirectParams['folder'] = $folder->id;
            }
            return redirect()->route('workspaces.show', $redirectParams)
                ->with('success', __('messages.note_created_successfully'));
        } elseif ($folder) {
            return redirect()->route('folders.show', $folder)
                ->with('success', __('messages.note_created_successfully'));
        }
        
        return redirect()->route('notes.index')
            ->with('success', __('messages.note_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): View
    {
        $this->authorize('view', $note);

        $note->load('tags', 'user', 'reviews', 'histories.user', 'transactions.buyer', 'transactions.seller', 'transactions.originalCreator');
        
        // Get buyer history (all successful transactions) - visible to original creator
        $buyerHistory = collect();
        if (auth()->check() && ($note->original_creator_id === auth()->id() || $note->user_id === auth()->id())) {
            $buyerHistory = $note->transactions()
                ->where('status', 'success')
                ->with(['buyer', 'seller', 'originalCreator'])
                ->orderBy('created_at', 'asc')
                ->get();
        }
        
        // Get update history (all history records except 'sold') - visible to original creator
        $updateHistory = collect();
        if (auth()->check() && ($note->original_creator_id === auth()->id() || $note->user_id === auth()->id())) {
            $updateHistory = $note->histories()
                ->where('action', '!=', 'sold')
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        // Check if note has been sold (cannot delete)
        $hasTransactions = $note->transactions()
            ->where('status', 'success')
            ->exists();

        return view('notes.show', compact('note', 'buyerHistory', 'updateHistory', 'hasTransactions'));
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

        // Handle thumbnail uploads (merge with existing)
        $existingThumbnails = $note->thumbnails ?? [];
        $newThumbnails = $this->handleThumbnailUploads($request);
        $removedThumbnails = $request->input('removed_thumbnails', []);
        
        // Remove deleted thumbnails
        $existingThumbnails = array_filter($existingThumbnails, function($thumbnail) use ($removedThumbnails) {
            return !in_array($thumbnail, $removedThumbnails);
        });
        
        // Merge new and existing thumbnails
        $validated['thumbnails'] = array_merge(array_values($existingThumbnails), $newThumbnails);
        
        // Limit to 5 thumbnails
        if (count($validated['thumbnails']) > 5) {
            $validated['thumbnails'] = array_slice($validated['thumbnails'], 0, 5);
        }

        // Set default preview_percentage if not provided
        if (!isset($validated['preview_percentage'])) {
            $validated['preview_percentage'] = $note->preview_percentage ?? 0;
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Track changes for activity log and history
        $oldData = $note->only(['title', 'content', 'summary', 'price', 'discount_price', 'is_public', 'status', 'preview_content', 'preview_percentage']);
        $oldTags = $note->tags->pluck('name')->toArray();

        $note->update($validated);
        $newTags = $this->syncTags($note, $tags);
        
        $newData = $note->fresh()->only(['title', 'content', 'summary', 'price', 'discount_price', 'is_public', 'status', 'preview_content', 'preview_percentage']);

        // Create note history record for versioning
        $changes = [];
        foreach ($oldData as $key => $oldValue) {
            $newValue = $newData[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[] = ucfirst(str_replace('_', ' ', $key)) . ': "' . (is_string($oldValue) ? Str::limit($oldValue, 50) : $oldValue) . '" → "' . (is_string($newValue) ? Str::limit($newValue, 50) : $newValue) . '"';
            }
        }
        
        // Tag changes
        $addedTags = array_diff($newTags, $oldTags);
        $removedTags = array_diff($oldTags, $newTags);
        if (!empty($addedTags)) {
            $changes[] = 'Tags added: ' . implode(', ', $addedTags);
        }
        if (!empty($removedTags)) {
            $changes[] = 'Tags removed: ' . implode(', ', $removedTags);
        }
        
        // Create history record
        \App\Models\NoteHistory::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'old_data' => $oldData,
            'new_data' => $newData,
            'changes' => !empty($changes) ? implode('; ', $changes) : 'No significant changes',
            'notes' => 'Note updated by ' . auth()->user()->name,
        ]);

        // Log activity
        $activityService = app(NoteActivityService::class);
        $activityService->logUpdated($note, auth()->user(), $oldData, $newData);
        
        // Log tag changes if any
        if (!empty($addedTags) || !empty($removedTags)) {
            $activityService->logTagged($note, auth()->user(), $addedTags, $removedTags);
        }

        $wasPublicActive = ($oldData['is_public'] ?? false) && (($oldData['status'] ?? '') === 'active');
        $nowPublicActive = $note->is_public && $note->status === 'active';

        if ($nowPublicActive && (!$wasPublicActive || !$note->notificationMeta('published_notified_at'))) {
            app(NotificationService::class)->notifyNewNotePublished($note);
            $note->setNotificationMetaValue('published_notified_at', now()->toIso8601String());
        }

        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        // Check if note has been sold (has any successful transactions)
        $hasTransactions = $note->transactions()
            ->where('status', 'success')
            ->exists();
        
        if ($hasTransactions) {
            return redirect()->route('notes.show', $note)
                ->with('error', 'Cannot delete note that has been sold. Note has been purchased by buyers and cannot be deleted. You can still update it, but deletion is not allowed.');
        }

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

    /**
     * Handle thumbnail uploads for notes
     */
    protected function handleThumbnailUploads(Request $request): array
    {
        if (!$request->hasFile('thumbnails')) {
            return [];
        }

        $files = $request->file('thumbnails');
        $thumbnails = [];

        // Ensure public storage directory exists
        if (!Storage::disk('public')->exists('thumbnails')) {
            Storage::disk('public')->makeDirectory('thumbnails');
        }

        foreach ($files as $file) {
            // Validate it's an image
            if (!$file->isValid() || !str_starts_with($file->getMimeType(), 'image/')) {
                continue; // Skip invalid files
            }

            // Generate unique filename
            $filename = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('thumbnails/' . auth()->id(), $filename, 'public');

            $thumbnails[] = $path;
        }

        return $thumbnails;
    }
}
