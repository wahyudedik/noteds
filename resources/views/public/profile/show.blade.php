@extends('40-shared/layouts/app')

@section('title', $user->name . ' - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <!-- Profile Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                    <!-- Avatar -->
                    <div
                        class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center shadow-lg overflow-hidden flex-shrink-0">
                        @if ($user->avatar)
                            @if (str_starts_with($user->avatar, 'http'))
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                    class="w-32 h-32 rounded-full object-cover">
                            @else
                                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                    class="w-32 h-32 rounded-full object-cover">
                            @endif
                        @else
                            <span class="text-6xl font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>

                    <!-- User Info -->
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h1 class="text-4xl font-bold text-gray-900">{{ $user->name }}</h1>
                            @if ($user->hasRole('seller'))
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    Seller
                                </span>
                            @endif
                            @if ($user->verification_status === 'verified')
                                <span
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Verified
                                </span>
                            @endif
                        </div>

                        @if ($user->username)
                            <p class="text-gray-600 text-lg mb-3">@{{ $user - > username }}</p>
                        @endif

                        @if ($user->bio)
                            <p class="text-gray-700 text-base mb-4 max-w-2xl">{{ $user->bio }}</p>
                        @endif

                        <!-- Location & Contact -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                            @if ($user->location)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    {{ $user->location }}
                                </div>
                            @endif

                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Joined {{ $user->created_at->format('M Y') }}
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 w-full md:w-auto">
                        @auth
                            @if (auth()->id() !== $user->id)
                                <a href="{{ route('messages.index') }}"
                                    class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Message
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                                class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                                Sign In
                            </a>
                        @endauth

                        <!-- Share Buttons -->
                        <div class="flex gap-2">
                            <a href="https://facebook.com/sharer/sharer.php?u={{ urlencode(route('public.profile.show', $user->username)) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                                title="Share on Facebook">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5c-.563-.074-2.323-.216-4.408-.216-4.979 0-8.385 3.541-8.385 10.045v1.671z" />
                                </svg>
                            </a>

                            <a href="https://twitter.com/intent/tweet?text={{ urlencode('Check out ' . $user->name . ' on ' . config('app.name')) }}&url={{ urlencode(route('public.profile.show', $user->username)) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-sky-500 text-white hover:bg-sky-600 transition-colors"
                                title="Share on Twitter">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2s9 5 20 5a9.5 9.5 0 00-9-5.5c4.75 2.25 7-7 7-7s1.08-7-8-13.25z" />
                                </svg>
                            </a>

                            <button onclick="copyToClipboard('{{ route('public.profile.show', $user->username) }}')"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-600 text-white hover:bg-gray-700 transition-colors"
                                title="Copy profile link">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>

                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('public.profile.show', $user->username)) }}"
                                target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-700 text-white hover:bg-blue-800 transition-colors"
                                title="Share on LinkedIn">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="bg-white border-b border-gray-200 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Notes Count -->
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_notes']) }}</div>
                        <div class="text-sm text-gray-600 mt-1">Notes Published</div>
                    </div>

                    <!-- Sales Count -->
                    @if ($user->hasRole('seller'))
                        <div class="text-center">
                            <div class="text-3xl font-bold text-green-600">{{ number_format($stats['total_sales']) }}</div>
                            <div class="text-sm text-gray-600 mt-1">Sales</div>
                        </div>

                        <!-- Revenue -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-purple-600">Rp
                                {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                            <div class="text-sm text-gray-600 mt-1">Total Revenue</div>
                        </div>
                    @endif

                    <!-- Rating -->
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <div class="flex text-yellow-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($stats['average_rating']))
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                            <path
                                                d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 text-gray-300 fill-current" viewBox="0 0 20 20">
                                            <path
                                                d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                        </svg>
                                    @endif
                                @endfor
                            </div>
                        </div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['average_rating'] }}</div>
                        <div class="text-sm text-gray-600">{{ number_format($stats['total_reviews']) }} Reviews</div>
                    </div>

                    <!-- Posts -->
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ number_format($stats['total_posts']) }}</div>
                        <div class="text-sm text-gray-600 mt-1">Forum Posts</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Badges & Certifications -->
        @if ($user->badges->count() > 0 || $user->approvedCertifications->count() > 0)
            <div class="bg-white border-b border-gray-200 py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Achievements & Certifications</h2>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Badges -->
                        @if ($user->badges->count() > 0)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Badges</h3>
                                <div class="flex flex-wrap gap-4">
                                    @foreach ($user->badges as $badge)
                                        <div class="text-center">
                                            <div
                                                class="w-20 h-20 rounded-full bg-gradient-to-br from-yellow-300 to-yellow-500 flex items-center justify-center mx-auto mb-2 shadow-md">
                                                <svg class="w-10 h-10 text-white" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-gray-900">{{ $badge->name }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $badge->pivot->earned_at?->format('M Y') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Certifications -->
                        @if ($user->approvedCertifications->count() > 0)
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Certifications</h3>
                                <div class="space-y-3">
                                    @foreach ($user->approvedCertifications as $cert)
                                        <div
                                            class="flex items-center gap-3 p-3 bg-green-50 rounded-lg border border-green-200">
                                            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900">{{ $cert->certification->name }}</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $cert->certification->issuer ?? 'Verified' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabs: Notes & Posts -->
        <div class="bg-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6" x-data="{ tab: 'notes' }">
                    <div class="flex gap-4 border-b border-gray-200">
                        <button @click="tab = 'notes'"
                            :class="tab === 'notes' ? 'border-b-2 border-blue-600 text-blue-600' :
                                'text-gray-600 hover:text-gray-900'"
                            class="py-4 px-4 font-medium transition-colors">
                            Notes ({{ number_format($stats['total_notes']) }})
                        </button>
                        <button @click="tab = 'posts'"
                            :class="tab === 'posts' ? 'border-b-2 border-blue-600 text-blue-600' :
                                'text-gray-600 hover:text-gray-900'"
                            class="py-4 px-4 font-medium transition-colors">
                            Forum Posts ({{ number_format($stats['total_posts']) }})
                        </button>
                    </div>

                    <!-- Notes Tab -->
                    <div x-show="tab === 'notes'" class="mt-8">
                        @if ($publicNotes->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($publicNotes as $note)
                                    <div
                                        class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                        <a href="{{ route('marketplace.show', $note) }}" class="block group">
                                            @if ($note->cover_image)
                                                <div class="w-full h-40 bg-gray-200 overflow-hidden">
                                                    <img src="{{ Storage::url($note->cover_image) }}"
                                                        alt="{{ $note->title }}"
                                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                                </div>
                                            @else
                                                <div
                                                    class="w-full h-40 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                                    <svg class="w-12 h-12 text-white opacity-50" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                            @endif

                                            <div class="p-4">
                                                <h3
                                                    class="font-semibold text-gray-900 group-hover:text-blue-600 line-clamp-2">
                                                    {{ $note->title }}</h3>
                                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                                    {{ Str::limit($note->description, 80) }}</p>

                                                <div class="mt-3 flex items-center justify-between">
                                                    @if ($note->price > 0)
                                                        <span class="font-bold text-blue-600">Rp
                                                            {{ number_format($note->price, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-green-600 font-medium">Free</span>
                                                    @endif

                                                    @if ($note->reviews_count > 0)
                                                        <div class="flex items-center gap-1">
                                                            <span class="text-yellow-400">★</span>
                                                            <span
                                                                class="text-sm font-medium">{{ number_format($note->average_rating ?? 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if ($note->tags->count() > 0)
                                                    <div class="mt-3 flex flex-wrap gap-1">
                                                        @foreach ($note->tags->take(2) as $tag)
                                                            <span
                                                                class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded">{{ $tag->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="mt-8">
                                {{ $publicNotes->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-gray-600 text-lg">No notes published yet</p>
                            </div>
                        @endif
                    </div>

                    <!-- Posts Tab -->
                    <div x-show="tab === 'posts'" class="mt-8">
                        @if ($userPosts->count() > 0)
                            <div class="space-y-6 max-w-2xl">
                                @foreach ($userPosts as $post)
                                    <div
                                        class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                        <a href="{{ route('forum.show', $post) }}" class="block group mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600">
                                                {{ $post->title }}</h3>
                                            <p class="text-gray-600 mt-2 line-clamp-3">
                                                {{ Str::limit(strip_tags($post->content), 200) }}</p>
                                        </a>

                                        <div
                                            class="flex items-center justify-between text-sm text-gray-600 pt-4 border-t border-gray-100">
                                            <div class="flex items-center gap-4">
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                    </svg>
                                                    {{ $post->all_comments_count }} Responses
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2v-8a2 2 0 012-2h2.4a1 1 0 00.894-.553l1.342-2.683a1 1 0 00.894-.553h2.17a1 1 0 00.894.553l1.342 2.683a1 1 0 00.894.553Z" />
                                                    </svg>
                                                    {{ $post->likes_count }} Likes
                                                </div>
                                            </div>
                                            <time
                                                class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</time>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="mt-8">
                                {{ $userPosts->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <p class="text-gray-600 text-lg">No forum posts yet</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Profile link copied to clipboard!');
                }).catch(() => {
                    alert('Failed to copy link');
                });
            }
        </script>
    @endpush
@endsection
