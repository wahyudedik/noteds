@extends('40-shared/layouts/app')

@section('title', __('Notes Moderation'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Notes Moderation') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Review and manage reported marketplace notes') }}</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <form method="GET" action="{{ route('admin.notes.moderation.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search') }}</label>
                            <input type="text" id="search" name="search" value="{{ $search ?? '' }}"
                                placeholder="{{ __('Search notes or authors...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Note Status') }}</label>
                            <select id="status" name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('All Notes') }}</option>
                                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>{{ __('Active') }}
                                </option>
                                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>
                                    {{ __('Inactive') }}</option>
                                <option value="sold" {{ $status === 'sold' ? 'selected' : '' }}>{{ __('Sold') }}
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
                            <a href="{{ route('admin.notes.moderation.index') }}"
                                class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                                {{ __('Clear Filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Notes List -->
            @if (($notes ?? collect())->count() > 0)
                <div class="space-y-4">
                    @foreach ($notes ?? [] as $note)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <!-- Note Header -->
                                    <div class="flex items-center gap-3 mb-3">
                                        @if ($note?->user?->avatar_url)
                                            <img src="{{ $note->user->avatar_url }}" alt="{{ $note->user->name }}"
                                                class="w-10 h-10 rounded-full">
                                        @else
                                            <div
                                                class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                                {{ strtoupper(substr($note?->user?->name ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                {{ $note?->user?->name ?? 'Unknown User' }}</p>
                                            <p class="text-sm text-gray-500">@{{ $note - > user - > username ?? 'unknown' }} ·
                                                {{ $note?->created_at?->diffForHumans() ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    <!-- Note Title -->
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $note?->title ?? 'Untitled' }}
                                    </h3>

                                    <!-- Note Content Preview -->
                                    @if ($note?->description)
                                        <p class="text-gray-700 mb-4 line-clamp-2">
                                            {{ Str::limit($note->description, 200) }}</p>
                                    @endif

                                    <!-- Status Badges -->
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @php
                                            $statusColors = [
                                                'active' => 'bg-green-100 text-green-800',
                                                'inactive' => 'bg-gray-100 text-gray-800',
                                                'sold' => 'bg-blue-100 text-blue-800',
                                            ];
                                            $statusColor = $statusColors[$note->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                            {{ ucfirst($note->status) }}
                                        </span>

                                        @if ($note->pending_reports_count > 0)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                                {{ $note->pending_reports_count }}
                                                {{ __('Pending Report') }}{{ $note->pending_reports_count !== 1 ? 's' : '' }}
                                            </span>
                                        @endif

                                        @if ($note->reports_count > 0)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                {{ $note->reports_count }}
                                                {{ __('Total Report') }}{{ $note->reports_count !== 1 ? 's' : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="ml-6 flex items-center gap-2">
                                    <a href="{{ route('admin.notes.moderation.show', $note) }}"
                                        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm">
                                        {{ __('Review') }}
                                    </a>
                                    @if ($note->status === 'active')
                                        <form action="{{ route('admin.notes.moderation.suspend', $note) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition text-sm"
                                                onclick="return confirm('{{ __('Suspend this note?') }}')">
                                                {{ __('Suspend') }}
                                            </button>
                                        </form>
                                    @elseif ($note->status === 'inactive')
                                        <form action="{{ route('admin.notes.moderation.activate', $note) }}" method="POST"
                                            class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition text-sm"
                                                onclick="return confirm('{{ __('Activate this note?') }}')">
                                                {{ __('Activate') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($notes->hasPages())
                    <div class="mt-8">
                        {{ $notes->links() }}
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
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No notes to moderate') }}</h3>
                    <p class="text-gray-600">{{ __('All notes are in order!') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
