@extends('layouts.app')

@section('title', 'Admin - Ticket Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('admin.tickets.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">Ticket #{{ substr($ticket->id, 0, 8) }}</h1>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Ticket Details -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-gray-900">{{ $ticket->title }}</h2>
                        @if($ticket->status === 'open')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Open
                            </span>
                        @elseif($ticket->status === 'in_progress')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                In Progress
                            </span>
                        @elseif($ticket->status === 'resolved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                Resolved
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Closed
                            </span>
                        @endif
                        @if($ticket->priority === 'urgent')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Urgent
                            </span>
                        @elseif($ticket->priority === 'high')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                High
                            </span>
                        @elseif($ticket->priority === 'medium')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Medium
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Low
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">From</h3>
                    <div class="flex items-center gap-3">
                        @if($ticket->user->avatar)
                            <img src="{{ $ticket->user->avatar }}" alt="{{ $ticket->user->name }}" class="w-10 h-10 rounded-full">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                <span class="text-sm font-semibold text-white">{{ strtoupper(substr($ticket->user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $ticket->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                        </div>
                    </div>
                </div>

                <div class="prose max-w-none mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
                </div>

                @if($ticket->links && count($ticket->links) > 0)
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Related Links</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($ticket->links as $link)
                                <a href="{{ $link }}" target="_blank" class="inline-flex items-center px-3 py-1 rounded-lg text-sm text-blue-600 bg-blue-50 hover:bg-blue-100">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    {{ Str::limit($link, 30) }}
                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Created:</span>
                            <span class="text-gray-600">{{ $ticket->created_at->format('F d, Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Last Updated:</span>
                            <span class="text-gray-600">{{ $ticket->updated_at->format('F d, Y H:i') }}</span>
                        </div>
                        @if($ticket->assignedAdmin)
                            <div>
                                <span class="font-medium text-gray-700">Assigned To:</span>
                                <span class="text-gray-600">{{ $ticket->assignedAdmin->name }}</span>
                            </div>
                        @endif
                        @if($ticket->closedByUser)
                            <div>
                                <span class="font-medium text-gray-700">Closed By:</span>
                                <span class="text-gray-600">{{ $ticket->closedByUser->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h3>

            <!-- Assign Ticket -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Assign to Admin</h4>
                <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST" class="flex items-end gap-2">
                    @csrf
                    <div class="flex-1">
                        <select name="assigned_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="">Select admin...</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                        Assign
                    </button>
                </form>
            </div>

            <!-- Update Status & Priority -->
            <div class="mb-6 pb-6 border-b border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Update Status & Priority</h4>
                <form action="{{ route('admin.tickets.update', $ticket) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label for="priority" class="block text-xs font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority" id="priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            Update
                        </button>
                    </div>
                </form>
            </div>

            <!-- Admin Response -->
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Send Response</h4>
                <form action="{{ route('admin.tickets.response', $ticket) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <textarea name="admin_response" rows="5" required
                            placeholder="Type your response to the user here..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ old('admin_response', $ticket->admin_response) }}</textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                        Send Response
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

