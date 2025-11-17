@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('title', __('messages.post_moderation'))

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.forum.moderation.index') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to moderation list
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-2xl font-bold text-gray-900">Post Details</h2>
                        @if($post->is_hidden)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Hidden</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Visible</span>
                        @endif
                        @if($post->is_pinned)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pinned</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">Post ID: {{ $post->id }}</div>
                    <div class="text-sm text-gray-500">Created at: {{ $post->created_at->format('d M Y H:i') }}</div>
                    @if($post->hidden_at)
                        <div class="text-sm text-gray-500">Hidden at: {{ $post->hidden_at->format('d M Y H:i') }}</div>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($post->is_hidden)
                        <form method="POST" action="{{ route('admin.forum.moderation.unhide', $post) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">
                                Unhide Post
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.forum.moderation.hide', $post) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">
                                Hide Post
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.forum.moderation.destroy', $post) }}" onsubmit="return confirm('Are you sure you want to permanently delete this post? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gray-500 hover:bg-gray-600 rounded-lg">
                            Delete Post
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-6 p-5 border border-gray-200 rounded-lg bg-gray-50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-sm font-semibold text-gray-900">{{ $post->user->name }}</div>
                    <div class="text-xs text-gray-500">{{ '@' . $post->user->username }}</div>
                </div>
                <div class="text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $post->content }}</div>
                @if($post->note)
                    <div class="mt-4 text-sm text-gray-600">
                        <span class="font-medium text-gray-800">Shared note:</span>
                        <a href="{{ route('marketplace.show', $post->note) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                            {{ $post->note->title }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Reports ({{ $reports->total() }})</h3>
                    <p class="text-sm text-gray-600">Manage individual reports and update their status.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reports as $report)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $report->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ '@' . $report->user->username }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize">{{ $report->reason }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-sm">
                                    {{ $report->description ? Str::limit($report->description, 160) : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold @switch($report->status)
                                        @case('pending') bg-red-100 text-red-800 @break
                                        @case('reviewed') bg-yellow-100 text-yellow-800 @break
                                        @case('resolved') bg-green-100 text-green-800 @break
                                        @case('dismissed') bg-gray-100 text-gray-800 @break
                                        @default bg-blue-100 text-blue-800
                                    @endswitch">
                                        {{ ucfirst($report->status) }}
                                    </span>
                                    @if($report->reviewed_at)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Updated {{ $report->reviewed_at->diffForHumans() }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $report->created_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <form method="POST" action="{{ route('admin.forum.moderation.report.status', $report) }}" class="space-y-2">
                                        @csrf
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="reviewed" {{ $report->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                                            <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                            <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                        </select>
                                        <textarea name="admin_notes" rows="2" placeholder="Add internal notes (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No reports submitted for this post.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $reports->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

