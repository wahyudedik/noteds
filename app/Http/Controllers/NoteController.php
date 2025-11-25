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
use App\Services\AutoTaggingService;
use App\Services\VideoService;
use App\Services\ClamAVService;
use App\Services\WatermarkingService;
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
        
        // Check if user has uploaded document identity and selfie (admin tidak perlu verifikasi)
        if (!$user->hasRole('admin') && (!$user->ktp_path || !$user->selfie_path)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda dengan mengupload dokumen identitas (KTP atau Kartu Pelajar) dan foto selfie untuk membuat note.');
        }
        
        $tags = Tag::orderBy('name')->get();
        
        // Get folders and workspaces for all users
        $folders = $user->allFolders()->get();
        $workspaces = $user->allWorkspaces();
        $selectedWorkspace = null;
        $selectedFolder = null;
            
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
            'design' => 'Design',
            'code' => 'Code',
            'photo' => 'Photo',
            'audio' => 'Audio',
            'video' => 'Video',
            'theme' => 'Theme',
            '3d' => '3D',
            'elements' => 'Elements',
        ];

        $languages = [
            '' => '— Language —',
            'en' => 'English',
            'id' => 'Bahasa Indonesia',
            'ar' => 'العربية',
        ];

        return view('notes.create', compact('tags', 'folders', 'workspaces', 'selectedWorkspace', 'selectedFolder', 'priceGuidance', 'ecosystems', 'languages'));
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
        
        // Check if user has uploaded document identity and selfie (admin tidak perlu verifikasi)
        if (!$user->hasRole('admin') && (!$user->ktp_path || !$user->selfie_path)) {
            return redirect()->route('profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda dengan mengupload dokumen identitas (KTP atau Kartu Pelajar) dan foto selfie untuk membuat note.');
        }
        
        // No note creation limit - all users can create unlimited notes

        // Validate and handle file uploads
        $user = auth()->user();
        $uploadService = app(LargeFileUploadService::class);
        
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $validationErrors = [];
            
            // Check maximum files
            if (count($files) > 10) {
                return redirect()->route('notes.create')
                    ->withInput()
                    ->withErrors(['attachments' => 'Maximum 10 files allowed per note.']);
            }
            
            foreach ($files as $index => $file) {
                $validation = $uploadService->validateFile($file);
                
                if (!$validation['valid']) {
                    $validationErrors["attachments.{$index}"] = $validation['error'];
                }
            }
            
            if (!empty($validationErrors)) {
                return redirect()->route('notes.create')
                    ->withInput()
                    ->withErrors($validationErrors);
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
        
        // Handle workspace and folder with validation
        $workspace = null;
        $folder = null;
        $validationErrors = [];
        
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
                $validationErrors['workspace_id'] = 'Workspace yang dipilih tidak ditemukan atau Anda tidak memiliki akses ke workspace tersebut.';
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
                    // Validate folder's workspace access
                    $folderWorkspace = \App\Models\Workspace::where('id', $folder->workspace_id)
                        ->where(function($q) use ($user) {
                            $q->where('owner_id', $user->id)
                              ->orWhereHas('members', function($q) use ($user) {
                                  $q->where('users.id', $user->id);
                              });
                        })
                        ->first();
                    
                    if ($folderWorkspace) {
                        $validated['workspace_id'] = $folderWorkspace->id;
                        $workspace = $folderWorkspace;
                    }
                }
            } else {
                $validationErrors['folder_id'] = 'Folder yang dipilih tidak ditemukan atau Anda tidak memiliki akses ke folder tersebut.';
                unset($validated['folder_id']);
            }
        }
        
        // Return validation errors if any
        if (!empty($validationErrors)) {
            return redirect()->route('notes.create')
                ->withInput()
                ->withErrors($validationErrors);
        }

        // Handle preview content - auto-generate if not provided
        if (empty($validated['preview_content'])) {
            $content = strip_tags($validated['content']);
            $validated['preview_content'] = Str::limit($content, 300);
        }

        // Handle file uploads (with large file support)
        $attachments = $this->handleFileUploadsWithProgress($request, $user);
        
        // Scan uploaded files for viruses (if enabled)
        if (config('clamav.realtime_scanning', true)) {
            $clamAVService = app(ClamAVService::class);
            if ($clamAVService->isAvailable()) {
                $infectedFiles = [];
                foreach ($attachments as $index => $attachment) {
                    if (is_array($attachment) && isset($attachment['path'])) {
                        $filePath = Storage::disk($attachment['disk'] ?? 'private')->path($attachment['path']);
                        $scan = $clamAVService->scanFile($filePath, $attachment['filename'] ?? basename($attachment['path']), $user, 'realtime');
                        
                        if ($scan->isInfected()) {
                            $infectedFiles[] = $attachment['filename'] ?? basename($attachment['path']);
                            // Remove infected file from attachments
                            unset($attachments[$index]);
                        }
                    }
                }
                
                if (!empty($infectedFiles)) {
                    return redirect()->route('notes.create')
                        ->withInput()
                        ->withErrors(['attachments' => 'Virus terdeteksi pada file: ' . implode(', ', $infectedFiles) . '. File telah dihapus.']);
                }
            }
        }
        
        // Handle external links
        $externalLinks = $this->handleExternalLinks($request);
        $attachments = array_merge($attachments, $externalLinks);
        
        $validated['attachments'] = $attachments;
        $validated['file_count'] = count($attachments);

        // Handle thumbnail uploads
        $thumbnails = $this->handleThumbnailUploads($request);
        $validated['thumbnails'] = $thumbnails;

        // Handle video preview upload
        if ($request->hasFile('video_preview')) {
            try {
                $videoService = app(VideoService::class);
                $videoData = $videoService->processVideoPreview($request->file('video_preview'));
                $validated['video_preview'] = $videoData['video_path'];
                $validated['video_preview_thumbnail'] = $videoData['thumbnail_path'];
                $validated['video_preview_duration'] = $videoData['duration'];
            } catch (\Exception $e) {
                return redirect()->route('notes.create')
                    ->withInput()
                    ->withErrors(['video_preview' => $e->getMessage()]);
            }
        }

        // Set default preview_percentage if not provided
        if (!isset($validated['preview_percentage'])) {
            $validated['preview_percentage'] = 0;
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Handle draft and scheduled publishing
        $isDraft = $request->has('save_as_draft') || $request->input('is_draft', false);
        $scheduledPublishAt = $request->input('scheduled_publish_at');
        
        $validated['is_draft'] = $isDraft;
        
        // Use scheduled_publish_at (from form) and also set scheduled_at for compatibility
        if ($scheduledPublishAt && !$isDraft) {
            $scheduledDate = \Carbon\Carbon::parse($scheduledPublishAt);
            $validated['scheduled_publish_at'] = $scheduledDate;
            $validated['scheduled_at'] = $scheduledDate; // Also set for compatibility
            $validated['status'] = 'active'; // Will be published later
        } else {
            $validated['scheduled_at'] = null;
        }
        
        // If not draft and not scheduled, publish immediately
        if (!$isDraft && !$scheduledPublishAt) {
            $validated['published_at'] = now();
        }

        $note = Note::create($validated);
        $this->syncTags($note, $tags);

        // Create watermark settings if provided
        if ($request->has('watermark_enabled')) {
            $this->createWatermarkSettings($note, $request);
        }

        // Create DRM settings if provided
        if ($request->has('drm_enabled')) {
            $this->createDrmSettings($note, $request);
        }

        // Apply watermarking to uploaded files if enabled
        if ($note->watermarkSetting && $note->watermarkSetting->enabled) {
            $this->applyWatermarkingToAttachments($note, $attachments);
        }

        // No auto-tagging - users must tag manually

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
            'action' => $isDraft ? 'draft_created' : ($scheduledPublishAt ? 'scheduled' : 'created'),
            'old_data' => null,
            'new_data' => $note->only(['title', 'content', 'summary', 'price', 'discount_price', 'is_public', 'status', 'is_draft', 'scheduled_at', 'scheduled_publish_at']),
            'changes' => $isDraft ? 'Note saved as draft' : ($scheduledPublishAt ? 'Note scheduled for publishing' : 'Note created'),
            'notes' => 'Note ' . ($isDraft ? 'saved as draft' : ($scheduledPublishAt ? 'scheduled' : 'created')) . ' by ' . auth()->user()->name,
        ]);

        // Log activity
        if (!$isDraft) {
            app(NoteActivityService::class)->logCreated($note, auth()->user());
        }

        // Only notify if published immediately (not draft, not scheduled)
        if (!$isDraft && !$scheduledPublishAt && $note->is_public && $note->status === 'active' && !$note->notificationMeta('published_notified_at')) {
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
        
        // Get folders and workspaces for all users
        $folders = $user->allFolders()->get();
        $workspaces = $user->allWorkspaces();

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
            'design' => 'Design',
            'code' => 'Code',
            'photo' => 'Photo',
            'audio' => 'Audio',
            'video' => 'Video',
            'theme' => 'Theme',
            '3d' => '3D',
            'elements' => 'Elements',
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

        // Validate and handle file uploads
        $uploadService = app(LargeFileUploadService::class);
        
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $validationErrors = [];
            
            // Check maximum files (including existing)
            $existingCount = count($note->attachments ?? []);
            if (count($files) + $existingCount > 10) {
                return redirect()->route('notes.edit', $note)
                    ->withInput()
                    ->withErrors(['attachments' => 'Maximum 10 files allowed per note.']);
            }
            
            foreach ($files as $index => $file) {
                $validation = $uploadService->validateFile($file);
                
                if (!$validation['valid']) {
                    $validationErrors["attachments.{$index}"] = $validation['error'];
                }
            }
            
            if (!empty($validationErrors)) {
                return redirect()->route('notes.edit', $note)
                    ->withInput()
                    ->withErrors($validationErrors);
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
        $validationErrors = [];
        
        // Handle folder first (because folder can determine workspace)
        if ($request->has('folder_id')) {
            $folderId = $request->input('folder_id');
            if (!empty($folderId)) {
                $folder = \App\Models\Folder::where('id', $folderId)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($folder) {
                    $validated['folder_id'] = $folder->id;
                    // If folder has workspace, validate access to it
                    if ($folder->workspace_id) {
                        $folderWorkspace = \App\Models\Workspace::where('id', $folder->workspace_id)
                            ->where(function($q) use ($user) {
                                $q->where('owner_id', $user->id)
                                  ->orWhereHas('members', function($q) use ($user) {
                                      $q->where('users.id', $user->id);
                                  });
                            })
                            ->first();
                        
                        if ($folderWorkspace) {
                            $workspace = $folderWorkspace;
                            $validated['workspace_id'] = $workspace->id;
                        } else {
                            // Folder belongs to workspace user doesn't have access to
                            $validationErrors['folder_id'] = 'Folder yang dipilih berada di workspace yang tidak Anda miliki aksesnya.';
                            $validated['folder_id'] = null;
                        }
                    }
                } else {
                    // Invalid folder, remove it
                    $validationErrors['folder_id'] = 'Folder yang dipilih tidak ditemukan atau Anda tidak memiliki akses ke folder tersebut.';
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
            // Check if it's an external link
            if (is_array($attachment) && isset($attachment['type']) && $attachment['type'] === 'external') {
                $url = $attachment['url'] ?? '';
                return !in_array($url, $removedAttachments);
            }
            // Check if it's a URL string
            if (is_string($attachment) && filter_var($attachment, FILTER_VALIDATE_URL)) {
                return !in_array($attachment, $removedAttachments);
            }
            // Regular file attachment
            $filename = is_array($attachment) ? ($attachment['filename'] ?? '') : basename($attachment);
            return !in_array($filename, $removedAttachments);
        });

        // Handle external links
        $externalLinks = $this->handleExternalLinks($request);
        
        // Merge new and existing attachments
        $validated['attachments'] = array_merge(array_values($existingAttachments), $newAttachments, $externalLinks);
        $validated['file_count'] = count($validated['attachments']);
        
        // Store new attachments for watermarking
        $newAttachmentsForWatermarking = $newAttachments;

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

        // Handle video preview upload (replace existing if new one is uploaded)
        if ($request->hasFile('video_preview')) {
            try {
                // Delete old video and thumbnail if they exist
                if ($note->video_preview) {
                    $videoService = app(VideoService::class);
                    $videoService->deleteVideoPreview($note->video_preview, $note->video_preview_thumbnail);
                }

                $videoService = app(VideoService::class);
                $videoData = $videoService->processVideoPreview($request->file('video_preview'));
                $validated['video_preview'] = $videoData['video_path'];
                $validated['video_preview_thumbnail'] = $videoData['thumbnail_path'];
                $validated['video_preview_duration'] = $videoData['duration'];
            } catch (\Exception $e) {
                return redirect()->route('notes.edit', $note)
                    ->withInput()
                    ->withErrors(['video_preview' => $e->getMessage()]);
            }
        } elseif ($request->has('remove_video_preview') && $request->input('remove_video_preview')) {
            // Remove video preview if user explicitly removes it
            if ($note->video_preview) {
                $videoService = app(VideoService::class);
                $videoService->deleteVideoPreview($note->video_preview, $note->video_preview_thumbnail);
            }
            $validated['video_preview'] = null;
            $validated['video_preview_thumbnail'] = null;
            $validated['video_preview_duration'] = null;
        }

        // Set default preview_percentage if not provided
        if (!isset($validated['preview_percentage'])) {
            $validated['preview_percentage'] = $note->preview_percentage ?? 0;
        }

        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Handle draft and scheduled publishing
        $isDraft = $request->has('save_as_draft') || $request->input('is_draft', false);
        $scheduledPublishAt = $request->input('scheduled_publish_at');
        $scheduledAt = $request->input('scheduled_at'); // Also check for scheduled_at (from edit form)
        $publishNow = $request->has('publish_now');
        
        // Use scheduled_publish_at if available, otherwise use scheduled_at
        $scheduledDate = $scheduledPublishAt ?: $scheduledAt;
        
        if ($publishNow && $note->is_draft) {
            // Publishing draft now
            $validated['is_draft'] = false;
            $validated['scheduled_at'] = null;
            $validated['scheduled_publish_at'] = null;
            if (!$note->published_at) {
                $validated['published_at'] = now();
            }
        } elseif ($scheduledDate && !$isDraft) {
            // Scheduling for later
            $scheduled = \Carbon\Carbon::parse($scheduledDate);
            $validated['scheduled_publish_at'] = $scheduled;
            $validated['scheduled_at'] = $scheduled; // Also set for compatibility
            $validated['is_draft'] = false;
        } elseif ($isDraft) {
            // Saving as draft
            $validated['is_draft'] = true;
            $validated['scheduled_at'] = null;
            $validated['scheduled_publish_at'] = null;
        } else {
            // Publishing immediately (if not already published)
            if (!$note->published_at && !$note->is_draft) {
                $validated['published_at'] = now();
            }
            $validated['is_draft'] = false;
            $validated['scheduled_at'] = null;
            $validated['scheduled_publish_at'] = null;
        }

        // Track changes for activity log and history
        $oldData = $note->only(['title', 'content', 'summary', 'price', 'discount_price', 'is_public', 'status', 'preview_content', 'preview_percentage', 'is_draft', 'scheduled_at', 'published_at']);
        $oldTags = $note->tags->pluck('name')->toArray();

        $note->update($validated);
        $newTags = $this->syncTags($note, $tags);

        // Update watermark settings if provided
        if ($request->has('watermark_enabled')) {
            $this->createWatermarkSettings($note, $request);
        }

        // Update DRM settings if provided
        if ($request->has('drm_enabled')) {
            $this->createDrmSettings($note, $request);
        }

        // Re-apply watermarking if enabled and new files uploaded
        if (isset($newAttachmentsForWatermarking) && !empty($newAttachmentsForWatermarking)) {
            if ($note->watermarkSetting && $note->watermarkSetting->enabled) {
                $this->applyWatermarkingToAttachments($note, $newAttachmentsForWatermarking);
            }
        }

        // No auto-tagging - users must tag manually
        
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
        $recommendedPrice = $defaultMinPrice > 0
            ? round($defaultMinPrice * Setting::getRecommendedPriceMultiplier())
            : null;
        $priceGuidance = [
            'min_default' => $defaultMinPrice,
            'recommended_multiplier' => Setting::getRecommendedPriceMultiplier(),
            'recommended_price' => $recommendedPrice !== null ? (string) number_format($recommendedPrice, 2, '.', '') : null,
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
        
        // Update note price (decimal:2 - cast to string for proper decimal handling)
        $note->price = (string) number_format($resalePrice, 2, '.', '');
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
            'new_data' => ['price' => (string) $resalePrice],
            'changes' => 'Resale price set to ' . currency($resalePrice),
            'notes' => 'Note listed for resale by ' . auth()->user()->name,
        ]);
        
        // Log activity
        $oldPrice = $note->getOriginal('price');
        app(NoteActivityService::class)->logUpdated($note, auth()->user(), 
            ['price' => $oldPrice], 
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
     * Handle background file upload for files > 5MB
     * This allows files to be uploaded in the background without blocking form submission
     */
    public function uploadBackground(Request $request)
    {
        // Increase memory limit and execution time IMMEDIATELY for file uploads
        // This prevents memory exhaustion errors during file processing
        ini_set('memory_limit', '512M');
        set_time_limit(900); // 15 minutes for very large files (51MB+)
        ini_set('max_execution_time', '900');
        ini_set('max_input_time', '900'); // Also increase input time
        
        $user = auth()->user();
        
        // Check if file was actually uploaded
        if (!$request->hasFile('file')) {
            // Check if it's a PHP upload error
            if ($request->has('file')) {
                $uploadError = $request->input('file');
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File terlalu besar. Melebihi batas upload_max_filesize di server.',
                    UPLOAD_ERR_FORM_SIZE => 'File terlalu besar. Melebihi batas MAX_FILE_SIZE di form.',
                    UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian. Silakan coba lagi.',
                    UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan di server.',
                    UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
                    UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension PHP.',
                ];
                
                $errorCode = is_numeric($uploadError) ? (int)$uploadError : UPLOAD_ERR_NO_FILE;
                $errorMessage = $errorMessages[$errorCode] ?? 'Unknown upload error';
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'error_code' => $errorCode
                ], 400);
            }
            
            return response()->json([
                'success' => false,
                'error' => 'Tidak ada file yang dipilih atau file terlalu besar (melebihi post_max_size atau upload_max_filesize di PHP).'
            ], 400);
        }

        $file = $request->file('file');
        
        // Check if file upload was successful
        if (!$file->isValid()) {
            $errorCode = $file->getError();
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File terlalu besar. Melebihi batas upload_max_filesize (' . ini_get('upload_max_filesize') . ') di server.',
                UPLOAD_ERR_FORM_SIZE => 'File terlalu besar. Melebihi batas MAX_FILE_SIZE di form.',
                UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian. Silakan coba lagi.',
                UPLOAD_ERR_NO_FILE => 'Tidak ada file yang dipilih.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan di server.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
                UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh extension PHP.',
            ];
            
            $errorMessage = $errorMessages[$errorCode] ?? 'File upload error: ' . $errorCode;
            
            // Check PHP configuration limits
            $uploadMaxFilesize = ini_get('upload_max_filesize');
            $postMaxSize = ini_get('post_max_size');
            $fileSizeMB = round($file->getSize() / 1048576, 2);
            
            if ($errorCode == UPLOAD_ERR_INI_SIZE || $errorCode == UPLOAD_ERR_FORM_SIZE) {
                $errorMessage .= " File Anda: {$fileSizeMB}MB. Limit server: upload_max_filesize={$uploadMaxFilesize}, post_max_size={$postMaxSize}";
            }
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'error_code' => $errorCode,
                'file_size_mb' => $fileSizeMB,
                'server_limits' => [
                    'upload_max_filesize' => $uploadMaxFilesize,
                    'post_max_size' => $postMaxSize,
                ]
            ], 400);
        }

        $uploadService = app(LargeFileUploadService::class);
        
        // Validate file
        $validation = $uploadService->validateFile($file);
        
        if (!$validation['valid']) {
            $errorMessage = $validation['error'] ?? 'File validation failed';
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
            ], 400);
        }

        try {
            // Log upload start for debugging
            \Log::info('Background upload started', [
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'file_size_mb' => round($file->getSize() / 1048576, 2),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
            ]);
            
            // Memory limit and execution time already increased at the start of method
            // Upload file (memory limit already set to 512M)
            $attachment = $uploadService->handleLargeFileUpload($file, $user->id);
            
            // Store in session for later use in form submission
            // Only store minimal data to avoid session size issues
            $sessionKey = 'background_uploads_' . $user->id;
            $backgroundUploads = session($sessionKey, []);
            $uploadId = Str::uuid();
            
            // Store only essential data (not the full file content)
            $backgroundUploads[$uploadId] = [
                'filename' => $attachment['filename'],
                'path' => $attachment['path'],
                'size' => $attachment['size'],
                'mime' => $attachment['mime'],
                'is_large' => $attachment['is_large'] ?? false,
            ];
            
            // Save session with minimal data
            session([$sessionKey => $backgroundUploads]);
            
            // Log successful upload
            \Log::info('Background upload completed', [
                'user_id' => $user->id,
                'upload_id' => $uploadId,
                'filename' => $attachment['filename'],
                'file_size_mb' => round($attachment['size'] / 1048576, 2),
            ]);
            
            return response()->json([
                'success' => true,
                'upload_id' => $uploadId,
                'attachment' => $attachment,
                'message' => 'File uploaded successfully'
            ]);
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $fileSize = $file->getSize() ?? 0;
            $fileSizeMB = round($fileSize / 1048576, 2);
            $uploadMaxFilesize = ini_get('upload_max_filesize');
            $postMaxSize = ini_get('post_max_size');
            $maxExecutionTime = ini_get('max_execution_time');
            $memoryLimit = ini_get('memory_limit');
            $currentMemoryUsage = memory_get_usage(true);
            $currentMemoryUsageMB = round($currentMemoryUsage / 1048576, 2);
            $memoryLimitBytes = $this->convertMemoryToBytes($memoryLimit);
            $memoryLimitMB = round($memoryLimitBytes / 1048576, 2);
            
            // Check if it's a memory exhaustion error
            $isMemoryError = str_contains($errorMessage, 'memory') 
                || str_contains($errorMessage, 'Memory') 
                || str_contains($errorMessage, 'Allowed memory') 
                || str_contains($errorMessage, 'exhausted');
            
            // Provide more helpful error messages with reassurance
            if ($isMemoryError) {
                $errorMessage = "Out of memory. File terlalu besar ({$fileSizeMB}MB). Memory limit: {$memoryLimitMB}MB, Usage: {$currentMemoryUsageMB}MB. File akan otomatis diupload saat form disubmit dengan memory limit yang lebih besar.";
            } elseif (str_contains($errorMessage, 'timeout') || str_contains($errorMessage, 'execution time') || str_contains($errorMessage, 'Maximum execution time')) {
                $errorMessage = "Upload timeout. File terlalu besar ({$fileSizeMB}MB) atau koneksi lambat. File akan otomatis diupload saat form disubmit.";
            } elseif (str_contains($errorMessage, 'size') || str_contains($errorMessage, 'exceed') || str_contains($errorMessage, 'larger than')) {
                $errorMessage = "File terlalu besar ({$fileSizeMB}MB). Limit server: upload_max_filesize={$uploadMaxFilesize}, post_max_size={$postMaxSize}. File akan otomatis diupload saat form disubmit.";
            } elseif (str_contains($errorMessage, 'disk') || str_contains($errorMessage, 'Disk') || str_contains($errorMessage, 'space')) {
                $errorMessage = "Disk penuh atau tidak ada space tersedia. Silakan hubungi administrator.";
            } else {
                // Generic error with reassuring message
                $errorMessage = "Upload gagal: {$errorMessage}. File: {$fileSizeMB}MB. File akan otomatis diupload saat form disubmit.";
            }
            
            \Log::error('Background file upload failed', [
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'file_size' => $fileSize,
                'file_size_mb' => $fileSizeMB,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'memory_limit' => $memoryLimit,
                'memory_usage' => $currentMemoryUsageMB . 'MB',
                'memory_peak' => round(memory_get_peak_usage(true) / 1048576, 2) . 'MB',
                'upload_max_filesize' => $uploadMaxFilesize,
                'post_max_size' => $postMaxSize,
                'max_execution_time' => $maxExecutionTime,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'can_retry' => true, // File can be uploaded on form submit
                'file_size_mb' => $fileSizeMB,
                'server_limits' => [
                    'upload_max_filesize' => $uploadMaxFilesize,
                    'post_max_size' => $postMaxSize,
                    'max_execution_time' => $maxExecutionTime,
                    'memory_limit' => $memoryLimit,
                    'memory_usage' => $currentMemoryUsageMB . 'MB',
                ],
                'error_detail' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function handleFileUploadsWithProgress($request, $user): array
    {
        // Increase memory limit for file uploads during form submission
        // This handles files that failed background upload or files uploaded directly
        ini_set('memory_limit', '512M');
        set_time_limit(600);
        ini_set('max_execution_time', '600');
        
        $attachments = [];
        $uploadService = app(LargeFileUploadService::class);
        
        // Get background uploaded files from session
        $sessionKey = 'background_uploads_' . $user->id;
        $backgroundUploads = session($sessionKey, []);
        $uploadIds = $request->input('background_upload_ids', []);
        
        // Add background uploaded files
        foreach ($uploadIds as $uploadId) {
            if (isset($backgroundUploads[$uploadId])) {
                $attachments[] = $backgroundUploads[$uploadId];
            }
        }
        
        // Handle regular file uploads (< 5MB or files not uploaded in background)
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $backgroundUploadIds = $request->input('background_upload_ids', []);
            $backgroundFileNames = [];
            
            // Get filenames of background uploaded files
            foreach ($backgroundUploadIds as $uploadId) {
                if (isset($backgroundUploads[$uploadId])) {
                    $backgroundFileNames[] = $backgroundUploads[$uploadId]['filename'];
                }
            }

            // Increase execution time and memory limit for large files
            $hasLargeFile = false;
            foreach ($files as $file) {
                if ($file->getSize() >= \App\Services\LargeFileUploadService::MAX_FILE_SIZE) {
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
                // Skip files that were already uploaded in background
                if (in_array($file->getClientOriginalName(), $backgroundFileNames)) {
                    continue;
                }
                
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

                    // Use standard upload (max 10MB)
                    $attachment = $uploadService->handleLargeFileUpload($file, $user->id);
                    
                    // Scan file for viruses (if enabled)
                    if (config('clamav.realtime_scanning', true)) {
                        $clamAVService = app(ClamAVService::class);
                        if ($clamAVService->isAvailable() && isset($attachment['path'])) {
                            $filePath = Storage::disk($attachment['disk'] ?? 'private')->path($attachment['path']);
                            $scan = $clamAVService->scanFile($filePath, $attachment['filename'] ?? $file->getClientOriginalName(), $user, 'realtime');
                            
                            if ($scan->isInfected()) {
                                // Delete infected file
                                Storage::disk($attachment['disk'] ?? 'private')->delete($attachment['path']);
                                throw new \Exception('Virus terdeteksi pada file: ' . ($attachment['filename'] ?? $file->getClientOriginalName()));
                            }
                        }
                    }
                    
                    $attachments[] = $attachment;

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
        }
        
        // Clear background uploads from session after use
        if (!empty($uploadIds)) {
            $remainingUploads = array_diff_key($backgroundUploads, array_flip($uploadIds));
            session([$sessionKey => $remainingUploads]);
        }
        
        return $attachments;
    }

    /**
     * Handle external links from textarea input
     * Parse URLs from textarea (one per line) and validate them
     */
    protected function handleExternalLinks($request): array
    {
        $externalLinks = [];
        $linksText = $request->input('external_links');
        
        if (empty($linksText)) {
            return $externalLinks;
        }
        
        // Split by newlines and filter empty lines
        $links = array_filter(
            array_map('trim', explode("\n", $linksText)),
            fn($link) => !empty($link)
        );
        
        foreach ($links as $link) {
            // Validate URL
            if (filter_var($link, FILTER_VALIDATE_URL)) {
                // Extract filename from URL if possible
                $parsedUrl = parse_url($link);
                $path = $parsedUrl['path'] ?? '';
                $filename = basename($path);
                if (empty($filename) || $filename === '/') {
                    $filename = 'External Link';
                }
                
                $externalLinks[] = [
                    'type' => 'external',
                    'url' => $link,
                    'filename' => $filename,
                    'size' => null, // External links don't have size
                ];
            }
        }
        
        return $externalLinks;
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

    /**
     * Create watermark settings for note
     */
    protected function createWatermarkSettings(Note $note, Request $request): void
    {
        \App\Models\WatermarkSetting::updateOrCreate(
            ['note_id' => $note->id],
            [
                'enabled' => $request->boolean('watermark_enabled'),
                'type' => $request->input('watermark_type', 'text'),
                'text' => $request->input('watermark_text'),
                'text_color' => $request->input('watermark_text_color', '#000000'),
                'text_size' => $request->input('watermark_text_size', 24),
                'text_font' => $request->input('watermark_text_font'),
                'position' => $request->input('watermark_position', 'center'),
                'opacity' => $request->input('watermark_opacity', 50),
                'image_path' => $request->input('watermark_image_path'),
                'image_size' => $request->input('watermark_image_size'),
                'margin' => $request->input('watermark_margin', 10),
                'apply_to_images' => $request->boolean('watermark_apply_to_images', true),
                'apply_to_pdfs' => $request->boolean('watermark_apply_to_pdfs', true),
            ]
        );
    }

    /**
     * Create DRM settings for note
     */
    protected function createDrmSettings(Note $note, Request $request): void
    {
        \App\Models\DrmSetting::updateOrCreate(
            ['note_id' => $note->id],
            [
                'enabled' => $request->boolean('drm_enabled'),
                'encrypt_files' => $request->boolean('drm_encrypt_files'),
                'time_limited_access' => $request->boolean('drm_time_limited_access'),
                'access_duration_days' => $request->input('drm_access_duration_days'),
                'device_limit_enabled' => $request->boolean('drm_device_limit_enabled'),
                'max_devices' => $request->input('drm_max_devices', 3),
                'license_key_enabled' => $request->boolean('drm_license_key_enabled'),
                'license_key_type' => $request->input('drm_license_key_type', 'per_user'),
            ]
        );
    }

    /**
     * Apply watermarking to attachments
     */
    protected function applyWatermarkingToAttachments(Note $note, array $attachments): void
    {
        $watermarkSetting = $note->watermarkSetting;
        if (!$watermarkSetting || !$watermarkSetting->enabled) {
            return;
        }

        $watermarkingService = app(WatermarkingService::class);

        foreach ($attachments as $attachment) {
            if (is_array($attachment) && isset($attachment['path'])) {
                $disk = $attachment['disk'] ?? 'private';
                $watermarkedPath = $watermarkingService->applyWatermark(
                    $attachment['path'],
                    $disk,
                    $watermarkSetting
                );

                // Update attachment path if watermarked
                if ($watermarkedPath !== $attachment['path']) {
                    $attachment['watermarked_path'] = $watermarkedPath;
                }
            }
        }
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

    /**
     * Convert memory limit string to bytes
     * 
     * @param string $memoryLimit
     * @return int
     */
    protected function convertMemoryToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        if (empty($memoryLimit) || $memoryLimit === '-1') {
            return PHP_INT_MAX;
        }
        
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
