@extends('admin.layouts.app')

@section('title', 'Notes Management')
@section('header', 'Notes Management')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Notes</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Published Notes</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['published'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Pending Moderation</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($stats['pending'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Flagged/Blocked</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($stats['blocked'] ?? 0) }}</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <input type="text" id="search" placeholder="Search by title..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <select id="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="blocked">Blocked</option>
            </select>
            <select id="category" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Categories</option>
                <option value="science">Science</option>
                <option value="technology">Technology</option>
                <option value="business">Business</option>
            </select>
            <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
            <button class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Export</button>
        </div>
    </div>

    <!-- Notes Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Note Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Author</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Sales</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($notes as $note)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ substr($note->title ?? 'N/A', 0, 30) }}...</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $note->author->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $note->category->name ?? 'Uncategorized' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($note->status === 'published')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Published</span>
                        @elseif($note->status === 'draft')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                        @elseif($note->status === 'pending')
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @else
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Blocked</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Rp {{ number_format($note->price ?? 0, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ $note->purchase_count ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $note->created_at->format('d M Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        <a href="#" class="text-green-600 hover:text-green-900 mr-3">Edit</a>
                        <button class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No notes found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($notes->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">
            {{ $notes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
