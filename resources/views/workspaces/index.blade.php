@extends('layouts.app')

@section('title', 'Workspaces')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2 flex items-center gap-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Workspaces
                </h1>
                <p class="text-gray-600">Manage your personal, team, and organization workspaces</p>
            </div>
            <a href="{{ route('workspaces.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Workspace
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Owned Workspaces -->
        @if($ownedWorkspaces->count() > 0)
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">My Workspaces</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($ownedWorkspaces as $workspace)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900">
                                                <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-purple-600 transition-colors">
                                                    {{ $workspace->name }}
                                                </a>
                                            </h3>
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                @if($workspace->type === 'personal') bg-blue-100 text-blue-800
                                                @elseif($workspace->type === 'team') bg-green-100 text-green-800
                                                @else bg-purple-100 text-purple-800
                                                @endif">
                                                {{ ucfirst($workspace->type) }}
                                            </span>
                                        </div>
                                        @if($workspace->description)
                                            <p class="text-sm text-gray-600 line-clamp-2">{{ $workspace->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('workspaces.edit', $workspace) }}" class="text-gray-400 hover:text-purple-600 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ $workspace->notes_count }} notes
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        {{ $workspace->members()->count() }} members
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Member Workspaces -->
        @if($memberWorkspaces->count() > 0)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Workspaces I'm a Member Of</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($memberWorkspaces as $workspace)
                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900">
                                                <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-purple-600 transition-colors">
                                                    {{ $workspace->name }}
                                                </a>
                                            </h3>
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                @if($workspace->type === 'personal') bg-blue-100 text-blue-800
                                                @elseif($workspace->type === 'team') bg-green-100 text-green-800
                                                @else bg-purple-100 text-purple-800
                                                @endif">
                                                {{ ucfirst($workspace->type) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">Owner: {{ $workspace->owner->name }}</p>
                                        @if($workspace->description)
                                            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $workspace->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ $workspace->notes_count }} notes
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                        {{ ucfirst($workspace->pivot->role) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Empty State -->
        @if($ownedWorkspaces->count() === 0 && $memberWorkspaces->count() === 0)
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No workspaces yet</h3>
                <p class="text-gray-600 mb-6">Create workspaces to organize your notes for personal, team, or organization use</p>
                <a href="{{ route('workspaces.create') }}" class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                    Create Your First Workspace
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

