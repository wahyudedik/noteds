<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\User;
use App\Models\NoteConversation;
use App\Models\SearchHistory;
use App\Models\SavedSearch;
use App\Services\CommissionService;
use App\Services\ReferralService;
use App\Services\TaxService;
use App\Services\NotificationService;
use App\Services\NoteShareService;
use App\Services\EmailCampaignService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private EmailCampaignService $emailCampaignService
    ) {}

    public function index(Request $request): View
    {
        // Cache key based on request parameters
        $cacheKey = 'marketplace_index_' . md5(json_encode($request->all()));

        // Get featured notes for marketplace grid (cached for 1 hour)
        $featuredNotes = Cache::remember('marketplace_featured_notes_grid', 3600, function () {
            return \App\Models\FeaturedNote::active()
                ->byLocation('marketplace_grid')
                ->with(['note' => function ($q) {
                    $q->select('id', 'title', 'price', 'user_id', 'summary', 'thumbnails', 'ecosystem_category', 'status', 'is_public', 'created_at');
                }, 'note.tags:id,name', 'note.user:id,name,username,avatar', 'note.user.badges:id,name,icon,color,category', 'note.reviews:id,note_id,rating', 'note.viewHistory' => function ($q) {
                    $q->select('id', 'note_id', 'viewed_at')
                        ->where('viewed_at', '>=', now()->subDays(7));
                }])
                ->inRandomOrder()
                ->limit(6)
                ->get();
        });

        // Get featured banner (cached for 1 hour)
        $featuredBanner = Cache::remember('marketplace_featured_banner', 3600, function () {
            return \App\Models\FeaturedNote::active()
                ->byLocation('marketplace_banner')
                ->with(['note' => function ($q) {
                    $q->select('id', 'title', 'price', 'user_id', 'summary', 'thumbnails', 'ecosystem_category', 'status', 'is_public', 'created_at');
                }, 'note.tags:id,name', 'note.user:id,name,username,avatar', 'note.user.badges:id,name,icon,color,category', 'note.reviews:id,note_id,rating', 'note.viewHistory' => function ($q) {
                    $q->select('id', 'note_id', 'viewed_at')
                        ->where('viewed_at', '>=', now()->subDays(7));
                }])
                ->inRandomOrder()
                ->first();
        });

        // Build query with optimized eager loading and select specific columns
        // Add avg_rating subquery if rating filter is used
        $hasRatingFilter = $request->min_rating || $request->rating;
        $baseSelect = [
            'id',
            'user_id',
            'title',
            'summary',
            'price',
            'discount_price',
            'thumbnails',
            'ecosystem_category',
            'language',
            'status',
            'is_public',
            'created_at',
            'updated_at'
        ];

        $notesQuery = Note::publicOnly();

        // Add avg_rating subquery if rating filter is used
        if ($hasRatingFilter) {
            $notesQuery->select(array_merge($baseSelect, [
                DB::raw('(SELECT COALESCE(AVG(rating), 0) FROM note_reviews WHERE note_reviews.note_id = notes.id) as avg_rating')
            ]));
        } else {
            $notesQuery->select($baseSelect);
        }

        $notesQuery = $notesQuery->with([
            'tags:id,name',
            'user:id,name,username,avatar',
            'user.badges:id,name,icon,color,category',
            'user.userLevels.level',
            'reviews' => function ($q) {
                $q->select('id', 'note_id', 'rating', 'comment', 'created_at');
            },
            'viewHistory' => function ($q) {
                $q->select('id', 'note_id', 'viewed_at')
                    ->where('viewed_at', '>=', now()->subDays(7));
            }
        ])
            ->when($request->search, function ($query) use ($request) {
                return $this->applyAdvancedSearch($query, $request->search, $request);
            })
            ->when($request->author, function ($query) use ($request) {
                return $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->author . '%')
                        ->orWhere('username', 'like', '%' . $request->author . '%');
                });
            })
            ->when($request->date_from || $request->date_to, function ($query) use ($request) {
                if ($request->date_from) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->date_to) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
                return $query;
            })
            ->when($request->ecosystem, function ($query) use ($request) {
                return $query->where('ecosystem_category', $request->ecosystem);
            })
            ->when($request->language, function ($query) use ($request) {
                return $query->where('language', $request->language);
            })
            ->when($request->min_price, function ($query) use ($request) {
                return $query->where('price', '>=', $request->min_price);
            })
            ->when($request->max_price, function ($query) use ($request) {
                return $query->where('price', '<=', $request->max_price);
            })
            ->when($request->tag || $request->tags, function ($query) use ($request) {
                $tagIds = $request->tags ?? [];
                if ($request->tag) {
                    $tagIds[] = $request->tag;
                }
                if (!empty($tagIds)) {
                    return $query->whereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('tags.id', $tagIds);
                    });
                }
                return $query;
            })
            ->when($request->input('language') || $request->input('languages'), function ($query) use ($request) {
                $languages = $request->input('languages') ?? [];
                if ($request->input('language')) {
                    $languages[] = $request->input('language');
                }
                if (!empty($languages)) {
                    return $query->whereIn('language', array_unique($languages));
                }
                return $query;
            })
            ->when($request->min_rating || $request->rating, function ($query) use ($request) {
                $minRating = (float) ($request->min_rating ?? $request->rating ?? 0);
                if ($minRating > 0) {
                    return $query->havingRaw('avg_rating >= ?', [$minRating]);
                }
                return $query;
            })
            ->when($request->seller_verified, function ($query) use ($request) {
                if ($request->seller_verified === '1') {
                    return $query->whereHas('user', function ($q) {
                        $q->where('is_verified', true);
                    });
                }
                return $query;
            })
            ->when($request->seller_type, function ($query) use ($request) {
                if ($request->seller_type === 'top_rated') {
                    return $query->whereHas('user', function ($q) {
                        $q->selectRaw('users.id, AVG(note_reviews.rating) as avg_seller_rating')
                            ->join('notes', 'notes.user_id', '=', 'users.id')
                            ->join('note_reviews', 'note_reviews.note_id', '=', 'notes.id')
                            ->groupBy('users.id')
                            ->havingRaw('AVG(note_reviews.rating) >= 4.5');
                    });
                }
                return $query;
            })
            ->when($request->file_type, function ($query) use ($request) {
                $fileTypes = is_array($request->file_type) ? $request->file_type : [$request->file_type];
                $allExtensions = [];
                foreach ($fileTypes as $fileType) {
                    $extensions = $this->getExtensionsForFileType($fileType);
                    if (!empty($extensions)) {
                        $allExtensions = array_merge($allExtensions, $extensions);
                    }
                }
                if (!empty($allExtensions)) {
                    return $query->where(function ($q) use ($allExtensions) {
                        $q->where(function ($subQ) use ($allExtensions) {
                            foreach ($allExtensions as $extension) {
                                $subQ->orWhere('attachments', 'like', '%' . $extension . '%')
                                    ->orWhere('attachments', 'like', '%' . strtoupper($extension) . '%');
                            }
                        });
                    });
                }
                return $query;
            })
            ->when($request->seller, function ($query) use ($request) {
                return $query->whereHas('user', function ($q) use ($request) {
                    $q->where('users.id', $request->seller)
                        ->orWhere('users.username', 'like', '%' . $request->seller . '%')
                        ->orWhere('users.name', 'like', '%' . $request->seller . '%');
                });
            })
            ->when($request->sort, function ($query) use ($request) {
                return match ($request->sort) {
                    'price_asc' => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    'rating' => $query->selectSub(function ($q) {
                        $q->selectRaw('COALESCE(AVG(rating), 0)')
                            ->from('note_reviews')
                            ->whereColumn('note_reviews.note_id', 'notes.id');
                    }, 'avg_rating')
                        ->orderByDesc('avg_rating'),
                    'popular' => $query->withCount('transactions')
                        ->selectSub(function ($q) {
                            $q->selectRaw('COALESCE(AVG(rating), 0)')
                                ->from('note_reviews')
                                ->whereColumn('note_reviews.note_id', 'notes.id');
                        }, 'avg_rating')
                        ->selectSub(function ($q) {
                            $q->selectRaw('COUNT(*)')
                                ->from('note_reviews')
                                ->whereColumn('note_reviews.note_id', 'notes.id');
                        }, 'total_reviews_count')
                        ->orderByDesc('transactions_count')
                        ->orderByDesc('avg_rating')
                        ->orderByDesc('total_reviews_count'),
                    'trending' => $query->withCount(['transactions' => function ($q) {
                        $q->where('created_at', '>=', now()->subDays(7));
                    }])
                        ->orderByDesc('transactions_count')
                        ->orderByDesc('created_at'),
                    'newest' => $query->latest(),
                    'oldest' => $query->oldest(),
                    default => $query->latest(),
                };
            }, function ($query) {
                return $query->latest();
            });

        // Cache tags for 1 hour (they don't change frequently)
        $tags = Cache::remember('marketplace_tags_list', 3600, function () {
            return Tag::withCount('notes')
                ->having('notes_count', '>', 0)
                ->orderBy('name')
                ->get(['id', 'name']);
        });

        // Paginate notes (don't cache paginated results as they change frequently)
        $notes = $notesQuery->paginate(12)->withQueryString();

        // Save search history if user is authenticated or track by IP
        if ($request->search || $request->hasAny(['author', 'date_from', 'date_to', 'min_rating', 'seller_verified', 'seller_type', 'file_type', 'tags', 'languages'])) {
            $this->saveSearchHistory($request, $notes->total());
        }

        // Track affiliate link click if ref parameter exists
        if ($request->has('ref')) {
            try {
                $affiliateService = app(\App\Services\AffiliateService::class);
                $affiliateService->trackClick(
                    $request->get('ref'),
                    request()->ip(),
                    request()->userAgent()
                );
                // Store affiliate code in session
                session(['affiliate_code' => $request->get('ref')]);
            } catch (\Exception $e) {
                // Log error but don't fail the page load
                logger()->error('Affiliate click tracking failed', [
                    'error' => $e->getMessage(),
                    'ref' => $request->get('ref'),
                ]);
            }
        }

        // Get search history for authenticated users (last 10)
        $searchHistory = [];
        if (auth()->check()) {
            $searchHistory = SearchHistory::where('user_id', auth()->id())
                ->orderBy('searched_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Get saved searches for authenticated users
        $savedSearches = [];
        if (auth()->check()) {
            $savedSearches = SavedSearch::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('marketplace.index', compact('notes', 'tags', 'featuredNotes', 'featuredBanner', 'searchHistory', 'savedSearches'));
    }

    /**
     * Search autocomplete endpoint
     */
    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Search notes
        $notes = Note::publicOnly()
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('summary', 'like', '%' . $query . '%');
            })
            ->with(['user', 'tags'])
            ->limit(5)
            ->get()
            ->map(function ($note) {
                return [
                    'type' => 'note',
                    'id' => $note->id,
                    'title' => $note->title,
                    'url' => route('marketplace.show', $note),
                    'price' => $note->price > 0 ? currency((float) $note->price) : 'Gratis',
                    'author' => $note->user->name,
                    'thumbnail' => $note->hasThumbnails() ? Storage::url($note->thumbnails[0]) : null,
                ];
            });

        // Search tags
        $tags = Tag::where('name', 'like', '%' . $query . '%')
            ->withCount('notes')
            ->having('notes_count', '>', 0)
            ->limit(3)
            ->get()
            ->map(function ($tag) {
                return [
                    'type' => 'tag',
                    'id' => $tag->id,
                    'title' => $tag->name,
                    'url' => route('marketplace.index', ['tag' => $tag->id]),
                    'count' => $tag->notes_count,
                ];
            });

        return response()->json([
            'notes' => $notes,
            'tags' => $tags,
        ]);
    }

    /**
     * Get file extensions for a file type category
     */
    private function getExtensionsForFileType(string $fileType): array
    {
        return match ($fileType) {
            'pdf' => ['.pdf'],
            'doc' => ['.doc', '.docx'],
            'xls' => ['.xls', '.xlsx', '.csv'],
            'ppt' => ['.ppt', '.pptx'],
            'txt' => ['.txt'],
            'zip' => ['.zip', '.rar', '.7z', '.tar', '.gz'],
            'image' => ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg'],
            'video' => ['.mp4', '.avi', '.mov', '.wmv', '.flv', '.mkv'],
            'audio' => ['.mp3', '.wav', '.ogg', '.flac', '.m4a'],
            'code' => ['.js', '.jsx', '.ts', '.tsx', '.php', '.py', '.java', '.cpp', '.c', '.html', '.css', '.scss', '.vue', '.json', '.xml'],
            default => [],
        };
    }

    public function show(Request $request, Note $note, NoteShareService $noteShareService): View
    {
        // Track share referral click if ref parameter exists
        if ($request->has('ref')) {
            $referralToken = $request->input('ref');
            $noteShareService->trackClick($referralToken);

            // Store in session for purchase tracking
            $request->session()->put('share_referral_token', $referralToken);
        }

        if (!$note->is_public || $note->status !== 'active') {
            $note->load('user');

            $pendingReports = $note->reports()
                ->where('status', 'pending')
                ->count();

            $isOwner = auth()->check() && auth()->id() === $note->user_id;

            return view('marketplace.note-unavailable', [
                'note' => $note,
                'pendingReports' => $pendingReports,
                'isOwner' => $isOwner,
                'status' => $note->status,
                'isPublic' => $note->is_public,
            ]);
        }

        // Optimize eager loading with specific columns
        $note->load([
            'tags:id,name',
            'user:id,name,username,avatar,bio',
            'user.badges:id,name,icon,color,category',
            'originalCreator:id,name,username,avatar',
            'reviews' => function ($q) {
                $q->select('id', 'note_id', 'user_id', 'rating', 'comment', 'created_at')
                    ->with('user:id,name,username,avatar');
            },
            'transactions' => function ($q) {
                $q->select('id', 'note_id', 'buyer_id', 'seller_id', 'amount', 'status', 'created_at')
                    ->where('status', 'success')
                    ->limit(10);
            },
            'comments' => function ($q) {
                $q->select('id', 'note_id', 'user_id', 'content', 'created_at')
                    ->with('user:id,name,username,avatar')
                    ->latest()
                    ->limit(20);
            },
            'reactions' => function ($q) {
                $q->select('id', 'note_id', 'user_id', 'reaction_type', 'created_at')
                    ->with('user:id,name,username,avatar')
                    ->latest()
                    ->limit(50);
            },
            'questions' => function ($q) {
                $q->select('id', 'note_id', 'user_id', 'question', 'answer', 'created_at', 'helpful_count')
                    ->with('user:id,name,username,avatar')
                    ->latest()
                    ->limit(20);
            },
            'viewHistory' => function ($q) {
                $q->select('id', 'note_id', 'viewed_at')
                    ->where('viewed_at', '>=', now()->subDays(7));
            }
        ]);

        // Track impression for featured notes (track for all users, not just authenticated)
        $featuredNote = \App\Models\FeaturedNote::where('note_id', $note->id)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if ($featuredNote) {
            $featuredNote->incrementImpressions();
        }

        // Track abandoned cart for email campaigns
        if ($note->price > 0) {
            $this->emailCampaignService->trackAbandonedCart(
                auth()->user(),
                $note,
                auth()->check() ? null : $request->input('email'),
                request()->ip()
            );
        }

        // Track note view history for all users (for viral/hot badge calculation)
        // For authenticated users, track with user_id
        // For guests, track with IP address only
        $userId = auth()->check() ? auth()->id() : null;

        // Prevent duplicate views: check if same user/IP viewed in last hour
        $oneHourAgo = now()->subHour();
        $existingView = \App\Models\NoteViewHistory::where('note_id', $note->id)
            ->where('viewed_at', '>=', $oneHourAgo);

        if ($userId) {
            $existingView->where('user_id', $userId);
        } else {
            $existingView->whereNull('user_id')
                ->where('ip_address', request()->ip());
        }

        $existingView = $existingView->first();

        if (!$existingView) {
            // Get analytics tracking data
            $analyticsService = app(\App\Services\AnalyticsTrackingService::class);
            $trafficSource = $analyticsService->detectTrafficSource($request);
            $utmParams = $analyticsService->getUtmParameters($request);
            $geographicData = $analyticsService->getGeographicData(request()->ip());
            $hour = $analyticsService->getHour();

            \App\Models\NoteViewHistory::create([
                'user_id' => $userId,
                'note_id' => $note->id,
                'viewed_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'traffic_source' => $trafficSource,
                'referrer_url' => $request->header('referer'),
                'utm_source' => $utmParams['utm_source'],
                'utm_medium' => $utmParams['utm_medium'],
                'utm_campaign' => $utmParams['utm_campaign'],
                'country_code' => $geographicData['country_code'],
                'country_name' => $geographicData['country_name'],
                'city' => $geographicData['city'],
                'region' => $geographicData['region'],
                'hour' => $hour,
            ]);
        }

        // Process view monetization for free notes (0.01 rupiah per view)
        if ($note->price == 0) {
            $monetizationService = app(\App\Services\NoteViewMonetizationService::class);
            // Get fingerprint from session or generate from request data
            $fingerprint = session('browser_fingerprint') ?? request()->header('X-Fingerprint') ?? null;

            // Process view revenue (with bot protection)
            $monetizationService->processView(
                $note,
                request()->ip(),
                request()->userAgent(),
                $fingerprint,
                auth()->id()
            );
        }

        // Load reviews with replies
        $reviews = $note->reviews()
            ->with([
                'user',
                'replies',
            ])
            ->latest()
            ->paginate(10);

        // Load comments with replies
        $comments = $note->comments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(10, ['*'], 'comments');

        // Load reactions summary
        $reactionsSummary = $note->reactions()
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->get()
            ->pluck('count', 'reaction_type')
            ->toArray();

        // Get related notes (same ecosystem category or same tags, exclude current note) - optimized with caching
        $relatedNotesCacheKey = 'related_notes_' . $note->id;
        $relatedNotes = Cache::remember($relatedNotesCacheKey, 1800, function () use ($note) {
            return Note::publicOnly()
                ->where('id', '!=', $note->id)
                ->where('status', 'active')
                ->where(function ($query) use ($note) {
                    // Same ecosystem category
                    if ($note->ecosystem_category) {
                        $query->where('ecosystem_category', $note->ecosystem_category);
                    }
                    // OR same tags
                    if ($note->tags->count() > 0) {
                        $tagIds = $note->tags->pluck('id');
                        $query->orWhereHas('tags', function ($q) use ($tagIds) {
                            $q->whereIn('tags.id', $tagIds);
                        });
                    }
                })
                ->select([
                    'id',
                    'user_id',
                    'title',
                    'summary',
                    'price',
                    'discount_price',
                    'thumbnails',
                    'ecosystem_category',
                    'language',
                    'status',
                    'is_public',
                    'created_at'
                ])
                ->selectSub(function ($q) {
                    $q->selectRaw('COALESCE(AVG(rating), 0)')
                        ->from('note_reviews')
                        ->whereColumn('note_reviews.note_id', 'notes.id');
                }, 'avg_rating')
                ->with(['tags:id,name', 'user:id,name,username,avatar'])
                ->withCount('transactions')
                ->orderByDesc('transactions_count')
                ->orderByDesc('avg_rating')
                ->limit(6)
                ->get();
        });

        // Get user's reaction if authenticated
        $userReaction = null;
        if (auth()->check()) {
            $userReaction = $note->reactions()
                ->where('user_id', auth()->id())
                ->first();
        }

        // Load questions with answers
        $questions = $note->questions()
            ->with(['user', 'answeredBy'])
            ->latest()
            ->paginate(10, ['*'], 'questions');

        $canBuy = false;
        $alreadyPurchased = false;
        $canReview = false;
        $userReview = null;
        $showFullContent = false; // For content protection

        if (auth()->check()) {
            $user = auth()->user();

            // Check if user owns this note (current owner - only current owner can access)
            $isNoteOwner = $user->id === $note->user_id;

            // Check if user has ever purchased this note (for checking if they can buy again)
            // Note: Once sold, buyer loses access - only current owner has access
            $existingTransaction = Transaction::where('buyer_id', auth()->id())
                ->where('note_id', $note->id)
                ->where('status', 'success')
                ->first();
            $alreadyPurchased = $existingTransaction !== null;

            // IMPORTANT: Only current owner can access full content
            // Buyer who sold the note loses access - it's a one-time sale
            $canBuy = false;
            // Admin can buy (admin has all access)
            if (($user->role === 'buyer' || $user->hasRole('admin')) && !$isNoteOwner) {
                // Buyer/Admin can buy if they haven't purchased it before
                $canBuy = !$alreadyPurchased && $note->price > 0;
                // Only show full content if they are current owner (not if they purchased before but sold it)
                $showFullContent = false; // Buyer who doesn't own can't see full content

                // Check if can review (only if they are current owner)
                if ($isNoteOwner) {
                    $userReview = $note->reviews()->where('user_id', auth()->id())->first();
                    $canReview = $userReview === null;
                }
            } elseif ($isNoteOwner) {
                // Current owner (seller, buyer, or admin who owns it) can see full content
                $showFullContent = true;

                // Check if can review (if buyer/admin owns it)
                if ($user->role === 'buyer' || $user->hasRole('admin')) {
                    $userReview = $note->reviews()->where('user_id', auth()->id())->first();
                    $canReview = $userReview === null;
                }
            } else {
                // Seller viewing other seller's note - can't buy, can see preview (except admin)
                $showFullContent = $note->price == 0 || $user->hasRole('admin');
                // Admin can buy even if they are seller
                if ($user->hasRole('admin') && !$isNoteOwner) {
                    $canBuy = !$alreadyPurchased && $note->price > 0;
                }
            }
        } else {
            // Guest users can only see preview (for paid notes)
            $showFullContent = $note->price == 0;
        }

        // Pass additional info for view
        $isNoteOwner = auth()->check() && auth()->id() === $note->user_id;
        $hasPurchasedBefore = $alreadyPurchased ?? false;

        // Calculate base price (with note discount if any)
        $basePrice = $note->hasDiscount() ? $note->discount_price : $note->price;

        // Calculate subscription discount if user has active subscription
        $subscriptionDiscount = 0;
        $subscriptionDiscountPrice = $basePrice;
        $activeSubscription = null;

        if (auth()->check() && $note->price > 0) {
            $activeSubscription = auth()->user()->activeBuyerSubscription();
            if ($activeSubscription) {
                $subscriptionDiscount = auth()->user()->getSubscriptionDiscount();
                if ($subscriptionDiscount > 0) {
                    $subscriptionDiscountPrice = $basePrice * (1 - ($subscriptionDiscount / 100));
                }
            }
        }

        // Check repurchase info for scarcity mode
        $canRepurchase = false;
        $repurchasePrice = null;
        $gracePeriodEndsAt = null;
        $isWithinGracePeriod = false;

        if (auth()->check() && $note->isScarcityMode() && $hasPurchasedBefore && !$isNoteOwner) {
            $canRepurchase = $note->canRepurchase(auth()->id());
            if ($canRepurchase) {
                $repurchasePrice = $note->getRepurchasePrice(auth()->id());
                $existingTransaction = Transaction::where('buyer_id', auth()->id())
                    ->where('note_id', $note->id)
                    ->where('status', 'success')
                    ->first();
                if ($existingTransaction && $existingTransaction->grace_period_ends_at) {
                    $gracePeriodEndsAt = $existingTransaction->grace_period_ends_at;
                    $isWithinGracePeriod = $gracePeriodEndsAt->isFuture();
                }
            }
        }

        $conversation = null;
        if (auth()->check()) {
            $conversation = NoteConversation::with(['buyer', 'seller', 'latestMessage.sender'])
                ->where('note_id', $note->id)
                ->where(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                })
                ->orderByDesc('updated_at')
                ->first();
        }

        $sellerReviewStats = [
            'average' => 0,
            'count' => 0,
        ];

        if ($note->user) {
            $sellerReviewStats = $note->user->sellerReviewStats();
        }

        $taxPreview = null;
        if ($basePrice > 0) {
            $taxService = app(TaxService::class);
            $taxContext = $taxService->resolveTaxForPurchase($note, auth()->user());
            $taxPreview = array_merge(
                $taxService->calculateAmounts((float) $basePrice, $taxContext),
                ['country_code' => $taxContext['country_code'] ?? null]
            );
        }

        // Generate share URL for authenticated users
        $shareUrl = null;
        if (auth()->check()) {
            $shareUrl = $noteShareService->generateShareUrl($note, auth()->user());
        }

        return view('marketplace.show', compact(
            'subscriptionDiscount',
            'subscriptionDiscountPrice',
            'activeSubscription',
            'relatedNotes',
            'note',
            'canBuy',
            'alreadyPurchased',
            'reviews',
            'canReview',
            'userReview',
            'showFullContent',
            'isNoteOwner',
            'hasPurchasedBefore',
            'basePrice',
            'conversation',
            'sellerReviewStats',
            'taxPreview',
            'canRepurchase',
            'repurchasePrice',
            'gracePeriodEndsAt',
            'isWithinGracePeriod',
            'comments',
            'reactionsSummary',
            'userReaction',
            'questions',
            'shareUrl'
        ));
    }

    public function purchase(Request $request, Note $note, ReferralService $referralService, TaxService $taxService, CommissionService $commissionService, NoteShareService $noteShareService, \App\Services\AffiliateService $affiliateService): RedirectResponse
    {
        if (!$note->is_public || $note->status !== 'active') {
            return redirect()->route('marketplace.index')->with('error', 'Catatan tidak tersedia untuk dibeli.');
        }

        $buyer = auth()->user();
        $seller = $note->user;

        // Check if user is buyer (middleware already checks, but double check for security)
        if ($buyer->role !== 'buyer' && !$buyer->hasRole('admin')) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Fitur ini hanya tersedia untuk Buyer. Seller tidak dapat membeli note. Jika ingin membeli, silakan buat akun Buyer dengan email berbeda.');
        }

        // Buyer cannot buy their own note (but they can resell it if they own it)
        if ($buyer->id === $seller->id) {
            return redirect()->route('marketplace.show', $note)->with('error', 'Anda tidak dapat membeli catatan Anda sendiri. Jika Anda adalah pemilik note ini, Anda dapat menjualnya ke buyer lain.');
        }

        // Use DB transaction with lock to prevent race conditions
        try {
            DB::beginTransaction();

            // Lock the note row to prevent concurrent purchases (especially for scarcity mode)
            $note = Note::lockForUpdate()->find($note->id);

            if (!$note) {
                DB::rollBack();
                return redirect()->route('marketplace.index')->with('error', 'Catatan tidak ditemukan.');
            }

            // Re-check status after lock (might have been sold during request)
            if (!$note->is_public || $note->status !== 'active' || !$note->is_for_sale) {
                DB::rollBack();
                return redirect()->route('marketplace.show', $note)->with('error', 'Catatan tidak tersedia untuk dibeli.');
            }

            // Handle different sale modes
            if ($note->isStandardMode()) {
                // Standard mode: Multiple sales allowed, buyer cannot resell, no commission
                // Check if buyer already purchased (can buy multiple times from different sellers)
                // But can't buy from same seller twice
                $existingTransaction = Transaction::where('buyer_id', $buyer->id)
                    ->where('note_id', $note->id)
                    ->where('seller_id', $seller->id)
                    ->where('status', 'success')
                    ->first();

                if ($existingTransaction) {
                    DB::rollBack();
                    return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah membeli catatan ini dari penjual ini sebelumnya.');
                }
            } else {
                // Scarcity mode: One-time purchase per user, but can repurchase if sold
                // Check if note is already sold (lock ensures we see latest state)
                if ($note->is_sold && $note->user_id !== $buyer->id) {
                    DB::rollBack();
                    return redirect()->route('marketplace.show', $note)->with('error', 'Catatan ini sudah terjual. Setiap note scarcity hanya bisa dibeli 1x.');
                }

                $existingTransaction = Transaction::where('buyer_id', $buyer->id)
                    ->where('note_id', $note->id)
                    ->where('status', 'success')
                    ->first();

                if ($existingTransaction) {
                    // Check if buyer still owns the note (hasn't sold it yet)
                    if ($buyer->id === $note->user_id) {
                        DB::rollBack();
                        return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah memiliki catatan ini. Anda dapat menjualnya ke buyer lain, tetapi ingat bahwa setelah dijual, Anda tidak akan bisa mengaksesnya lagi.');
                    } else {
                        // Buyer sold the note - check if can repurchase
                        if ($note->canRepurchase($buyer->id)) {
                            // Can repurchase - will use repurchase price below
                            $repurchasePrice = $note->getRepurchasePrice($buyer->id);
                            if ($repurchasePrice) {
                                // Will use repurchase price instead of base price
                                $basePrice = $repurchasePrice;
                            } else {
                                DB::rollBack();
                                return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah membeli dan menjual catatan ini sebelumnya. Setiap user hanya bisa membeli note ini 1x. Setelah dijual, akses hilang secara permanen.');
                            }
                        } else {
                            DB::rollBack();
                            return redirect()->route('marketplace.show', $note)->with('error', 'Anda sudah membeli dan menjual catatan ini sebelumnya. Setiap user hanya bisa membeli note ini 1x. Setelah dijual, akses hilang secara permanen.');
                        }
                    }
                }
            }

            // Get final price (use discount_price if available, otherwise use regular price)
            // If repurchasing, basePrice already set above in scarcity mode check
            if (!isset($basePrice)) {
                $basePrice = $note->hasDiscount() ? $note->discount_price : $note->price;
            }

            // Apply subscription discount if user has active subscription
            $finalPrice = $basePrice;
            $subscriptionDiscount = 0;
            $activeSubscription = $buyer->activeBuyerSubscription();

            if ($activeSubscription && $basePrice > 0) {
                $subscriptionDiscount = $buyer->getSubscriptionDiscount();
                if ($subscriptionDiscount > 0) {
                    $finalPrice = $basePrice * (1 - ($subscriptionDiscount / 100));
                }
            }

            if ($finalPrice <= 0) {
                DB::rollBack();
                return redirect()->route('marketplace.show', $note)->with('error', 'Catatan ini gratis, tidak perlu dibeli.');
            }

            $taxContext = $taxService->resolveTaxForPurchase($note, $buyer);
            $taxBreakdown = $taxService->calculateAmounts((float) $finalPrice, $taxContext);
            $buyerPaysAmount = $taxBreakdown['total_amount'];
            $priceExcludingTax = $taxBreakdown['price_excluding_tax'];

            if ($buyerPaysAmount <= 0) {
                DB::rollBack();
                return redirect()->route('marketplace.show', $note)->with('error', 'Catatan ini gratis, tidak perlu dibeli.');
            }

            // Ensure wallets exist
            $baseCurrency = config('currency.base_currency', 'IDR');

            $buyerWallet = Wallet::firstOrCreate(
                ['user_id' => $buyer->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            if ($buyerWallet->currency !== $baseCurrency) {
                $buyerWallet->currency = $baseCurrency;
                $buyerWallet->save();
            }

            $sellerWallet = Wallet::firstOrCreate(
                ['user_id' => $seller->id],
                ['balance' => 0, 'currency' => $baseCurrency]
            );
            if ($sellerWallet->currency !== $baseCurrency) {
                $sellerWallet->currency = $baseCurrency;
                $sellerWallet->save();
            }

            // Sync wallet balance with user wallet_balance
            if ($buyerWallet->balance != $buyer->wallet_balance) {
                $buyerWallet->balance = $buyer->wallet_balance;
                $buyerWallet->save();
            }

            if ($buyerWallet->balance < $buyerPaysAmount) {
                DB::rollBack();
                return redirect()->route('marketplace.show', $note)->with('error', 'Saldo wallet tidak cukup. Silakan top-up terlebih dahulu.');
            }

            $notificationData = [
                'purchase' => null,
                'sale' => null,
                'commission' => [],
                'low_balance' => [],
                'popularity_check' => false,
            ];

            $commissionTier = $commissionService->resolveTierForSeller($seller);

            $amount = $buyerPaysAmount;

            // Handle commission based on sale mode
            $platformFee = 0;
            $creatorCommission = 0;
            $originalCreator = null;
            $sellerAmount = $priceExcludingTax;

            if ($note->isStandardMode()) {
                // Standard mode: No commission, seller gets full amount (minus tax)
                // No original creator commission
            } else {
                // Scarcity mode: Apply commission as usual
                // Get commission rates based on seller tier (fallback to settings)
                $basePlatformCommissionPercent = $commissionTier?->platform_fee_percent ?? Setting::getSetting('platform_commission_percent', 'marketplace', 20);

                // Apply level discount to platform commission
                $levelService = app(\App\Services\LevelService::class);
                $levelDiscount = $levelService->getCommissionDiscount($seller);
                $platformCommissionPercent = max(0, $basePlatformCommissionPercent - $levelDiscount);

                $creatorCommissionPercent = $commissionTier?->creator_commission_percent ?? Setting::getSetting('creator_commission_percent', 'marketplace', 0);

                // Platform fee (always deducted from every transaction)
                $platformFee = $priceExcludingTax * ($platformCommissionPercent / 100);

                // Original creator commission (always for original creator in every transaction)
                // Original creator gets commission every time the note is sold, regardless of seller
                // If note doesn't have original_creator_id set, use the first seller (from first transaction)
                // or fallback to current seller if no transactions exist
                if ($note->original_creator_id) {
                    $originalCreator = $note->originalCreator;
                } else {
                    // Find original creator from first transaction
                    $firstTransaction = Transaction::where('note_id', $note->id)
                        ->where('status', 'success')
                        ->orderBy('created_at', 'asc')
                        ->first();

                    if ($firstTransaction && $firstTransaction->original_creator_id) {
                        $originalCreator = User::find($firstTransaction->original_creator_id);
                    } else {
                        // No previous transaction - current seller is original creator
                        // But check if note was created by a seller (not a buyer reselling)
                        if ($seller->role === 'seller') {
                            $originalCreator = $seller;
                        } else {
                            // Buyer is selling - find original creator from their purchase transaction
                            $buyerPurchase = Transaction::where('buyer_id', $seller->id)
                                ->where('note_id', $note->id)
                                ->where('status', 'success')
                                ->first();

                            if ($buyerPurchase && $buyerPurchase->original_creator_id) {
                                $originalCreator = User::find($buyerPurchase->original_creator_id);
                            }
                        }
                    }
                }

                // If still no original creator found, use current seller (shouldn't happen, but fallback)
                if (!$originalCreator) {
                    $originalCreator = $seller;
                }

                // Set original_creator_id on note if not set (for future resells)
                if (!$note->original_creator_id) {
                    $note->original_creator_id = $originalCreator->id;
                }

                if ($originalCreator && $creatorCommissionPercent > 0) {
                    // Original creator always gets commission (if setting is > 0)
                    // Even if seller is the original creator, they still get commission separately
                    $creatorCommission = $priceExcludingTax * ($creatorCommissionPercent / 100);
                }

                // Seller gets: amount - platform_fee - creator_commission
                // If seller is original creator, they get seller amount + creator commission (total = amount - platform fee)
                $sellerAmount = $priceExcludingTax - $platformFee - $creatorCommission;
            }

            $taxAmount = $taxBreakdown['tax_amount'];

            // Deduct from buyer
            $buyerWallet->balance -= $amount;
            $buyerWallet->save();
            $buyer->wallet_balance = $buyerWallet->balance;
            $buyer->save();

            $notificationData['low_balance'][] = [
                'user_id' => $buyer->id,
                'balance' => (float) $buyer->wallet_balance,
            ];

            // Add to seller
            $sellerWallet->balance += $sellerAmount;
            $sellerWallet->save();
            $seller->wallet_balance = $sellerWallet->balance;
            $seller->save();

            // Add commission to original creator (always, if commission is set)
            // Original creator gets commission in every transaction, even if they are the seller
            if ($creatorCommission > 0 && $originalCreator) {
                // If seller is original creator, they already got seller amount, now add commission
                if ($originalCreator->id === $seller->id) {
                    // Seller is original creator: add commission to their wallet (they already got sellerAmount)
                    $sellerWallet->balance += $creatorCommission;
                    $sellerWallet->save();
                    $seller->wallet_balance = $sellerWallet->balance;
                    $seller->save();
                } else {
                    // Seller is different: original creator gets separate commission
                    $creatorWallet = Wallet::firstOrCreate(
                        ['user_id' => $originalCreator->id],
                        ['balance' => 0, 'currency' => $baseCurrency]
                    );
                    if ($creatorWallet->currency !== $baseCurrency) {
                        $creatorWallet->currency = $baseCurrency;
                    }
                    $creatorWallet->balance += $creatorCommission;
                    $creatorWallet->save();
                    $originalCreator->wallet_balance = $creatorWallet->balance;
                    $originalCreator->save();
                }
            }

            // Get or create admin wallet (platform wallet)
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $adminWallet = Wallet::firstOrCreate(
                    ['user_id' => $admin->id],
                    ['balance' => 0, 'currency' => $baseCurrency]
                );
                if ($adminWallet->currency !== $baseCurrency) {
                    $adminWallet->currency = $baseCurrency;
                }
                $adminWallet->balance += $platformFee + $taxAmount;
                $adminWallet->save();
                $admin->wallet_balance = $adminWallet->balance;
                $admin->save();
            }

            // Handle ownership transfer based on sale mode
            if ($note->isScarcityMode()) {
                // Scarcity mode: Transfer ownership to buyer (so buyer can resell it to other buyers)
                // Original creator stays in original_creator_id for commission tracking
                // This allows buyer to resell the note while original creator still gets commission
                $note->user_id = $buyer->id;
                // Ensure original_creator_id is set (should already be set above, but double check)
                if (!$note->original_creator_id && $originalCreator) {
                    $note->original_creator_id = $originalCreator->id;
                }
            } else {
                // Standard mode: Keep ownership with seller, buyer cannot resell
                // Don't transfer ownership
            }
            $note->save();

            // Calculate grace period end date (only for scarcity mode)
            $gracePeriodEndsAt = null;
            if ($note->isScarcityMode() && $note->grace_period_days > 0) {
                $gracePeriodEndsAt = now()->addDays($note->grace_period_days);
            }

            // Check if this is a resale (buyer selling to another buyer)
            // Resale price is the price set by the seller (current owner) when they list it for sale
            $resalePrice = null;
            $soldAt = null;
            if ($note->isScarcityMode() && $seller->role === 'buyer' && $seller->id !== $originalCreator?->id) {
                // This is a resale - the seller is a buyer who bought it before
                // The resale price is the current price of the note (set by the seller)
                $resalePrice = $basePrice;
                $soldAt = now();
            }

            // Get share referral token from request or session
            $shareReferralToken = $request->input('share_ref')
                ?? $request->session()->get('share_referral_token')
                ?? null;

            // Prepare transaction notes (store share referral token if exists)
            $transactionNotes = 'Pembelian catatan: ' . $note->title;
            $transactionNotesData = [];
            if ($shareReferralToken) {
                $transactionNotesData['share_referral_token'] = $shareReferralToken;
            }

            // Create transaction record
            $transaction = Transaction::create([
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'original_creator_id' => $originalCreator ? $originalCreator->id : null,
                'note_id' => $note->id,
                'amount' => $amount,
                'resale_price' => $resalePrice,
                'sold_at' => $soldAt,
                'commission' => $platformFee, // Keep for backward compatibility
                'currency' => $baseCurrency,
                'original_amount' => $amount,
                'original_currency' => $baseCurrency,
                'exchange_rate' => 1,
                'platform_fee' => $platformFee,
                'creator_commission' => $creatorCommission,
                'commission_tier_id' => $commissionTier?->id,
                'tax_percent' => $taxBreakdown['tax_percent'],
                'tax_amount' => $taxAmount,
                'tax_inclusive' => $taxBreakdown['tax_inclusive'],
                'tax_country_code' => $taxContext['country_code'] ?? null,
                'status' => 'success',
                'payment_method' => 'wallet',
                'notes' => !empty($transactionNotesData) ? json_encode($transactionNotesData) : $transactionNotes,
                'grace_period_ends_at' => $gracePeriodEndsAt,
            ]);

            // Create note history record for sale
            \App\Models\NoteHistory::create([
                'note_id' => $note->id,
                'user_id' => $seller->id, // Seller who sold it
                'action' => 'sold',
                'old_data' => ['user_id' => $seller->id],
                'new_data' => ['user_id' => $buyer->id, 'buyer_id' => $buyer->id, 'buyer_name' => $buyer->name],
                'changes' => 'Note sold to ' . $buyer->name . ' for ' . currency($amount),
                'notes' => 'Sold by ' . $seller->name . ' to ' . $buyer->name . '. Original creator: ' . ($originalCreator ? $originalCreator->name : 'N/A'),
            ]);

            // Create purchased note record for buyer premium features
            $purchasedNote = \App\Models\PurchasedNote::create([
                'user_id' => $buyer->id,
                'note_id' => $note->id,
                'transaction_id' => $transaction->id,
                'purchase_price' => $amount,
                'purchased_at' => now(),
                'download_count' => 0,
            ]);

            // Mark abandoned cart as purchased
            try {
                $this->emailCampaignService->markAbandonedCartAsPurchased($buyer, $note);
            } catch (\Exception $e) {
                logger()->error('Failed to mark abandoned cart as purchased', [
                    'error' => $e->getMessage(),
                    'buyer_id' => $buyer->id,
                    'note_id' => $note->id,
                ]);
            }

            // Track affiliate conversion
            try {
                $affiliateService->trackConversion(
                    $buyer,
                    'purchase',
                    $transaction,
                    $purchasedNote,
                    request()->ip(),
                    request()->userAgent()
                );
            } catch (\Exception $e) {
                // Log error but don't fail the purchase
                logger()->error('Affiliate tracking failed', [
                    'error' => $e->getMessage(),
                    'buyer_id' => $buyer->id,
                    'transaction_id' => $transaction->id,
                ]);
            }

            $sellerNetAmount = $sellerAmount;
            if ($originalCreator && $originalCreator->id === $seller->id) {
                $sellerNetAmount += $creatorCommission;
            }

            $notificationData['purchase'] = [
                'buyer_id' => $buyer->id,
                'note_id' => $note->id,
                'amount' => $amount,
                'transaction_id' => $transaction->id,
                'breakdown' => [
                    'subtotal' => $priceExcludingTax,
                    'tax_amount' => $taxAmount,
                    'tax_percent' => $taxBreakdown['tax_percent'],
                    'tax_inclusive' => $taxBreakdown['tax_inclusive'],
                    'platform_fee_percent' => $platformCommissionPercent,
                    'creator_commission_percent' => $creatorCommissionPercent,
                    'total' => $amount,
                    'currency' => $baseCurrency,
                    'commission_tier' => $commissionTier?->name,
                ],
            ];

            $notificationData['sale'] = [
                'seller_id' => $seller->id,
                'note_id' => $note->id,
                'amount' => $amount,
                'buyer_name' => $buyer->name,
                'transaction_id' => $transaction->id,
                'breakdown' => [
                    'subtotal' => $priceExcludingTax,
                    'tax_amount' => $taxAmount,
                    'tax_percent' => $taxBreakdown['tax_percent'],
                    'tax_inclusive' => $taxBreakdown['tax_inclusive'],
                    'platform_fee' => $platformFee,
                    'creator_commission' => $creatorCommission,
                    'platform_fee_percent' => $platformCommissionPercent,
                    'creator_commission_percent' => $creatorCommissionPercent,
                    'net_amount' => $sellerNetAmount,
                    'total' => $amount,
                    'currency' => $baseCurrency,
                    'commission_tier' => $commissionTier?->name,
                ],
            ];

            if ($creatorCommission > 0 && $originalCreator && $originalCreator->id !== $seller->id) {
                $notificationData['commission'][] = [
                    'creator_id' => $originalCreator->id,
                    'note_id' => $note->id,
                    'amount' => $creatorCommission,
                    'seller_id' => $seller->id,
                ];
            }

            $notificationData['popularity_check'] = true;

            NoteConversation::updateOrCreate(
                [
                    'note_id' => $note->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $seller->id,
                ],
                [
                    'last_message_at' => now(),
                ]
            );

            DB::commit();

            // Check and award badges for seller
            $achievementService = app(\App\Services\AchievementService::class);
            $achievementService->checkSalesBadges($seller);
            $achievementService->checkQualityBadges($seller);

            // Check and assign seller level
            try {
                $levelService = app(\App\Services\LevelService::class);
                $levelService->checkSellerLevel($seller);
            } catch (\Exception $e) {
                logger()->error('Failed to check seller level', [
                    'seller_id' => $seller->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Check and assign buyer level
            try {
                $levelService = app(\App\Services\LevelService::class);
                $levelService->checkBuyerLevel($buyer);
            } catch (\Exception $e) {
                logger()->error('Failed to check buyer level', [
                    'buyer_id' => $buyer->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Award points for purchase (buyer)
            try {
                $pointsService = app(\App\Services\PointsService::class);
                $pointsService->awardPurchasePoints($buyer, $transaction);
            } catch (\Exception $e) {
                logger()->error('Failed to award purchase points', [
                    'buyer_id' => $buyer->id,
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Process transaction reward for referral (outside transaction to avoid deadlock)
            if ($transaction) {
                try {
                    $referralService->processTransactionReward($transaction);
                } catch (\Exception $e) {
                    // Log error but don't fail the purchase
                    logger()->error('Failed to process transaction reward', [
                        'transaction_id' => $transaction->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Process share commission (outside transaction to avoid deadlock)
                try {
                    $noteShareService->processShareCommission($transaction, $shareReferralToken);
                } catch (\Exception $e) {
                    // Log error but don't fail the purchase
                    logger()->error('Failed to process share commission', [
                        'transaction_id' => $transaction->id,
                        'share_referral_token' => $shareReferralToken,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $note = $note->fresh(['user']);

            // Auto-approve monetization for free notes if seller has at least 1 sale
            if ($note->price == 0) {
                $note->checkAndAutoApproveMonetization();
            }

            if (isset($notificationData['purchase']) && is_array($notificationData['purchase'])) {
                $buyerForNotification = User::find($notificationData['purchase']['buyer_id'] ?? null);
                if ($buyerForNotification) {
                    $this->notificationService->notifyPurchase(
                        $buyerForNotification,
                        $note,
                        $notificationData['purchase']['amount'],
                        $notificationData['purchase']['transaction_id'],
                        $notificationData['purchase']['breakdown'] ?? []
                    );
                }
            }

            if (isset($notificationData['sale']) && is_array($notificationData['sale'])) {
                $sellerForNotification = User::find($notificationData['sale']['seller_id'] ?? null);
                if ($sellerForNotification) {
                    $this->notificationService->notifySale(
                        $sellerForNotification,
                        $note,
                        $notificationData['sale']['amount'],
                        $notificationData['sale']['buyer_name'],
                        $notificationData['sale']['breakdown'] ?? []
                    );
                }
            }

            if (isset($notificationData['commission']) && is_array($notificationData['commission']) && !empty($notificationData['commission'])) {
                foreach ($notificationData['commission'] as $commissionData) {
                    $creator = User::find($commissionData['creator_id']);
                    $commissionSeller = User::find($commissionData['seller_id']);

                    if ($creator) {
                        $this->notificationService->notifyCreatorCommission(
                            $creator,
                            $note,
                            $commissionData['amount'],
                            $commissionSeller
                        );
                    }
                }
            }

            if (!empty($notificationData['low_balance'])) {
                foreach ($notificationData['low_balance'] as $lowBalance) {
                    $lowUser = User::find($lowBalance['user_id']);
                    if ($lowUser) {
                        $this->notificationService->maybeNotifyLowBalance($lowUser, $lowBalance['balance']);
                    }
                }
            }

            if ($notificationData['popularity_check']) {
                // Dispatch popularity check to queue for async processing
                \App\Jobs\CheckNotePopularityJob::dispatch($note->id)
                    ->onQueue('notifications');
            }

            // Track click for featured notes
            $featuredNote = \App\Models\FeaturedNote::where('note_id', $note->id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if ($featuredNote) {
                $featuredNote->incrementClicks();
            }

            return redirect()->route('marketplace.show', $note)
                ->with('success', 'Catatan berhasil dibeli! Anda dapat melihat detail lengkapnya.');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Note purchase failed', [
                'note_id' => $note->id ?? null,
                'buyer_id' => $buyer->id ?? null,
                'seller_id' => $seller->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('marketplace.show', $note)
                ->with('error', 'Terjadi kesalahan saat memproses pembelian. Silakan coba lagi.');
        }
    }

    /**
     * Apply advanced search with boolean operators (AND, OR, NOT)
     */
    private function applyAdvancedSearch($query, string $searchQuery, Request $request)
    {
        // Check if query contains boolean operators
        $hasBooleanOperators = preg_match('/\b(AND|OR|NOT)\b/i', $searchQuery);

        if ($hasBooleanOperators) {
            // Parse boolean query
            $terms = $this->parseBooleanQuery($searchQuery);
            return $query->where(function ($q) use ($terms) {
                $this->buildBooleanQuery($q, $terms);
            });
        } else {
            // Simple search (full-text search in title, summary, content)
            return $query->where(function ($q) use ($searchQuery) {
                $q->where('title', 'like', '%' . $searchQuery . '%')
                    ->orWhere('summary', 'like', '%' . $searchQuery . '%')
                    ->orWhere('content', 'like', '%' . $searchQuery . '%');
            });
        }
    }

    /**
     * Parse boolean query into terms and operators
     */
    private function parseBooleanQuery(string $query): array
    {
        // Normalize query: convert to uppercase for operators, preserve case for terms
        $query = preg_replace('/\b(AND|OR|NOT)\b/i', '|$1|', $query);
        $parts = explode('|', $query);

        $terms = [];
        $currentOperator = 'OR'; // Default operator

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) continue;

            $upperPart = strtoupper($part);
            if (in_array($upperPart, ['AND', 'OR', 'NOT'])) {
                $currentOperator = $upperPart;
            } else {
                $terms[] = [
                    'term' => $part,
                    'operator' => $currentOperator,
                ];
                $currentOperator = 'OR'; // Reset to default after term
            }
        }

        return $terms;
    }

    /**
     * Build query from parsed boolean terms
     */
    private function buildBooleanQuery($query, array $terms)
    {
        if (empty($terms)) return;

        $firstTerm = true;

        foreach ($terms as $termData) {
            $term = trim($termData['term']);
            $operator = $termData['operator'];

            if (empty($term)) continue;

            if ($firstTerm) {
                // First term - always use where
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', '%' . $term . '%')
                        ->orWhere('summary', 'like', '%' . $term . '%')
                        ->orWhere('content', 'like', '%' . $term . '%');
                });
                $firstTerm = false;
            } else {
                switch ($operator) {
                    case 'AND':
                        $query->where(function ($q) use ($term) {
                            $q->where('title', 'like', '%' . $term . '%')
                                ->orWhere('summary', 'like', '%' . $term . '%')
                                ->orWhere('content', 'like', '%' . $term . '%');
                        });
                        break;
                    case 'OR':
                        $query->orWhere(function ($q) use ($term) {
                            $q->where('title', 'like', '%' . $term . '%')
                                ->orWhere('summary', 'like', '%' . $term . '%')
                                ->orWhere('content', 'like', '%' . $term . '%');
                        });
                        break;
                    case 'NOT':
                        $query->where(function ($q) use ($term) {
                            $q->where('title', 'not like', '%' . $term . '%')
                                ->where('summary', 'not like', '%' . $term . '%')
                                ->where('content', 'not like', '%' . $term . '%');
                        });
                        break;
                }
            }
        }
    }

    /**
     * Save search history
     */
    private function saveSearchHistory(Request $request, int $resultCount): void
    {
        try {
            $filters = $request->only([
                'search',
                'author',
                'date_from',
                'date_to',
                'min_rating',
                'seller_verified',
                'seller_type',
                'file_type',
                'tags',
                'languages',
                'ecosystem',
                'min_price',
                'max_price',
                'sort'
            ]);

            // Remove empty filters
            $filters = array_filter($filters, function ($value) {
                return !empty($value);
            });

            SearchHistory::create([
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'query' => $request->search ?? '',
                'filters' => $filters,
                'result_count' => $resultCount,
                'searched_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            logger()->error('Failed to save search history', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Save search query
     */
    public function saveSearch(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'query' => 'nullable|string',
        ]);

        $filters = $request->only([
            'search',
            'author',
            'date_from',
            'date_to',
            'min_rating',
            'seller_verified',
            'seller_type',
            'file_type',
            'tags',
            'languages',
            'ecosystem',
            'min_price',
            'max_price',
            'sort'
        ]);

        $filters = array_filter($filters, function ($value) {
            return !empty($value);
        });

        $savedSearch = SavedSearch::create([
            'user_id' => auth()->id(),
            'name' => $request->name ?? 'Saved Search ' . now()->format('Y-m-d H:i'),
            'query' => $request->query ?? $request->search ?? '',
            'filters' => $filters,
            'result_count' => 0, // Will be updated when search is executed
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search saved successfully',
            'saved_search' => $savedSearch,
        ]);
    }

    /**
     * Delete saved search
     */
    public function deleteSavedSearch(SavedSearch $savedSearch)
    {
        if ($savedSearch->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $savedSearch->delete();

        return response()->json([
            'success' => true,
            'message' => 'Saved search deleted successfully',
        ]);
    }
}
