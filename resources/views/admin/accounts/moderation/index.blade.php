@extends('40-shared/layouts/app')

@section('title', __('Account Moderation'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Account Moderation') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Review and manage reported user accounts') }}</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <form method="GET" action="{{ route('admin.accounts.moderation.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
                            <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                                placeholder="{{ __('Search by name, email, or username...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                        @if ($search || $reportStatus)
                            <a href="{{ route('admin.accounts.moderation.index') }}"
                                class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                                {{ __('Clear Filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Users List -->
            @if (($users ?? collect())->count() > 0)
                <div class="space-y-4">
                    @foreach ($users ?? [] as $user)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4 flex-1">
                                    <!-- User Avatar -->
                                    @if ($user->avatar_url)
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                            class="w-16 h-16 rounded-full">
                                    @else
                                        <div
                                            class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xl">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="flex-1">
                                        <!-- User Info -->
                                        <h3 class="text-xl font-semibold text-gray-900">{{ $user->name }}</h3>
                                        <p class="text-gray-600">@{{ $user - > username }}</p>
                                        <p class="text-sm text-gray-500 mb-4">{{ $user->email }}</p>

                                        <!-- Account Info -->
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                            <div>
                                                <p class="text-xs text-gray-600 uppercase font-semibold">
                                                    {{ __('Member Since') }}</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ $user->created_at->format('M d, Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-600 uppercase font-semibold">
                                                    {{ __('Status') }}</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if ($user->email_verified_at)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            {{ __('Verified') }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                            {{ __('Unverified') }}
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-600 uppercase font-semibold">
                                                    {{ __('Active') }}</p>
                                                <p class="text-sm font-medium text-gray-900">
                                                    @if (!$user->suspended_at)
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            {{ __('Yes') }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                            {{ __('Suspended') }}
                                                        </span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Report Badges -->
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if ($user->pending_reports_count > 0)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                    {{ $user->pending_reports_count }}
                                                    {{ __('Pending Report') }}{{ $user->pending_reports_count !== 1 ? 's' : '' }}
                                                </span>
                                            @endif

                                            @if ($user->account_reports_count > 0)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                    {{ $user->account_reports_count }}
                                                    {{ __('Total Report') }}{{ $user->account_reports_count !== 1 ? 's' : '' }}
                                                </span>
                                            @endif

                                            @if ($user->account_reports_count === 0)
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    {{ __('No Reports') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="ml-6 flex items-center gap-2">
                                    @if ($user->account_reports_count > 0)
                                        <a href="{{ route('admin.accounts.moderation.show', $user) }}"
                                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm">
                                            {{ __('Review') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($users->hasPages())
                    <div class="mt-8">
                        {{ $users->links() }}
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No accounts to moderate') }}</h3>
                    <p class="text-gray-600">{{ __('All accounts are in good standing!') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
