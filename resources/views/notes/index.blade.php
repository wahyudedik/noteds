@extends('layouts.app')

@section('title', __('messages.my_notes'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.notes') }}</h1>
                <p class="mt-2 text-base text-gray-600">{{ __('messages.manage_notes', [], app()->getLocale()) ?: 'Manage and organize all your notes' }}</p>
            </div>
            <a href="{{ route('notes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('messages.create') }} {{ __('messages.notes') }}
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Notes Grid -->
        @if($notes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($notes as $note)
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                        <div class="p-6">
                            <!-- Title and Content -->
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                    <a href="{{ route('notes.show', $note) }}">{{ $note->title }}</a>
                                </h3>
                                <p class="text-sm text-gray-600 line-clamp-3">{!! Str::limit(strip_tags($note->content), 120) !!}</p>
                            </div>

                            <!-- Tags -->
                            @if($note->tags->count() > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach($note->tags->take(3) as $tag)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                    @if($note->tags->count() > 3)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            +{{ $note->tags->count() - 3 }} {{ __('messages.more') }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Badges -->
                            <div class="flex flex-wrap items-center gap-2 mb-4">
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 font-semibold">
                                        {{ currency($note->price) }}
                                    </span>
                                @endif
                                @if($note->hasAttachments())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800" title="{{ $note->file_count }} {{ $note->file_count == 1 ? __('messages.file') : __('messages.files') }}">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        {{ $note->file_count }}
                                    </span>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <span class="text-xs text-gray-500">{{ localized_diff_for_humans($note->created_at) }}</span>
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('notes.show', $note) }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                        {{ __('messages.view') }}
                                    </a>
                                    <a href="{{ route('notes.edit', $note) }}" class="text-sm font-medium text-green-600 hover:text-green-700 transition-colors duration-200">
                                        {{ __('messages.edit') }}
                                    </a>
                                    <form action="{{ route('notes.destroy', $note) }}" method="POST" class="inline delete-note-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 transition-colors duration-200 focus:outline-none">
                                            {{ __('messages.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $notes->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 text-center py-16 px-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_notes_yet') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('messages.start_creating') }}</p>
                <div class="mt-6">
                    <a href="{{ route('notes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('messages.create_your_first_note') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete confirmation with SweetAlert2
    document.querySelectorAll('.delete-note-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            
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
                        form.submit();
                    }
                });
            } else {
                // Fallback to browser confirm if Swal not loaded
                if (confirm('{{ __('messages.delete_confirmation') }}')) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
