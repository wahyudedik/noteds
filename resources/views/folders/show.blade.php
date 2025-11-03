@extends('layouts.app')

@section('title', $folder->name)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('folders.index') }}" class="text-gray-500 hover:text-gray-700 inline-flex items-center mb-4">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Folders
            </a>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($folder->color)
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: {{ $folder->color }}20;">
                            <svg class="w-7 h-7" style="color: {{ $folder->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $folder->name }}</h1>
                        @if($folder->description)
                            <p class="text-gray-600 mt-1">{{ $folder->description }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('folders.edit', $folder) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                </div>
            </div>
        </div>

        <!-- Subfolders -->
        @if($folder->children->count() > 0)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Subfolders</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($folder->children as $child)
                        <a href="{{ route('folders.show', $child) }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                @if($child->color)
                                    <div class="w-8 h-8 rounded flex items-center justify-center" style="background-color: {{ $child->color }}20;">
                                        <svg class="w-5 h-5" style="color: {{ $child->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $child->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $child->notes()->count() }} notes</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Notes -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Notes in this folder</h2>
                    <span class="text-sm text-gray-600">{{ $folder->notes->count() }} notes</span>
                </div>
            </div>
            <div class="p-6">
                @if($folder->notes->count() > 0)
                    <div class="space-y-4">
                        @foreach($folder->notes as $note)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <h3 class="font-semibold text-gray-900 mb-2">
                                    <a href="{{ route('notes.show', $note) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $note->title }}
                                    </a>
                                </h3>
                                @if($note->summary)
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $note->summary }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>{{ $note->created_at->diffForHumans() }}</span>
                                    @if($note->is_public)
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800">Public</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800">Private</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No notes in this folder yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

