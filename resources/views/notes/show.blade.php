@extends('layouts.app')

@section('title', $note->title)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('notes.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_my_notes') }}
            </a>
        </div>

        <!-- Note Details Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="p-6">
                <!-- Header with Actions -->
                <div class="flex justify-between items-start mb-6">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $note->title }}</h1>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('messages.created') }} {{ $note->created_at->format('d M Y') }}
                            @if($note->updated_at != $note->created_at)
                                <span class="text-gray-400">•</span>
                                <span>{{ __('messages.updated') }} {{ $note->updated_at->format('d M Y') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        @can('update', $note)
                            <a href="{{ route('notes.edit', $note) }}" class="inline-flex items-center px-3 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ __('messages.edit') }}
                            </a>
                        @endcan
                        @can('delete', $note)
                            <form action="{{ route('notes.destroy', $note) }}" method="POST" class="delete-note-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-3 mb-6 pb-6 border-b border-gray-200">
                    @if($note->is_public)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.public') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.private') }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst($note->status) }}
                    </span>
                    @if($note->price > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-base font-semibold bg-yellow-100 text-yellow-800">
                            {{ currency($note->price) }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            {{ __('messages.free') }}
                        </span>
                    @endif
                    @if($note->average_rating > 0)
                        <div class="inline-flex items-center gap-0.5 ml-2">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                            <span class="text-sm font-medium text-gray-700 ml-1">{{ $note->average_rating }}</span>
                            <span class="text-xs text-gray-500">({{ $note->total_reviews }} {{ Str::plural('review', $note->total_reviews) }})</span>
                        </div>
                    @endif
                </div>

                <!-- Tags -->
                @if($note->tags->count() > 0)
                    <div class="mb-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach($note->tags as $tag)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Author -->
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                            @if($note->user->avatar)
                                <img src="{{ $note->user->avatar }}" alt="{{ $note->user->name }}" class="w-10 h-10 rounded-full object-cover">
                            @else
                                <span class="text-sm font-semibold text-gray-600">{{ strtoupper(substr($note->user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Created by {{ $note->user->name }}</p>
                            @if($note->is_public)
                                <a href="{{ route('public.profile.show', $note->user->username) }}" class="text-xs text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                    View profile →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="prose max-w-none">
                    <div class="ql-editor text-gray-900 leading-relaxed">{!! $note->content !!}</div>
                </div>

                <!-- Attachments (if exists) -->
                @if($note->hasAttachments())
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Attachments ({{ $note->file_count }})
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($note->attachments as $attachment)
                                @php
                                    $filename = is_array($attachment) ? ($attachment['filename'] ?? 'Unknown') : basename($attachment);
                                @endphp
                                <a href="{{ route('notes.attachments.download', ['note' => $note->id, 'filename' => $filename]) }}" 
                                   class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-300 transition-all duration-200">
                                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $filename }}</p>
                                        @if(is_array($attachment) && isset($attachment['size']))
                                            <p class="text-xs text-gray-500">{{ number_format($attachment['size'] / 1024, 2) }} KB</p>
                                        @endif
                                    </div>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Rating/Review Summary (if public) -->
                @if($note->is_public && $note->total_reviews > 0)
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-base font-semibold text-gray-900">Rating Summary</h3>
                            <div class="flex items-center gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                                <span class="text-sm font-medium text-gray-700 ml-1">{{ $note->average_rating }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">{{ $note->total_reviews }} {{ Str::plural('review', $note->total_reviews) }} from buyers</p>
                        <a href="{{ route('marketplace.show', $note) }}" class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                            View all reviews in marketplace →
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation with SweetAlert2
    document.querySelectorAll('.delete-note-form').forEach(form => {
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
