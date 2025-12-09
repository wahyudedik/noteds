@php
    $user = auth()->user();
    $isAdmin = $user?->hasRole('admin');
    $isSeller = $user?->role === 'seller';
    $isBuyer = $user?->role === 'buyer';
    $isSellerOrAdmin = $user && ($isSeller || $isAdmin);
    $isBuyerOrAdmin = $user && ($isBuyer || $isAdmin);

    // Build menu structure
    $menuGroups = [];

    // Home
    $menuGroups[] = [
        'title' => null,
        'items' => [
            [
                'label' => __('messages.home'),
                'href' => url('/'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
                'active' => request()->routeIs('welcome'),
            ],
        ],
    ];

    if ($user) {
        if ($user->role === 'user_workspaces') {
            $menuGroups[] = [
                'title' => null,
                'items' => [
                    [
                        'label' => __('messages.workspaces'),
                        'href' => route('workspaces.index'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
                        'active' => request()->routeIs('workspaces.*'),
                    ],
                ],
            ];
        } else {
            // Dashboard
            $menuGroups[] = [
                'title' => null,
                'items' => [
                    [
                        'label' => __('messages.dashboard'),
                        'href' => route('dashboard'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
                        'active' => request()->routeIs('dashboard'),
                    ],
                ],
            ];

            // Main Navigation
            $mainItems = [];

            if ($isSellerOrAdmin) {
                $mainItems[] = [
                    'label' => __('messages.notes'),
                    'href' => route('notes.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                    'active' => request()->routeIs('notes.*'),
                ];
            }

            $mainItems[] = [
                'label' => __('messages.workspaces'),
                'href' => route('workspaces.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
                'active' => request()->routeIs('workspaces.*'),
            ];

            $mainItems[] = [
                'label' => __('messages.wallet'),
                'href' => route('wallet.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>',
                'active' => request()->routeIs('wallet.*'),
            ];

            $mainItems[] = [
                'label' => __('messages.marketplace'),
                'href' => route('marketplace.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>',
                'active' => request()->routeIs('marketplace.*'),
            ];

            $mainItems[] = [
                'label' => 'Leaderboards',
                'href' => route('leaderboard.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" /></svg>',
                'active' => request()->routeIs('leaderboard.*'),
            ];

            $mainItems[] = [
                'label' => 'Contests',
                'href' => route('contests.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                'active' => request()->routeIs('contests.*'),
            ];

            // Studio / Marketplace for Services
            $mainItems[] = [
                'label' => 'Studio',
                'href' => route('studio.orders.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>',
                'active' => request()->routeIs('studio.orders.*'),
            ];

            if (!empty($mainItems)) {
                $menuGroups[] = [
                    'title' => null,
                    'items' => $mainItems,
                ];
            }

            // Studio specific section for vendors and buyers
            if (!$isAdmin) {
                $studioItems = [];

                // For VENDOR - show work submission and vendor dashboard
                if ($isSeller) {
                    $studioItems[] = [
                        'label' => 'My Orders',
                        'href' => route('studio.orders.index'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
                        'active' => request()->routeIs('studio.orders.*'),
                    ];

                    $studioItems[] = [
                        'label' => 'Vendor Dashboard',
                        'href' => route('vendor.index'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>',
                        'active' => request()->routeIs('vendor.*'),
                    ];
                }

                // For BUYER - show my orders and pending approvals
                if ($isBuyer) {
                    $studioItems[] = [
                        'label' => 'My Orders',
                        'href' => route('studio.orders.index'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>',
                        'active' => request()->routeIs('studio.orders.*'),
                    ];

                    $studioItems[] = [
                        'label' => 'Pending Approvals',
                        'href' => '#', // Link to work submissions awaiting approval
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        'active' => false,
                    ];

                    $studioItems[] = [
                        'label' => 'Collections',
                        'href' => route('wallet.index'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                        'active' => request()->routeIs('wallet.*'),
                    ];
                }

                if (!empty($studioItems)) {
                    $menuGroups[] = [
                        'title' => 'Studio & Services',
                        'items' => $studioItems,
                    ];
                }
            }

            // Forum with submenu
            $forumIsActive = request()->routeIs('forum.*');
            $menuGroups[] = [
                'title' => __('messages.forum'),
                'items' => [
                    [
                        'label' => __('messages.forum'),
                        'href' => route('forum.index'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                        'active' =>
                            request()->routeIs('forum.index') ||
                            (request()->routeIs('forum.*') &&
                                !request()->routeIs('forum.analytics') &&
                                !request()->routeIs('forum.preferences.*')),
                    ],
                    [
                        'label' => __('messages.analytics'),
                        'href' => route('forum.analytics'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
                        'active' => request()->routeIs('forum.analytics'),
                    ],
                    [
                        'label' => __('messages.preferences'),
                        'href' => route('forum.preferences.edit'),
                        'icon' =>
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
                        'active' => request()->routeIs('forum.preferences.*'),
                    ],
                ],
            ];

            // Seller Tools
            if ($isSellerOrAdmin) {
                $menuGroups[] = [
                    'title' => __('messages.seller_tools'),
                    'items' => [
                        [
                            'label' => __('messages.featured_notes'),
                            'href' => route('featured-notes.index'),
                            'icon' =>
                                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>',
                            'active' => request()->routeIs('featured-notes.*'),
                        ],
                    ],
                ];
            }

            // Buyer Library
            if ($isBuyerOrAdmin || $isAdmin) {
                $menuGroups[] = [
                    'title' => __('messages.my_library'),
                    'items' => [
                        [
                            'label' => __('messages.collections'),
                            'href' => route('collections.index'),
                            'icon' =>
                                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
                            'active' => request()->routeIs('collections.*'),
                        ],
                        [
                            'label' => __('messages.analytics'),
                            'href' => route('buyer-analytics.index'),
                            'icon' =>
                                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>',
                            'active' => request()->routeIs('buyer-analytics.*'),
                        ],
                        [
                            'label' => __('messages.reading_history'),
                            'href' => route('reading-history.index'),
                            'icon' =>
                                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                            'active' => request()->routeIs('reading-history.*'),
                        ],
                        [
                            'label' => __('messages.batch_download'),
                            'href' => route('batch-download.index'),
                            'icon' =>
                                '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>',
                            'active' => request()->routeIs('batch-download.*'),
                        ],
                    ],
                ];
            }

            // More Features
            $moreItems = [];

            $moreItems[] = [
                'label' => __('messages.ecosystem'),
                'href' => route('ecosystem.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>',
                'active' => request()->routeIs('ecosystem.*'),
            ];

            $moreItems[] = [
                'label' => __('messages.tuts'),
                'href' => route('tuts.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>',
                'active' => request()->routeIs('tuts.*'),
            ];

            $moreItems[] = [
                'label' => __('messages.studio'),
                'href' => route('studio.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
                'active' => request()->routeIs('studio.*'),
            ];

            $moreItems[] = [
                'label' => __('messages.produk_chats'),
                'href' => route('note-conversations.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>',
                'active' => request()->routeIs('note-conversations.*'),
            ];

            $moreItems[] = [
                'label' => __('messages.simulators'),
                'href' => route('simulators.index'),
                'icon' =>
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>',
                'active' => request()->routeIs('simulators.*'),
            ];

            if ($user->hasRole('vendor') || $isAdmin) {
                $moreItems[] = [
                    'label' => __('messages.vendor'),
                    'href' => route('vendor.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>',
                    'active' => request()->routeIs('vendor.*'),
                ];
            }

            if (!empty($moreItems)) {
                $menuGroups[] = [
                    'title' => 'More Features',
                    'items' => $moreItems,
                ];
            }

            // Settings
            $settingsItems = [];

            // Referral (only for sellers and buyers, hide from admin)
            if (!$isAdmin) {
                $settingsItems[] = [
                    'label' => __('messages.referral'),
                    'href' => route('referral.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>',
                    'active' => request()->routeIs('referral.*'),
                ];
            }

            // Affiliate (only for sellers and buyers, hide from admin)
            if (!$isAdmin) {
                $settingsItems[] = [
                    'label' => __('affiliate.title'),
                    'href' => route('affiliate.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>',
                    'active' => request()->routeIs('affiliate.*'),
                ];
            }

            // Share Analytics (only for sellers)
            if ($isSeller) {
                $settingsItems[] = [
                    'label' => 'Share Analytics',
                    'href' => route('share.analytics'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>',
                    'active' => request()->routeIs('share.analytics'),
                ];

                // Share Leaderboard (only for sellers, not admin)
                $settingsItems[] = [
                    'label' => 'Share Leaderboard',
                    'href' => route('share.leaderboard'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>',
                    'active' => request()->routeIs('share.leaderboard'),
                ];
            }

            // Points & Rewards (only for buyers)
            if ($isBuyer) {
                $settingsItems[] = [
                    'label' => 'Points & Rewards',
                    'href' => route('points.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'active' => request()->routeIs('points.*'),
                ];
            }

            if (!empty($settingsItems)) {
                $menuGroups[] = [
                    'title' => 'Settings',
                    'items' => $settingsItems,
                ];
            }

            // Admin
            if ($isAdmin) {
                $adminItems = [];
                $adminItems[] = [
                    'label' => __('messages.admin'),
                    'href' => route('admin.dashboard'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>',
                    'active' => request()->routeIs('admin.dashboard'),
                ];
                $adminItems[] = [
                    'label' => 'Forum Moderation',
                    'href' => route('admin.forum.moderation.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'active' => request()->routeIs('admin.forum.moderation.*'),
                ];
                $adminItems[] = [
                    'label' => 'Note Moderation',
                    'href' => route('admin.notes.moderation.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>',
                    'active' => request()->routeIs('admin.notes.moderation.*'),
                ];
                $adminItems[] = [
                    'label' => 'Account Moderation',
                    'href' => route('admin.accounts.moderation.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>',
                    'active' => request()->routeIs('admin.accounts.moderation.*'),
                ];
                $adminItems[] = [
                    'label' => __('messages.system_health'),
                    'href' => route('admin.system-health.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'active' => request()->routeIs('admin.system-health.*'),
                ];

                // Studio Payment Verification
                $adminItems[] = [
                    'label' => 'Order Verification',
                    'href' => route('admin.order-verification.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'active' => request()->routeIs('admin.order-verification.*'),
                ];
                $adminItems[] = [
                    'label' => __('affiliate.affiliate_settings'),
                    'href' => route('admin.affiliate-settings.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                    'active' => request()->routeIs('admin.affiliate-settings.*'),
                ];

                $menuGroups[] = [
                    'title' => 'Admin',
                    'items' => $adminItems,
                ];
            }
        }
    } else {
        // Guest menu
        $menuGroups[] = [
            'title' => null,
            'items' => [
                [
                    'label' => __('messages.ecosystem'),
                    'href' => route('ecosystem.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>',
                    'active' => request()->routeIs('ecosystem.*'),
                ],
                [
                    'label' => __('messages.tuts'),
                    'href' => route('tuts.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>',
                    'active' => request()->routeIs('tuts.*'),
                ],
                [
                    'label' => __('messages.studio'),
                    'href' => route('studio.index'),
                    'icon' =>
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>',
                    'active' => request()->routeIs('studio.*'),
                ],
            ],
        ];
    }
@endphp

<div x-data="{
    sidebarOpen: false,
    isExpanded: localStorage.getItem('sidebarExpanded') !== 'false',
    isMobile: window.innerWidth < 1024,
    init() {
        const self = this;

        // Initialize on load
        if (window.innerWidth >= 1024) {
            self.sidebarOpen = true;
            self.isExpanded = localStorage.getItem('sidebarExpanded') !== 'false';
        }

        // Sync isExpanded with localStorage
        this.$watch('isExpanded', value => {
            localStorage.setItem('sidebarExpanded', value);
        });

        // Handle window resize
        const handleResize = () => {
            self.isMobile = window.innerWidth < 1024;
            if (window.innerWidth >= 1024) {
                self.sidebarOpen = true;
            } else {
                self.sidebarOpen = false;
            }
        };

        window.addEventListener('resize', handleResize);

        // Listen for toggle event from top-nav (mobile)
        document.addEventListener('toggle-sidebar', () => {
            if (self.isMobile) {
                self.sidebarOpen = !self.sidebarOpen;
            }
        });
    }
}" class="flex h-screen bg-gray-50 dark:bg-gray-900 overflow-hidden">
    <!-- Sidebar -->
    <aside
        :class="[
            isExpanded ? 'w-64' : 'w-20',
            'transition-all duration-300 ease-in-out'
        ]"
        class="hidden lg:flex lg:flex-col fixed lg:static inset-y-0 left-0 z-30 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-hidden flex-col flex-shrink-0 h-full"
        x-cloak>
        <!-- Logo -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <a href="/" class="flex items-center gap-2 min-w-0">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Noteds') }}" class="h-8 w-8 flex-shrink-0">
                <span x-show="isExpanded" x-transition
                    class="text-xl font-bold text-gray-900 dark:text-white whitespace-nowrap overflow-hidden">
                    {{ config('app.name', 'Noteds') }}
                </span>
            </a>
            <button @click="isExpanded = !isExpanded"
                class="p-1.5 rounded-md text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg x-show="isExpanded" x-transition class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <svg x-show="!isExpanded" x-transition class="w-5 h-5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-2">
            @foreach ($menuGroups as $group)
                @if ($group['title'])
                    <div x-show="isExpanded" x-transition
                        class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $group['title'] }}
                    </div>
                @endif
                <div class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['href'] }}"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150 group
                                {{ $item['active']
                                    ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400'
                                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                            title="{{ $item['label'] }}">
                            <span class="flex-shrink-0 {!! $item['active']
                                ? 'text-blue-600 dark:text-blue-400'
                                : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300' !!}">
                                {!! $item['icon'] !!}
                            </span>
                            <span x-show="isExpanded" x-transition class="flex-1 truncate">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @endforeach
                </div>
                @if (!$loop->last)
                    <div class="my-2 border-t border-gray-200 dark:border-gray-700"></div>
                @endif
            @endforeach
        </nav>

        <!-- User Section (if authenticated) -->
        @auth
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                        @if (auth()->user()->avatar)
                            @if (str_starts_with(auth()->user()->avatar, 'http'))
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                    class="w-10 h-10 rounded-full object-cover" loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @else
                                @php
                                    $avatarPath = auth()->user()->avatar;
                                    $avatarPath = ltrim($avatarPath, '/');
                                    $avatarPath = preg_replace('#^marketplace/#', '', $avatarPath);
                                @endphp
                                <img src="{{ asset('storage/' . $avatarPath) }}" alt="{{ auth()->user()->name }}"
                                    class="w-10 h-10 rounded-full object-cover" loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            @endif
                        @endif
                        <span class="text-sm font-semibold text-white"
                            style="display: {{ auth()->user()->avatar ? 'none' : 'flex' }};">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <div x-show="isExpanded" x-transition class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ auth()->user()->email }}
                        </p>
                    </div>
                </div>
            </div>
        @endauth
    </aside>

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        class="fixed inset-0 bg-gray-600 bg-opacity-75 lg:hidden z-20"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
    </div>

    <!-- Mobile Sidebar -->
    <aside x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed lg:hidden inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col flex-shrink-0 h-full"
        x-cloak>
        <!-- Mobile Sidebar Header -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
            <a href="/" class="flex items-center gap-2 min-w-0">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Noteds') }}"
                    class="h-8 w-8 flex-shrink-0">
                <span class="text-xl font-bold text-gray-900 dark:text-white whitespace-nowrap overflow-hidden">
                    {{ config('app.name', 'Noteds') }}
                </span>
            </a>
            <button @click="sidebarOpen = false"
                class="p-1.5 rounded-md text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <nav class="flex-1 overflow-y-auto py-4 px-2">
            @foreach ($menuGroups as $group)
                @if ($group['title'])
                    <div
                        class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $group['title'] }}
                    </div>
                @endif
                <div class="space-y-1">
                    @foreach ($group['items'] as $item)
                        @if (isset($item['submenu']) && count($item['submenu']) > 0)
                            <div x-data="{ open: {{ $item['active'] ? 'true' : 'false' }} }" class="space-y-1">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md {{ $item['active'] ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors duration-150">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="flex-shrink-0">{!! $item['icon'] !!}</span>
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </button>
                                <div x-show="open" class="pl-3 space-y-1">
                                    @foreach ($item['submenu'] as $subitem)
                                        <a href="{{ $subitem['href'] }}"
                                            class="block px-3 py-2 text-sm rounded-md {{ $subitem['active'] ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors duration-150">
                                            {{ $subitem['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ $item['href'] }}"
                                class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md {{ $item['active'] ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition-colors duration-150">
                                <span class="flex-shrink-0">{!! $item['icon'] !!}</span>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </nav>

        <!-- Mobile User Section (if authenticated) -->
        @auth
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                        @if (auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                class="w-10 h-10 rounded-full object-cover">
                        @else
                            <span class="text-sm font-semibold text-white">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            @foreach (auth()->user()->roles as $role)
                                {{ $role->name }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        @endauth
    </aside>
</div>
