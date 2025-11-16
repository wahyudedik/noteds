<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Requests\ResaleNoteRequest;
use App\Models\Note;
use App\Models\PurchasedNote;
use App\Models\Tag;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkspaceActivityLog;
use App\Services\NoteActivityService;
use App\Services\NotificationService;
use App\Services\LargeFileUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

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

        $defaultMinPrice = Setting::getDefaultMinPrice();
        $priceGuidance = [
            'min_default' => $defaultMinPrice,
            'recommended_multiplier' => Setting::getRecommendedPriceMultiplier(),
            'recommended_price' => $defaultMinPrice > 0
                ? round($defaultMinPrice * Setting::getRecommendedPriceMultiplier())
                : null,
            'category_rules' => Setting::getCategoryMinPriceList(),
        ];

        $ecosystems = [
            '' => '— Tidak ditentukan —',
            'elements' => 'Elements (Unlimited)',
            'audiojungle' => 'AudioJungle',
            'codecanyon' => 'CodeCanyon',
            'graphicriver' => 'GraphicRiver',
            'photodune' => 'PhotoDune',
            'themeforest' => 'Themeforest',
            'videohive' => 'VideoHive',
            '3docean' => '3DOcean',
        ];

        return view('notes.create', compact('tags', 'folders', 'workspaces', 'selectedWorkspace', 'selectedFolder', 'priceGuidance', 'ecosystems'));
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

        // Validate and handle file uploads (including large files)
        $user = auth()->user();
        $uploadService = app(LargeFileUploadService::class);
        
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $validationErrors = [];
            $largeFilesWarning = [];
            
            foreach ($files as $index => $file) {
                $validation = $uploadService->validateFile($file, $user->hasPremium());
                
                if (!$validation['valid']) {
                    $validationErrors["attachments.{$index}"] = $validation['error'];
                } elseif (isset($validation['is_large']) && $validation['is_large']) {
                    $sizeInMB = round($file->getSize() / 1048576, 2);
                    $largeFilesWarning[] = $file->getClientOriginalName() . ' (' . $sizeInMB . 'MB)';
                }
            }
            
            if (!empty($validationErrors)) {
                return redirect()->route('notes.create')
                    ->withInput()
                    ->withErrors($validationErrors)
                    ->with('upgrade_message', !$user->hasPremium() ? 'Upgrade to Premium to upload files up to 100MB per file.' : null);
            }
            
            // Store large files warning in session for frontend display
            if (!empty($largeFilesWarning)) {
                session()->flash('large_files_warning', $largeFilesWarning);
            }
        }

        $validated = $request->validated();
        $validated['ecosystem_category'] = $request->input('ecosystem_category') ?: null;
        $validated['language'] = $request->input('language') ?: null;
        $validated['scheduled_publish_at'] = $request->input('scheduled_publish_at') ?: null;
        $user = auth()->user();
        $validated['user_id'] = $user->id;
        $validated['original_creator_id'] = $user->id; // Set original creator on creation
        $validated['price'] = $validated['price'] ?? 0;
        $validated['is_public'] = $request->has('is_public');
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['is_sold'] = false; // New notes are not sold yet
        $validated['sale_mode'] = $validated['sale_mode'] ?? 'scarcity';
        $validated['grace_period_days'] = $validated['grace_period_days'] ?? 30;
        $validated['relist_price_multiplier'] = $validated['relist_price_multiplier'] ?? 1.5;

        $contentHash = $this->generateContentHash($validated['content'] ?? '');
        if ($duplicate = $this->detectUnauthorizedResale($contentHash, $user)) {
            return redirect()->route('notes.create')
                ->withInput()
                ->withErrors([
                    'content' => __('messages.note_content_duplicate', [
                        'title' => $duplicate->title,
                        'seller' => optional($duplicate->originalCreator)->name ?? optional($duplicate->user)->name ?? __('messages.another_seller'),
                    ]),
                ]);
        }
        $validated['content_hash'] = $contentHash;
        
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

        // Handle file uploads (with large file support)
        $attachments = $this->handleFileUploadsWithProgress($request, $user);
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

        $defaultMinPrice = Setting::getDefaultMinPrice();
        $priceGuidance = [
            'min_default' => $defaultMinPrice,
            'recommended_multiplier' => Setting::getRecommendedPriceMultiplier(),
            'recommended_price' => $defaultMinPrice > 0
                ? round($defaultMinPrice * Setting::getRecommendedPriceMultiplier())
                : null,
            'category_rules' => Setting::getCategoryMinPriceList(),
        ];

        $ecosystems = [
            '' => '— Tidak ditentukan —',
            'elements' => 'Elements (Unlimited)',
            'audiojungle' => 'AudioJungle',
            'codecanyon' => 'CodeCanyon',
            'graphicriver' => 'GraphicRiver',
            'photodune' => 'PhotoDune',
            'themeforest' => 'Themeforest',
            'videohive' => 'VideoHive',
            '3docean' => '3DOcean',
        ];

        $languages = [
            '' => '— Language —',
            'en' => 'English',
            'id' => 'Bahasa Indonesia',
            'ar' => 'العربية',
        ];

        return view('notes.edit', compact('note', 'tags', 'folders', 'workspaces', 'priceGuidance', 'ecosystems', 'languages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $user = auth()->user();
        
        // Check if note has been sold - prevent changing certain fields
        $hasTransactions = $note->transactions()
            ->where('status', 'success')
            ->exists();
        
        if ($hasTransactions) {
            // If note has been sold, prevent changing sale_mode and price-related fields
            // These fields should remain unchanged to maintain transaction integrity
            $request->merge([
                'sale_mode' => $note->sale_mode,
                'grace_period_days' => $note->grace_period_days,
                'relist_price_multiplier' => $note->relist_price_multiplier,
            ]);
        }

        // Validate and handle file uploads (including large files)
        $uploadService = app(LargeFileUploadService::class);
        
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $validationErrors = [];
            $largeFilesWarning = [];
            
            foreach ($files as $index => $file) {
                $validation = $uploadService->validateFile($file, $user->hasPremium());
                
                if (!$validation['valid']) {
                    $validationErrors["attachments.{$index}"] = $validation['error'];
                } elseif (isset($validation['is_large']) && $validation['is_large']) {
                    $sizeInMB = round($file->getSize() / 1048576, 2);
                    $largeFilesWarning[] = $file->getClientOriginalName() . ' (' . $sizeInMB . 'MB)';
                }
            }
            
            if (!empty($validationErrors)) {
                return redirect()->route('notes.edit', $note)
                    ->withInput()
                    ->withErrors($validationErrors)
                    ->with('upgrade_message', !$user->hasPremium() ? 'Upgrade to Premium to upload files up to 100MB per file.' : null);
            }
            
            // Store large files warning in session for frontend display
            if (!empty($largeFilesWarning)) {
                session()->flash('large_files_warning', $largeFilesWarning);
            }
        }

        $validated = $request->validated();
        if ($request->has('ecosystem_category')) {
            $validated['ecosystem_category'] = $request->input('ecosystem_category') ?: null;
        }
        $validated['is_public'] = $request->has('is_public');

        // Handle workspace and folder
        $workspace = null;
        $folder = null;
        
        // Handle folder first (because folder can determine workspace)
        if ($request->has('folder_id')) {
            $folderId = $request->input('folder_id');
            if (!empty($folderId)) {
                $folder = \App\Models\Folder::where('id', $folderId)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($folder) {
                    $validated['folder_id'] = $folder->id;
                    // If folder has workspace, use it
                    if ($folder->workspace_id) {
                        $workspace = $folder->workspace;
                        $validated['workspace_id'] = $workspace->id;
                    }
                } else {
                    // Invalid folder, remove it
                    $validated['folder_id'] = null;
                }
            } else {
                // Empty string means remove folder
                $validated['folder_id'] = null;
            }
        }
        // If folder_id not in request, keep existing (don't add to validated, will not be updated)
        
        // Handle workspace (can be set independently or by folder)
        if ($request->has('workspace_id')) {
            $workspaceId = $request->input('workspace_id');
            if (!empty($workspaceId)) {
                // Only process if not already set by folder
                if (!isset($validated['workspace_id'])) {
                    $workspace = \App\Models\Workspace::where('id', $workspaceId)
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
                        // Invalid workspace, but don't change if folder has workspace
                        if (!($folder && $folder->workspace_id)) {
                            $validated['workspace_id'] = null;
                        }
                    }
                }
            } else {
                // Empty string means remove workspace (only if folder doesn't require it)
                if (!($folder && $folder->workspace_id)) {
                    $validated['workspace_id'] = null;
                }
            }
        }
        // If workspace_id not in request, keep existing (don't add to validated, will not be updated)
        
        // Load workspace and folder for activity log and redirect
        if (isset($validated['workspace_id'])) {
            $workspace = $workspace ?? \App\Models\Workspace::find($validated['workspace_id']);
        } else if ($note->workspace_id) {
            $workspace = $note->workspace;
        }
        
        if (isset($validated['folder_id'])) {
            $folder = $folder ?? ($validated['folder_id'] ? \App\Models\Folder::find($validated['folder_id']) : null);
        } else if ($note->folder_id) {
            $folder = $note->folder;
        }

        // Handle preview content - auto-generate if not provided
        if (empty($validated['preview_content'])) {
            $content = strip_tags($validated['content']);
            $validated['preview_content'] = Str::limit($content, 300);
        }

        $contentHash = $this->generateContentHash($validated['content'] ?? '');
        if ($contentHash !== $note->content_hash) {
            if ($duplicate = $this->detectUnauthorizedResale($contentHash, $user, $note->id)) {
                return redirect()->route('notes.edit', $note)
                    ->withInput()
                    ->withErrors([
                        'content' => __('messages.note_content_duplicate', [
                            'title' => $duplicate->title,
                            'seller' => optional($duplicate->originalCreator)->name ?? optional($duplicate->user)->name ?? __('messages.another_seller'),
                        ]),
                    ]);
            }
            $validated['content_hash'] = $contentHash;
        }

        // Handle file uploads (merge with existing, with large file support)
        $newAttachments = $this->handleFileUploadsWithProgress($request, $user);
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

        // Log workspace activity if workspace changed
        if ($workspace) {
            WorkspaceActivityLog::record($workspace, 'note_updated', $user, [
                'note_id' => $note->id,
                'note_title' => $note->title,
                'folder_id' => $folder?->id,
            ]);
        }

        // Redirect based on context
        if ($workspace) {
            $redirectParams = ['workspace' => $workspace->id];
            if ($folder) {
                $redirectParams['folder'] = $folder->id;
            }
            return redirect()->route('workspaces.show', $redirectParams)
                ->with('success', 'Note updated successfully.');
        } elseif ($folder) {
            return redirect()->route('folders.show', $folder)
                ->with('success', 'Note updated successfully.');
        }
        
        return redirect()->route('notes.index')->with('success', 'Note updated successfully.');
    }

    /**
     * Show the form for reselling a note (buyer only, scarcity mode only).
     */
    public function resaleForm(Note $note): View|RedirectResponse
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user || $user->role !== 'buyer') {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Hanya buyer yang bisa menjual kembali note.');
        }
        
        if ($note->user_id !== $user->id) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Anda bukan pemilik note ini.');
        }
        
        if (!$note->isScarcityMode()) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Note dengan Standard Mode tidak bisa di-resell. Hanya Scarcity Mode yang bisa di-resell.');
        }
        
        // Check if user has purchased this note
        $purchasedNote = PurchasedNote::where('user_id', $user->id)
            ->where('note_id', $note->id)
            ->first();
        
        if (!$purchasedNote) {
            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Anda belum pernah membeli note ini.');
        }
        
        // Get original purchase price
        $purchaseTransaction = Transaction::where('buyer_id', $user->id)
            ->where('note_id', $note->id)
            ->where('status', 'success')
            ->first();
        
        $originalPrice = $purchaseTransaction ? (float) $purchaseTransaction->amount : 0;
        $currentPrice = $note->price > 0 ? (float) $note->price : $originalPrice;
        
        // Get price guidance
        $defaultMinPrice = Setting::getDefaultMinPrice();
        $priceGuidance = [
            'min_default' => $defaultMinPrice,
            'recommended_multiplier' => Setting::getRecommendedPriceMultiplier(),
            'recommended_price' => $defaultMinPrice > 0
                ? round($defaultMinPrice * Setting::getRecommendedPriceMultiplier())
                : null,
            'category_rules' => Setting::getCategoryMinPriceList(),
            'original_price' => $originalPrice,
        ];
        
        return view('notes.resale', compact('note', 'originalPrice', 'currentPrice', 'priceGuidance'));
    }

    /**
     * Handle resale submission (update price and make note available for sale).
     */
    public function resale(ResaleNoteRequest $request, Note $note): RedirectResponse
    {
        $validated = $request->validated();
        $resalePrice = (float) $validated['resale_price'];
        
        // Update note price
        $note->price = $resalePrice;
        $note->discount_price = null; // Clear discount when reselling
        $note->is_public = true; // Make sure note is public for resale
        $note->status = 'active';
        $note->save();
        
        // Create note history record
        \App\Models\NoteHistory::create([
            'note_id' => $note->id,
            'user_id' => auth()->id(),
            'action' => 'resale_price_set',
            'old_data' => ['price' => $note->getOriginal('price')],
            'new_data' => ['price' => $resalePrice],
            'changes' => 'Resale price set to ' . currency($resalePrice),
            'notes' => 'Note listed for resale by ' . auth()->user()->name,
        ]);
        
        // Log activity
        app(NoteActivityService::class)->logUpdated($note, auth()->user(), 
            ['price' => $note->getOriginal('price')], 
            ['price' => $resalePrice]
        );
        
        return redirect()->route('marketplace.show', $note)
            ->with('success', 'Note berhasil dipasang untuk dijual dengan harga ' . currency($resalePrice) . '. Buyer lain sekarang bisa membeli note ini dari Anda.');
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
     * Handle file uploads for notes (with large file support)
     */
    protected function handleFileUploads($request): array
    {
        return $this->handleFileUploadsWithProgress($request, auth()->user());
    }

    /**
     * Handle file uploads with progress tracking for large files
     */
    protected function handleFileUploadsWithProgress($request, $user): array
    {
        if (!$request->hasFile('attachments')) {
            return [];
        }

        $files = $request->file('attachments');
        $attachments = [];
        $uploadService = app(LargeFileUploadService::class);

        // Increase execution time and memory limit for large files
        $hasLargeFile = false;
        foreach ($files as $file) {
            if ($file->getSize() >= LargeFileUploadService::LARGE_FILE_THRESHOLD) {
                $hasLargeFile = true;
                break;
            }
        }

        if ($hasLargeFile) {
            // Increase time limit for large file uploads
            set_time_limit(600); // 10 minutes
            ini_set('max_execution_time', '600');
            ini_set('memory_limit', '512M');
        }

        foreach ($files as $index => $file) {
            try {
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

                // Check if file is large (40MB+)
                $isLargeFile = $file->getSize() >= LargeFileUploadService::LARGE_FILE_THRESHOLD;

                if ($isLargeFile) {
                    // Use large file upload service with progress tracking
                    $uploadId = Str::uuid();
                    $progressCallback = function($progress, $uploaded, $total) use ($uploadId, $uploadService) {
                        $uploadService->setUploadProgress($uploadId, $progress, $uploaded, $total);
                    };

                    $attachment = $uploadService->handleLargeFileUpload($file, $user->id, $progressCallback);
                    $attachments[] = $attachment;

                    // Clear progress after upload
                    session()->forget("upload_progress_{$uploadId}");
                } else {
                    // Use regular upload for smaller files
                    $attachment = $uploadService->handleRegularFile($file, $user->id);
                    $attachments[] = $attachment;
                }

            } catch (\Exception $e) {
                // Log error but continue with other files
                \Log::error('File upload failed', [
                    'user_id' => $user->id,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);

                // Add error to session for display
                session()->flash('upload_errors', array_merge(
                    session('upload_errors', []),
                    [$file->getClientOriginalName() => $e->getMessage()]
                ));
            }
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

    private function generateContentHash(?string $content): string
    {
        $normalized = Str::of(strip_tags($content ?? ''))
            ->lower()
            ->replaceMatches('/\s+/u', ' ')
            ->trim();

        return hash('sha256', (string) $normalized);
    }

    private function detectUnauthorizedResale(string $contentHash, User $user, ?string $ignoreNoteId = null): ?Note
    {
        if (empty($contentHash)) {
            return null;
        }

        $duplicate = Note::query()
            ->where('content_hash', $contentHash)
            ->when($ignoreNoteId, fn ($query) => $query->where('id', '!=', $ignoreNoteId))
            ->where(function ($query) use ($user) {
                $query->whereNull('original_creator_id')
                    ->orWhere('original_creator_id', '!=', $user->id);
            })
            ->first();

        if (!$duplicate) {
            return null;
        }

        if ($duplicate->user_id === $user->id) {
            return null;
        }

        $hasPurchased = PurchasedNote::where('user_id', $user->id)
            ->where('note_id', $duplicate->id)
            ->exists();

        $wasBuyer = Transaction::where('buyer_id', $user->id)
            ->where('note_id', $duplicate->id)
            ->where('status', 'success')
            ->exists();

        if ($hasPurchased || $wasBuyer) {
            return $duplicate;
        }

        return null;
    }
}
