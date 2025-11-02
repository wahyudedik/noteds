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
                    <h1 class="text-3xl font-bold text-gray-900">Ticket Details</h1>
                </div>
                <div class="flex items-center gap-2">
                    @if($supportTicket->isOpen() && $supportTicket->user_id === auth()->id())
                        <a href="{{ route('support-tickets.edit', $supportTicket) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50">
                            Edit
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

        <!-- Ticket Details -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold text-gray-900">{{ $supportTicket->title }}</h2>
                        <!-- Status Badge -->
                        @if($supportTicket->status === 'open')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Open
                            </span>
                        @elseif($supportTicket->status === 'in_progress')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                In Progress
                            </span>
                        @elseif($supportTicket->status === 'resolved')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                Resolved
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                Closed
                            </span>
                        @endif
                        <!-- Priority Badge -->
                        @if($supportTicket->priority === 'urgent')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Urgent
                            </span>
                        @elseif($supportTicket->priority === 'high')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                High
                            </span>
                        @elseif($supportTicket->priority === 'medium')
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
                <div class="prose max-w-none mb-6">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $supportTicket->description }}</p>
                </div>

                <!-- Meta Information -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700">Created:</span>
                            <span class="text-gray-600">{{ $supportTicket->created_at->format('F d, Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700">Last Updated:</span>
                            <span class="text-gray-600">{{ $supportTicket->updated_at->format('F d, Y H:i') }}</span>
                        </div>
                        @if($supportTicket->assignedAdmin)
                            <div>
                                <span class="font-medium text-gray-700">Assigned To:</span>
                                <span class="text-gray-600">{{ $supportTicket->assignedAdmin->name }}</span>
                            </div>
                        @endif
                        @if($supportTicket->closedByUser)
                            <div>
                                <span class="font-medium text-gray-700">Closed By:</span>
                                <span class="text-gray-600">{{ $supportTicket->closedByUser->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Links -->
                @if($supportTicket->links && count($supportTicket->links) > 0)
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Related Links</h3>
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

        <!-- Admin Response -->
        @if($supportTicket->admin_response)
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 mb-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Admin Response</h3>
                        <div class="prose max-w-none">
                            <p class="text-blue-800 whitespace-pre-wrap">{{ $supportTicket->admin_response }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center mb-6">
                <p class="text-gray-600">No admin response yet. We'll get back to you soon!</p>
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
                    title: '{{ __('messages.are_you_sure') }}',
                    text: '{{ __('messages.delete_confirmation') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '{{ __('messages.yes_delete') }}',
                    cancelButtonText: '{{ __('messages.no_cancel') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formElement.submit();
                    }
                });
            } else {
                if (confirm('{{ __('messages.delete_confirmation') }}')) {
                    formElement.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection

