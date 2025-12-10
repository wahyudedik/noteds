@extends('admin.layouts.app')

@section('title', 'Forum Moderation')
@section('header', 'Forum Moderation & Management')

@section('content')
<div class="space-y-6">
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Discussions</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_discussions'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Comments</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($stats['total_comments'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Pending Review</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($stats['pending_review'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Flagged Content</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($stats['flagged_content'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow border-b border-gray-200">
        <div class="flex flex-wrap">
            <button class="forum-tab active px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600" data-tab="discussions">
                Discussions
            </button>
            <button class="forum-tab px-6 py-4 text-sm font-medium text-gray-700 hover:text-gray-900 border-b-2 border-transparent" data-tab="comments">
                Comments
            </button>
            <button class="forum-tab px-6 py-4 text-sm font-medium text-gray-700 hover:text-gray-900 border-b-2 border-transparent" data-tab="flagged">
                Flagged Content
            </button>
        </div>
    </div>

    <!-- Discussions Tab -->
    <div id="discussions-tab" class="forum-content active bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" placeholder="Search discussions..." class="px-4 py-2 border border-gray-300 rounded-lg">
                <select class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option>All Categories</option>
                    <option>General</option>
                    <option>Questions</option>
                    <option>Tips & Tricks</option>
                </select>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
            </div>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Discussion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">Comments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($discussions as $discussion)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ substr($discussion->title ?? 'N/A', 0, 30) }}...</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $discussion->author->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">{{ $discussion->comments_count ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $discussion->created_at->format('d M Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <button class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No discussions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Comments Tab -->
    <div id="comments-tab" class="forum-content hidden bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" placeholder="Search comments..." class="px-4 py-2 border border-gray-300 rounded-lg">
                <select class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option>All Status</option>
                    <option>Approved</option>
                    <option>Pending</option>
                    <option>Flagged</option>
                </select>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
            </div>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Comment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Discussion</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($comments as $comment)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ substr($comment->content ?? 'N/A', 0, 50) }}...</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $comment->author->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ substr($comment->discussion->title ?? 'N/A', 0, 20) }}...</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $comment->created_at->format('d M Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <button class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No comments found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Flagged Content Tab -->
    <div id="flagged-tab" class="forum-content hidden bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" placeholder="Search flagged content..." class="px-4 py-2 border border-gray-300 rounded-lg">
                <select class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option>All Reasons</option>
                    <option>Spam</option>
                    <option>Offensive</option>
                    <option>Inappropriate</option>
                </select>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
                <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve All</button>
            </div>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Content</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Flagged By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($flagged as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">{{ substr($item->content ?? 'N/A', 0, 30) }}...</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst($item->type ?? 'N/A') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">{{ ucfirst($item->flag_reason ?? 'Unknown') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->flagged_by->name ?? 'System' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->flagged_at->format('d M Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <button class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                        <button class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No flagged content</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.forum-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.getAttribute('data-tab');
        
        // Hide all content
        document.querySelectorAll('.forum-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Show selected content
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        
        // Update active tab
        document.querySelectorAll('.forum-tab').forEach(t => {
            t.classList.remove('border-blue-600', 'text-blue-600');
            t.classList.add('border-transparent', 'text-gray-700');
        });
        this.classList.add('border-blue-600', 'text-blue-600');
        this.classList.remove('border-transparent', 'text-gray-700');
    });
});
</script>
@endsection
