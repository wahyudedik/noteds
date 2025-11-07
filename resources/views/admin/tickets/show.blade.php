@extends('layouts.app')

@section('title', __('messages.admin_ticket_details'))

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
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.ticket') }} #{{ substr($ticket->id, 0, 8) }}</h1>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Ticket Details Header -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-xl font-bold text-gray-900">{{ $ticket->title }}</h2>
                        @if($ticket->status === 'open')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ __('messages.open') }}
                            </span>
                        @elseif($ticket->status === 'in_progress')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ __('messages.in_progress') }}
                            </span>
                        @elseif($ticket->status === 'resolved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ __('messages.resolved') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ __('messages.closed') }}
                            </span>
                        @endif
                        @if($ticket->priority === 'urgent')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ __('messages.urgent') }}
                            </span>
                        @elseif($ticket->priority === 'high')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                {{ __('messages.high') }}
                            </span>
                        @elseif($ticket->priority === 'medium')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                {{ __('messages.medium') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ __('messages.low') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.from') }}</h3>
                    <div class="flex items-center gap-3">
                        @if($ticket->user->avatar)
                            <img src="{{ $ticket->user->avatar }}" alt="{{ $ticket->user->name }}" class="w-10 h-10 rounded-full">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                <span class="text-sm font-semibold text-white">{{ strtoupper(substr($ticket->user->name, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $ticket->user->name }}</span>
                                @if($ticket->user->hasPremium())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-400 to-orange-500 text-white" title="Premium User - Priority Support">
                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Premium - Priority Support
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                        </div>
                    </div>
                </div>

                @if($ticket->links && count($ticket->links) > 0)
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('messages.related_links') }}</h3>
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
                            <span class="font-medium text-gray-700">{{ __('messages.created') }}:</span>
                            <span class="text-gray-600 ml-2">{{ $ticket->created_at->format('F d, Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">{{ __('messages.last_updated') }}:</span>
                            <span class="text-gray-600 ml-2">{{ $ticket->updated_at->format('F d, Y H:i') }}</span>
                        </div>
                        @if($ticket->assignedAdmin)
                            <div>
                                <span class="font-medium text-gray-700">{{ __('messages.assigned_to') }}:</span>
                                <span class="text-gray-600 ml-2">{{ $ticket->assignedAdmin->name }}</span>
                            </div>
                        @endif
                        @if($ticket->closedByUser)
                            <div>
                                <span class="font-medium text-gray-700">{{ __('messages.closed_by') }}:</span>
                                <span class="text-gray-600 ml-2">{{ $ticket->closedByUser->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversation Thread -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.conversation') }}</h3>
            </div>
            <div class="divide-y divide-gray-200">
                <!-- Original Ticket Message -->
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            @if($ticket->user->avatar)
                                <img src="{{ $ticket->user->avatar }}" alt="{{ $ticket->user->name }}" class="w-10 h-10 rounded-full">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">{{ strtoupper(substr($ticket->user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-semibold text-gray-900">{{ $ticket->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $ticket->created_at->format('M d, Y H:i') }}</span>
                                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ __('messages.original_message') }}</span>
                            </div>
                            <div class="prose max-w-none">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                @forelse($ticket->replies as $reply)
                    <div class="p-6 {{ $reply->is_admin ? 'bg-green-50' : 'bg-white' }}">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                @if($reply->user->avatar)
                                    <img src="{{ $reply->user->avatar }}" alt="{{ $reply->user->name }}" class="w-10 h-10 rounded-full">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $reply->is_admin ? 'from-green-400 to-blue-500' : 'from-blue-400 to-purple-500' }} flex items-center justify-center">
                                        <span class="text-sm font-semibold text-white">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-semibold text-gray-900">{{ $reply->user->name }}</span>
                                    @if($reply->is_admin)
                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded">{{ __('messages.admin') }}</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ __('messages.user') }}</span>
                                    @endif
                                    <span class="text-xs text-gray-500">{{ $reply->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="prose max-w-none">
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ $reply->message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">
                        {{ __('messages.no_replies_yet') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.admin_actions') }}</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Assign Ticket -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('messages.assign_to_admin') }}</h4>
                    <form action="{{ route('admin.tickets.assign', $ticket) }}" method="POST" class="flex items-end gap-2">
                        @csrf
                        <div class="flex-1">
                            <select name="assigned_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="">{{ __('messages.select_admin') }}</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            {{ __('messages.assign') }}
                        </button>
                    </form>
                </div>

                <!-- Update Status & Priority -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">{{ __('messages.update_status_priority') }}</h4>
                    <form action="{{ route('admin.tickets.update', $ticket) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status" class="block text-xs font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                            <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>{{ __('messages.open') }}</option>
                                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>{{ __('messages.in_progress') }}</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>{{ __('messages.resolved') }}</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>{{ __('messages.closed') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-xs font-medium text-gray-700 mb-2">{{ __('messages.priority') }}</label>
                            <select name="priority" id="priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="low" {{ $ticket->priority === 'low' ? 'selected' : '' }}>{{ __('messages.low') }}</option>
                                <option value="medium" {{ $ticket->priority === 'medium' ? 'selected' : '' }}>{{ __('messages.medium') }}</option>
                                <option value="high" {{ $ticket->priority === 'high' ? 'selected' : '' }}>{{ __('messages.high') }}</option>
                                <option value="urgent" {{ $ticket->priority === 'urgent' ? 'selected' : '' }}>{{ __('messages.urgent') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                {{ __('messages.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reply Form -->
        @if($ticket->status !== 'closed')
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.send_reply') }}</h3>
                <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.your_message') }}</label>
                        <textarea name="message" id="message" rows="5" required minlength="10"
                            :placeholder="__('messages.type_your_response')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.minimum_10_characters') }}</p>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        {{ __('messages.send_reply') }}
                    </button>
                </form>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                <p class="text-gray-600">{{ __('messages.ticket_closed_no_replies') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
