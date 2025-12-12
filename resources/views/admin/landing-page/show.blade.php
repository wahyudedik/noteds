@extends('layouts.app')

@section('title', __('messages.view_landing_section'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $landingPage->title ?: __('messages.landing_page_section') }}</h1>
                    <p class="mt-2 text-base text-gray-600">{{ __('messages.view_section_details') }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.landing-page.edit', $landingPage) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        {{ __('messages.edit') }}
                    </a>
                    <a href="{{ route('admin.landing-page.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        ← {{ __('messages.back_to_list') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Section Details Card -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
                    <!-- Section Type & Status -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.section_type') }}</label>
                            <p class="mt-2 text-sm font-medium text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $landingPage->section_type_label }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</label>
                            <p class="mt-2">
                                @if($landingPage->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('messages.active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('messages.inactive') }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Title & Subtitle -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.title') }}</label>
                        <p class="mt-2 text-sm text-gray-900">{{ $landingPage->title ?: '—' }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.subtitle_description') }}</label>
                        <p class="mt-2 text-sm text-gray-900">{{ $landingPage->subtitle ?: '—' }}</p>
                    </div>

                    <!-- Content (JSON Preview) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.content') }}</label>
                        <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 overflow-auto max-h-96">
                            <pre class="text-xs text-gray-700 font-mono">{{ json_encode($landingPage->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.display_order') }}</label>
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $landingPage->order ?? '0' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.alignment') }}</label>
                            <p class="mt-2 text-sm font-medium text-gray-900">{{ $landingPage->alignment ?: '—' }}</p>
                        </div>
                    </div>

                    <!-- Colors -->
                    @if($landingPage->background_color || $landingPage->text_color)
                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                            @if($landingPage->background_color)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.background_color') }}</label>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded border border-gray-300" style="background-color: {{ $landingPage->background_color }}"></div>
                                        <p class="text-sm font-mono text-gray-900">{{ $landingPage->background_color }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($landingPage->text_color)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.text_color') }}</label>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="w-8 h-8 rounded border border-gray-300" style="background-color: {{ $landingPage->text_color }}"></div>
                                        <p class="text-sm font-mono text-gray-900">{{ $landingPage->text_color }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Image -->
                    @if($landingPage->image_url)
                        <div class="pt-4 border-t border-gray-200">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">{{ __('messages.image') }}</label>
                            <img src="{{ $landingPage->image_url }}" alt="{{ $landingPage->title }}" class="rounded-lg max-h-64 w-full object-cover">
                            <p class="mt-2 text-xs text-gray-500 font-mono break-all">{{ $landingPage->image_url }}</p>
                        </div>
                    @endif

                    <!-- Valid Period -->
                    @if($landingPage->section_type === 'promo' && ($landingPage->valid_from || $landingPage->valid_until))
                        <div class="pt-4 border-t border-gray-200">
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.valid_period') }}</label>
                            <div class="mt-2 grid grid-cols-2 gap-4">
                                @if($landingPage->valid_from)
                                    <div>
                                        <p class="text-xs text-gray-600">{{ __('messages.from') }}</p>
                                        <p class="text-sm font-medium text-gray-900">{{ localized_time($landingPage->valid_from, 'date') }}</p>
                                    </div>
                                @endif
                                @if($landingPage->valid_until)
                                    <div>
                                        <p class="text-xs text-gray-600">{{ __('messages.until') }}</p>
                                        <p class="text-sm font-medium text-gray-900">{{ localized_time($landingPage->valid_until, 'date') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Creator Info -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('messages.creator_info') }}</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.created_by') }}</label>
                            <div class="mt-2 flex items-center gap-2">
                                <img src="{{ $landingPage->creator->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($landingPage->creator->name) }}" alt="{{ $landingPage->creator->name }}" class="w-8 h-8 rounded-full">
                                <p class="text-sm font-medium text-gray-900">{{ $landingPage->creator->name }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.created_at') }}</label>
                            <p class="text-sm text-gray-900">{{ localized_time($landingPage->created_at) }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.last_updated') }}</label>
                            <p class="text-sm text-gray-900">{{ localized_time($landingPage->updated_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('messages.preview') }}</h3>
                    <a href="{{ route('welcome') }}" target="_blank" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ __('messages.view_homepage') }}
                    </a>
                </div>

                <!-- Actions -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('messages.actions') }}</h3>
                    <div class="space-y-2">
                        <a href="{{ route('admin.landing-page.edit', $landingPage) }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('messages.edit') }}
                        </a>
                        <form action="{{ route('admin.landing-page.destroy', $landingPage) }}" method="POST" class="w-full delete-section-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                {{ __('messages.delete') }}
                            </button>
                        </form>
                        <a href="{{ route('admin.landing-page.index') }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                            ← {{ __('messages.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-section-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formElement = this;
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formElement.submit();
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this section?')) {
                    formElement.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection
