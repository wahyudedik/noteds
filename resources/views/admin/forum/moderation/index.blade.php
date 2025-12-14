@extends('40-shared/layouts/app')

@section('title', __('Post Moderation'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Post Moderation') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Review and manage reported forum posts') }}</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <form method="GET" action="{{ route('admin.forum.moderation.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
                            <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                                placeholder="{{ __('Search posts or authors...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Post Status') }}</label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('All Posts') }}</option>
                                <option value="visible" {{ $status === 'visible' ? 'selected' : '' }}>{{ __('Visible') }}
                                </option>
                                <option value="hidden" {{ $status === 'hidden' ? 'selected' : '' }}>{{ __('Hidden') }}
                                </option>
                            </select>
                        </div>

                        <!-- Report Status Filter -->
                        <div>
                            <label for="report_status"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Report Status') }}</label>
                            <select id="report_status" name="report_status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('All Reports') }}</option>
                                <option value="pending" {{ $reportStatus === 'pending' ? 'selected' : '' }}>
                                    {{ __('Pending') }}</option>
                                <option value="reviewed" {{ $reportStatus === 'reviewed' ? 'selected' : '' }}>
                                    {{ __('Reviewed') }}</option>
                                <option value="resolved" {{ $reportStatus === 'resolved' ? 'selected' : '' }}>
                                    {{ __('Resolved') }}</option>
                                <option value="dismissed" {{ $reportStatus === 'dismissed' ? 'selected' : '' }}>
                                    {{ __('Dismissed') }}</option>
                                <option value="unreported" {{ $reportStatus === 'unreported' ? 'selected' : '' }}>
                                    {{ __('Unreported') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                            {{ __('Apply Filters') }}
                        </button>
                        @if ($search || $status || $reportStatus)
                            <a href="{{ route('admin.forum.moderation.index') }}"
                                class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                                {{ __('Clear Filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Posts List -->
            @if (($posts ?? collect())->count() > 0)
                <div class="space-y-4">
                    @foreach ($posts ?? [] as $post)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <!-- Post Header -->
                                    <div class="flex items-center gap-3 mb-3">
                                        @if ($post?->user?->avatar_url)
                                            <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }}"
                                                class="w-10 h-10 rounded-full">
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                                {{ strtoupper(substr($post?->user?->name ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                {{ $post?->user?->name ?? 'Unknown User' }}</p>
                                            <p class="text-sm text-gray-500">@{{ $post - > user - > username ?? 'unknown' }} ·
                                                {{ $post?->created_at?->diffForHumans() ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <!-- Post Content -->
                                    <p class="text-gray-700 mb-4 line-clamp-3">{{ Str::limit($post?->content ?? '', 300) }}
                                    </p>

                                    <!-- Status Badges -->
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if ($post?->is_hidden)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                {{ __('Hidden') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                {{ __('Visible') }}
                                            </span>
                                        @endif

                                        @if (($post?->pending_reports_count ?? 0) > 0)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                {{ $post->pending_reports_count }}
                                                {{ __('Pending Report') }}{{ $post->pending_reports_count !== 1 ? 's' : '' }}
                                            </span>
                                        @endif

                                        @if (($post?->reports_count ?? 0) > 0)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                                {{ $post->reports_count }}
                                                {{ __('Total Report') }}{{ $post->reports_count !== 1 ? 's' : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="ml-6 flex items-center gap-2">
                                    <a href="{{ route('admin.forum.moderation.show', $post) }}"
                                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm">
                                        {{ __('Review') }}
                                    </a>
                                    @if ($post?->is_hidden)
                                        <form action="{{ route('admin.forum.moderation.unhide', $post) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition text-sm"
                                                onclick="return confirm('{{ __('Unhide this post?') }}')">
                                                {{ __('Unhide') }}
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.forum.moderation.hide', $post) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm"
                                                onclick="return confirm('{{ __('Hide this post?') }}')">
                                                {{ __('Hide') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($posts->hasPages())
                    <div class="mt-8">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No posts to moderate') }}</h3>
                    <p class="text-gray-600">{{ __('All posts are in order!') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
