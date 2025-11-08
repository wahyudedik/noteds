@extends('layouts.app')

@section('title', 'Moderate Account')

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Moderate Account</h1>
                <p class="mt-1 text-sm text-gray-600">Review reports and take action on problematic accounts.</p>
            </div>
            <a href="{{ route('admin.accounts.moderation.index') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to Account Moderation
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900">{{ $user->name }}</h2>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                            <span>Username: @{{ $user->username }}</span>
                            <span>Email: {{ $user->email }}</span>
                            <span>Joined {{ $user->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                @if($user->bio)
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Bio</h3>
                        <p class="text-sm text-gray-800">{{ $user->bio }}</p>
                    </div>
                @endif

                <div class="flex items-center gap-3 text-sm text-gray-700">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $user->accountReports()->where('status', 'pending')->count() > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        Pending Reports: {{ $user->accountReports()->where('status', 'pending')->count() }}
                    </span>
                    <span class="text-xs text-gray-500">Total Reports: {{ $user->accountReports()->count() }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Reports</h2>
                <p class="text-sm text-gray-600 mt-1">Review submissions and update their statuses.</p>
            </div>

            <div class="divide-y divide-gray-200">
                @forelse($reports as $report)
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <span class="font-semibold text-gray-900">Reason:</span> {{ ucfirst($report->reason) }}
                                    <span class="text-gray-400">•</span>
                                    <span>Reported {{ $report->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <span class="font-semibold text-gray-900">Reported by:</span>
                                    {{ $report->reporter->name }} (@{{ $report->reporter->username }})
                                </div>
                                @if($report->description)
                                    <div class="mt-2 text-sm text-gray-800">
                                        <span class="font-semibold text-gray-900 block mb-1">Description</span>
                                        <p class="bg-gray-100 border border-gray-200 rounded-md px-3 py-2">
                                            {{ $report->description }}
                                        </p>
                                    </div>
                                @endif
                                @if($report->admin_notes)
                                    <div class="mt-2 text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900 block mb-1">Admin Notes</span>
                                        <p class="bg-blue-50 border border-blue-200 rounded-md px-3 py-2">
                                            {{ $report->admin_notes }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                            <div class="w-full md:w-64">
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        @if($report->status === 'pending') bg-red-100 text-red-800
                                        @elseif($report->status === 'resolved') bg-green-100 text-green-800
                                        @elseif($report->status === 'dismissed') bg-gray-200 text-gray-700
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                    @if($report->reviewer)
                                        <div class="mt-1 text-xs text-gray-500">
                                            Reviewed by {{ $report->reviewer->name }} {{ $report->reviewed_at?->format('d M Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.accounts.moderation.report.status', $report) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Update Status</label>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                            <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Admin Notes</label>
                                        <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Add context or follow-up details (optional)">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                                    </div>
                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                                        Save Update
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-sm text-gray-500">
                        No reports found for this account.
                    </div>
                @endforelse
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

