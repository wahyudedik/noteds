@extends('layouts.app')

@section('title', 'Support Ticket Details')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('support-tickets.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.ticket_details') }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    @if($supportTicket->isOpen() && $supportTicket->user_id === auth()->id())
                        <a href="{{ route('support-tickets.edit', $supportTicket) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50">
                            {{ __('messages.edit') }}
                        </a>
                    @endif
                    @if($supportTicket->isOpen() && $supportTicket->user_id === auth()->id())
                        <form action="{{ route('support-tickets.destroy', $supportTicket) }}" method="POST" class="inline-block delete-ticket-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50">
                                {{ __('messages.delete') }}
                            </button>
                        </form>
                    @endif
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
                        <h2 class="text-xl font-bold text-gray-900">{{ $supportTicket->title }}</h2>
                        <!-- Status Badge -->
                        @if($supportTicket->status === 'open')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                {{ __('messages.open') }}
                            </span>
                        @elseif($supportTicket->status === 'in_progress')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ __('messages.in_progress') }}
                            </span>
                        @elseif($supportTicket->status === 'resolved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ __('messages.resolved') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ __('messages.closed') }}
                            </span>
                        @endif
                        <!-- Priority Badge -->
                        @if($supportTicket->priority === 'urgent')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ __('messages.urgent') }}
                            </span>
                        @elseif($supportTicket->priority === 'high')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                {{ __('messages.high') }}
                            </span>
                        @elseif($supportTicket->priority === 'medium')
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
                <!-- Meta Information -->
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <span class="font-medium text-gray-700">{{ __('messages.created') }}:</span>
                        <span class="text-gray-600 ml-2">{{ $supportTicket->created_at->format('F d, Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">{{ __('messages.last_updated') }}:</span>
                        <span class="text-gray-600 ml-2">{{ $supportTicket->updated_at->format('F d, Y H:i') }}</span>
                    </div>
                    @if($supportTicket->assignedAdmin)
                        <div>
                            <span class="font-medium text-gray-700">{{ __('messages.assigned_to') }}:</span>
                            <span class="text-gray-600 ml-2">{{ $supportTicket->assignedAdmin->name }}</span>
                        </div>
                    @endif
                    @if($supportTicket->closedByUser)
                        <div>
                            <span class="font-medium text-gray-700">{{ __('messages.closed_by') }}:</span>
                            <span class="text-gray-600 ml-2">{{ $supportTicket->closedByUser->name }}</span>
                        </div>
                    @endif
                </div>

                <!-- Links -->
                @if($supportTicket->links && count($supportTicket->links) > 0)
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('messages.related_links_optional') }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($supportTicket->links as $link)
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
                            @if($supportTicket->user->avatar)
                                <img src="{{ $supportTicket->user->avatar }}" alt="{{ $supportTicket->user->name }}" class="w-10 h-10 rounded-full">
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">{{ strtoupper(substr($supportTicket->user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-semibold text-gray-900">{{ $supportTicket->user->name }}</span>
                                <span class="text-xs text-gray-500">{{ $supportTicket->created_at->format('M d, Y H:i') }}</span>
                                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">{{ __('messages.original_message') }}</span>
                            </div>
                            <div class="prose max-w-none">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $supportTicket->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                @forelse($supportTicket->replies as $reply)
                    <div class="p-6 {{ $reply->is_admin ? 'bg-blue-50' : 'bg-white' }}">
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

        <!-- Reply Form -->
        @if($supportTicket->status !== 'closed')
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.add_reply') }}</h3>
                <form action="{{ route('support-tickets.reply', $supportTicket) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.your_message') }}</label>
                        <textarea name="message" id="message" rows="5" required minlength="10"
                            :placeholder="__('messages.type_your_reply_here')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.minimum_characters_required') }}</p>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation with SweetAlert2
    document.querySelectorAll('.delete-ticket-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formElement = this;
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formElement.submit();
                    }
                });
            } else {
                if (confirm(@json(__('messages.delete_ticket_confirm')))) {
                    formElement.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
