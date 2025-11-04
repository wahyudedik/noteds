@extends('layouts.app')

@section('title', 'AI Insights - MyNoteds')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('mynoteds.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.ai_insights') }}</h1>
            </div>
            <p class="text-gray-600">{{ __('messages.get_automatic_insights_description') }}</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-gray-900">{{ $statistics['total'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('messages.total_notes') }}</div>
            </div>
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">{{ $statistics['this_week'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('messages.this_week') }}</div>
            </div>
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-green-600">{{ $statistics['this_month'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 mt-1">{{ __('messages.this_month') }}</div>
            </div>
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-purple-600">
                    {{ $statistics['most_active_day']['count'] ?? 0 }}
                </div>
                <div class="text-sm text-gray-600 mt-1">
                    @if(isset($statistics['most_active_day']['date']))
                        {{ __('messages.most_active') }}: {{ \Carbon\Carbon::parse($statistics['most_active_day']['date'])->format('M d') }}
                    @else
                        {{ __('messages.most_active_day') }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Weekly Summary -->
        @if($weeklySummary)
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">{{ __('messages.weekly_summary') }}</h2>
                    </div>
                    <span class="text-sm text-gray-500 bg-gray-50 px-3 py-1 rounded-full">{{ $weeklySummary['period'] ?? '' }}</span>
                </div>

                @php
                    $parsed = $weeklySummary['parsed'] ?? null;
                @endphp

                @if($parsed && (!empty($parsed['topics']) || !empty($parsed['insights']) || !empty($parsed['activities'])))
                    <!-- Structured Summary with List Format -->
                    <div class="space-y-6">
                        @if(!empty($parsed['topics']))
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2 text-base">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ __('messages.main_topics_themes') }}
                                </h3>
                                <ul class="space-y-2.5 list-none">
                                    @foreach($parsed['topics'] as $index => $topic)
                                        <li class="text-gray-700 flex items-start gap-3 bg-white rounded-md px-3 py-2.5 border border-blue-100 hover:border-blue-200 transition-colors">
                                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-semibold mt-0.5">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="flex-1 pt-0.5">{{ $topic }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($parsed['insights']))
                            <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2 text-base">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    {{ __('messages.key_insights_takeaways') }}
                                </h3>
                                <ul class="space-y-2.5 list-none">
                                    @foreach($parsed['insights'] as $index => $insight)
                                        <li class="text-gray-700 flex items-start gap-3 bg-white rounded-md px-3 py-2.5 border border-green-100 hover:border-green-200 transition-colors">
                                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-semibold mt-0.5">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="flex-1 pt-0.5">{{ $insight }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($parsed['activities']))
                            <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                                <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2 text-base">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('messages.notable_activities_updates') }}
                                </h3>
                                <ul class="space-y-2.5 list-none">
                                    @foreach($parsed['activities'] as $index => $activity)
                                        <li class="text-gray-700 flex items-start gap-3 bg-white rounded-md px-3 py-2.5 border border-purple-100 hover:border-purple-200 transition-colors">
                                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center text-xs font-semibold mt-0.5">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="flex-1 pt-0.5">{{ $activity }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Fallback: Plain text display as list -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $weeklySummary['summary'] ?? '' }}</p>
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>{{ __('messages.based_on_notes_this_week', ['count' => $weeklySummary['notes_count'] ?? 0]) }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-r-lg p-6 mb-6">
                <p class="text-yellow-800">
                    <strong>{{ __('messages.note') }}:</strong> {{ __('messages.weekly_summary_will_be_generated') }}
                </p>
            </div>
        @endif

        <!-- Topics -->
        @if(!empty($topics))
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('messages.detected_topics') }}</h2>
                <div class="flex flex-wrap gap-3">
                    @foreach($topics as $topic)
                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gradient-to-r from-purple-100 to-blue-100 text-purple-800 border border-purple-200">
                            {{ $topic }}
                        </span>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-6">
                <p class="text-blue-800">
                    <strong>{{ __('messages.tip') }}:</strong> {{ __('messages.topics_will_be_automatically_detected') }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

