@extends('layouts.app')

@section('title', 'MyNoteds - AI Memory Platform')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        MyNoteds
                    </h1>
                    <p class="text-gray-600">Your AI-powered memory platform. Ask questions, discover insights, and connect your thoughts.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Premium
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('mynoteds.ask') }}" class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Ask Your Notes</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-purple-100 text-sm mb-4">Ask natural language questions about your notes</p>
                <div class="text-xs text-purple-200">Example: "Apa yang kubicarakan dengan Rina minggu lalu?"</div>
            </a>

            <a href="{{ route('mynoteds.search') }}" class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Semantic Search</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <p class="text-blue-100 text-sm mb-4">Find notes using AI-powered semantic understanding</p>
                <div class="text-xs text-blue-200">Search by meaning, not just keywords</div>
            </a>

            <a href="{{ route('mynoteds.insights') }}" class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">AI Insights</h3>
                    <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <p class="text-green-100 text-sm mb-4">Get automatic insights and weekly summaries</p>
                <div class="text-xs text-green-200">Coming soon</div>
            </a>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total_notes'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Total Notes</div>
            </div>
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['public_notes'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Public Notes</div>
            </div>
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['private_notes'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Private Notes</div>
            </div>
            <div class="bg-white rounded-lg p-6 border border-gray-200 shadow-sm">
                <div class="text-2xl font-bold text-green-600">{{ $stats['total_tags'] }}</div>
                <div class="text-sm text-gray-600 mt-1">Tags</div>
            </div>
        </div>

        <!-- Recent Notes -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Your Notes</h2>
                    <a href="{{ route('notes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Note
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if($notes->count() > 0)
                    <div class="space-y-4">
                        @foreach($notes as $note)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-2">
                                            <a href="{{ route('notes.show', $note) }}" class="hover:text-blue-600 transition-colors duration-200">
                                                {{ $note->title }}
                                            </a>
                                        </h3>
                                        @if($note->summary)
                                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $note->summary }}</p>
                                        @endif
                                        <div class="flex items-center gap-4 text-xs text-gray-500">
                                            <span>{{ $note->created_at->diffForHumans() }}</span>
                                            @if($note->is_public)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-100 text-green-800">
                                                    Public
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-800">
                                                    Private
                                                </span>
                                            @endif
                                        </div>
                                        @if($note->tags->count() > 0)
                                            <div class="flex flex-wrap gap-2 mt-3">
                                                @foreach($note->tags->take(5) as $tag)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $notes->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No notes yet</h3>
                        <p class="text-gray-600 mb-6">Start creating notes to unlock the power of AI Memory Platform</p>
                        <a href="{{ route('notes.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                            Create Your First Note
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

