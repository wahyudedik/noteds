@extends('layouts.app')

@section('title', $contest->title . ' - Contest Details')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.contests.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Contests
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $contest->title }}</h2>
                    <a href="{{ route('admin.contests.edit', $contest) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <div class="text-sm text-gray-500">Status</div>
                        <div class="font-semibold">{{ ucfirst($contest->status) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Entries</div>
                        <div class="font-semibold">{{ $contest->entries()->count() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Votes</div>
                        <div class="font-semibold">{{ $contest->votes()->count() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Winners</div>
                        <div class="font-semibold">{{ $contest->winners()->count() }}</div>
                    </div>
                </div>

                @if($contest->status === 'voting' && $contest->approvedEntries()->count() > 0)
                    <div class="mb-6">
                        <form action="{{ route('admin.contests.select-winners', $contest) }}" method="POST" onsubmit="return confirm('Select winners based on current vote counts?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                Select Winners
                            </button>
                        </form>
                    </div>
                @endif

                @if($contest->status === 'closed' && $contest->winners()->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Winners</h3>
                        <div class="space-y-4">
                            @foreach($contest->winners as $winner)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-semibold text-gray-900">#{{ $winner->position }} - {{ $winner->user->name }}</div>
                                            <div class="text-sm text-gray-600">{{ $winner->entry->note->title }}</div>
                                            <div class="text-sm text-gray-500">Votes: {{ $winner->entry->vote_count }}</div>
                                        </div>
                                        @if(!$winner->prizes_distributed)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                                Prizes Not Distributed
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                Prizes Distributed
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($contest->winners()->where('prizes_distributed', false)->count() > 0)
                            <div class="mt-4">
                                <form action="{{ route('admin.contests.distribute-prizes', $contest) }}" method="POST" onsubmit="return confirm('Distribute prizes to all winners?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
                                        Distribute Prizes
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex gap-4">
            <a href="{{ route('admin.contests.entries', $contest) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                View Entries ({{ $contest->entries()->count() }})
            </a>
        </div>
    </div>
</div>
@endsection

