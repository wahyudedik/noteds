@extends('layouts.app')

@section('title', __('messages.note_moderation'))

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.note_moderation') }}</h1>
                <p class="mt-1 text-sm text-gray-600">Review reported notes, update statuses, and keep the marketplace safe.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to Admin Dashboard
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <form method="GET" action="{{ route('admin.notes.moderation.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search title or owner..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="sold" {{ $status === 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Status</label>
                    <select name="report_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        <option value="pending" {{ $reportStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reviewed" {{ $reportStatus === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="resolved" {{ $reportStatus === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="dismissed" {{ $reportStatus === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        <option value="unreported" {{ $reportStatus === 'unreported' ? 'selected' : '' }}>No Reports</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm">
                        Filter
                    </button>
                    @if($search || $status || $reportStatus)
                        <a href="{{ route('admin.notes.moderation.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($notes as $note)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                                    <p class="font-medium text-gray-900">{{ \Illuminate\Support\Str::limit($note->title, 120) }}</p>
                                    <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                        <span>ID: {{ $note->id }}</span>
                                        @if(!$note->is_public)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-200 text-gray-700">Private</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $note->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ '@' . $note->user->username }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $note->pending_reports_count > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $note->pending_reports_count }} pending
                                        </span>
                                        <span class="text-xs text-gray-500">Total: {{ $note->reports_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($note->status === 'active') bg-green-100 text-green-800
                                        @elseif($note->status === 'sold') bg-blue-100 text-blue-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($note->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $note->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.notes.moderation.show', $note) }}" class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No notes found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-top border-gray-200">
                {{ $notes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

