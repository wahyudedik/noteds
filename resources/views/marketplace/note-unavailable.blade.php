@extends('layouts.app')

@section('title', __('messages.note_unavailable_title'))

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 text-sm text-gray-500 mb-6">
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.back_to_marketplace') }}
                </a>
                <span>•</span>
                <span class="uppercase tracking-wider text-xs text-gray-400">{{ __('messages.note_unavailable_breadcrumb') }}</span>
            </div>

            <div class="bg-white shadow-xl rounded-3xl border border-gray-200 overflow-hidden">
                <div class="relative bg-gradient-to-r from-rose-500 via-purple-500 to-blue-500 p-8">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_top,_white,transparent_60%)]"></div>
                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6">
                        <div>
                            <p class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold uppercase tracking-wide rounded-full bg-white/20 text-white/90 backdrop-blur">
                                {{ __('messages.note_unavailable_badge') }}
                            </p>
                            <h1 class="mt-4 text-3xl sm:text-4xl font-semibold text-white">
                                {{ $note->title }}
                            </h1>
                            <p class="mt-3 text-white/80 text-sm">
                                {{ __('messages.note_unavailable_subtitle') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-start sm:items-end gap-2 text-white/80 text-sm">
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                {{ __('messages.note_status_label') }}:
                                <span class="font-semibold">
                                    {{ $status === 'inactive' ? __('messages.note_status_inactive') : __('messages.note_status_private') }}
                                </span>
                            </span>
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                {{ __('messages.note_owner_label') }}:
                                <span class="font-semibold">{{ $note->user->name }} ({{ '@' . $note->user->username }})</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M12 18a9 9 0 110-18 9 9 0 010 18z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.note_unavailable_reason_title') }}</h2>
                                <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                                    @if(!$isPublic)
                                        {{ __('messages.note_unavailable_reason_private') }}
                                    @elseif($status === 'inactive')
                                        {{ __('messages.note_unavailable_reason_inactive') }}
                                    @else
                                        {{ __('messages.note_unavailable_reason_generic') }}
                                    @endif
                                </p>
                                @if($pendingReports > 0)
                                    <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M5.07 19H18.93a2 2 0 001.74-3.006L13.74 4.994a2 2 0 00-3.48 0L3.33 15.994A2 2 0 005.07 19z" />
                                        </svg>
                                        {{ trans_choice('messages.note_unavailable_pending_reports', $pendingReports, ['count' => $pendingReports]) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border border-gray-200 rounded-2xl p-6 bg-white shadow-sm">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">
                                {{ __('messages.note_unavailable_next_steps_title') }}
                            </h3>
                            <ul class="space-y-3 text-sm text-gray-600">
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 w-2 h-2 rounded-full bg-blue-500"></span>
                                    {{ __('messages.note_unavailable_next_steps_browse') }}
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 w-2 h-2 rounded-full bg-blue-500"></span>
                                    {{ __('messages.note_unavailable_next_steps_support') }}
                                </li>
                                @if($isOwner)
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1 w-2 h-2 rounded-full bg-blue-500"></span>
                                        {{ __('messages.note_unavailable_next_steps_owner') }}
                                    </li>
                                @endif
                            </ul>
                            <div class="mt-5 flex flex-wrap gap-3">
                                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-medium hover:bg-blue-700">
                                    {{ __('messages.note_unavailable_button_explore') }}
                                </a>
                                <a href="{{ route('support-tickets.create') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 text-gray-700 text-sm font-medium hover:border-gray-400">
                                    {{ __('messages.note_unavailable_button_support') }}
                                </a>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-2xl p-6 bg-white shadow-sm">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-3">
                                {{ __('messages.note_unavailable_metadata_title') }}
                            </h3>
                            <dl class="space-y-3 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ __('messages.note_unavailable_created') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $note->created_at->format('d M Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ __('messages.note_unavailable_updated') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ $note->updated_at->format('d M Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ __('messages.note_unavailable_price') }}</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ $note->price > 0 ? currency($note->price) : __('messages.free') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ __('messages.note_unavailable_visibility') }}</dt>
                                    <dd class="font-medium text-gray-900">
                                        {{ $isPublic ? __('messages.visibility_public') : __('messages.visibility_private') }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('messages.note_unavailable_suggestions_title') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach(\App\Models\Note::publicOnly()->where('id', '!=', $note->id)->latest()->take(3)->with('user', 'tags')->get() as $suggestedNote)
                        <a href="{{ route('marketplace.show', $suggestedNote) }}"
                            class="bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition p-6 flex flex-col gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 text-white flex items-center justify-center text-sm font-semibold">
                                    {{ strtoupper(substr($suggestedNote->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ Str::limit($suggestedNote->title, 60) }}</h3>
                                    <p class="text-xs text-gray-500">{{ $suggestedNote->user->name }}</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 leading-relaxed">{{ Str::limit(strip_tags($suggestedNote->summary ?? $suggestedNote->content), 120) }}</p>
                            <div class="flex items-center justify-between text-sm font-semibold">
                                <span class="text-blue-600">{{ $suggestedNote->price > 0 ? currency($suggestedNote->price) : __('messages.free') }}</span>
                                <span class="inline-flex items-center gap-1 text-gray-400 text-xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

