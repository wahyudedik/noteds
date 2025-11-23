@extends('layouts.app')

@section('title', $contest->title . ' - Contest')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('contests.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Contests
            </a>
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

        <!-- Contest Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
            @if($contest->banner_image)
                <img src="{{ Storage::url($contest->banner_image) }}" alt="{{ $contest->title }}" class="w-full h-64 object-cover">
            @else
                <div class="w-full h-64 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                    <span class="text-9xl">🏆</span>
                </div>
            @endif

            <div class="p-8">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $contest->title }}</h1>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                @if($contest->status === 'open') bg-green-100 text-green-800
                                @elseif($contest->status === 'voting') bg-blue-100 text-blue-800
                                @elseif($contest->status === 'closed') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($contest->status) }}
                            </span>
                            @if($contest->type === 'monthly')
                                <span>📅 Monthly Challenge</span>
                            @elseif($contest->type === 'themed')
                                <span>🎨 Theme: {{ $contest->theme }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="prose max-w-none mb-6">
                    <p class="text-gray-700 text-lg">{{ $contest->description }}</p>
                </div>

                @if($contest->rules)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Rules</h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            @foreach($contest->rules as $rule)
                                <li>{{ $rule }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($contest->prizes)
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Prizes</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($contest->prizes as $index => $prize)
                                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="text-2xl font-bold text-yellow-600 mb-1">
                                        @if($index === 0) 🥇
                                        @elseif($index === 1) 🥈
                                        @elseif($index === 2) 🥉
                                        @else #{{ $index + 1 }}
                                        @endif
                                    </div>
                                    @if($prize['type'] === 'cash')
                                        <div class="font-semibold text-gray-900">${{ number_format($prize['value'], 2) }}</div>
                                    @elseif($prize['type'] === 'credits')
                                        <div class="font-semibold text-gray-900">{{ $prize['value'] }} Credits</div>
                                    @elseif($prize['type'] === 'badge')
                                        <div class="font-semibold text-gray-900">Badge</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-6">
                    <div>
                        <div class="font-semibold text-gray-900">Start Date</div>
                        <div>{{ $contest->start_date ? $contest->start_date->format('M d, Y') : 'TBD' }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">End Date</div>
                        <div>{{ $contest->end_date ? $contest->end_date->format('M d, Y') : 'TBD' }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Entries</div>
                        <div>{{ $contest->entries()->count() }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Votes</div>
                        <div>{{ $contest->votes()->count() }}</div>
                    </div>
                </div>

                @if($contest->status === 'open' && auth()->check() && $canSubmit['can_submit'] && !$userEntry)
                    <a href="{{ route('contests.submit', $contest) }}" 
                       class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors">
                        Submit Your Entry
                    </a>
                @elseif($userEntry)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-blue-800 font-medium">✓ You have submitted an entry</p>
                        <p class="text-sm text-blue-600 mt-1">Status: {{ ucfirst($userEntry->status) }}</p>
                    </div>
                @elseif(!$canSubmit['can_submit'])
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-yellow-800">{{ implode(' ', $canSubmit['reasons']) }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Winners Section -->
        @if($contest->winners->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">🏆 Winners</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($contest->winners as $winner)
                            <div class="text-center">
                                <div class="text-4xl mb-2">
                                    @if($winner->position === 1) 🥇
                                    @elseif($winner->position === 2) 🥈
                                    @elseif($winner->position === 3) 🥉
                                    @else #{{ $winner->position }}
                                    @endif
                                </div>
                                <div class="font-semibold text-gray-900">{{ $winner->user->name }}</div>
                                <div class="text-sm text-gray-600">{{ $winner->entry->note->title }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Top Entries -->
        @if($contest->status === 'voting' || $contest->status === 'closed')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Top Entries</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($topEntries as $entry)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="text-2xl font-bold text-gray-400">#{{ $loop->iteration }}</div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900">{{ $entry->note->title }}</div>
                                        <div class="text-sm text-gray-600">by {{ $entry->user->name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-blue-600">{{ $entry->vote_count }}</div>
                                        <div class="text-xs text-gray-500">votes</div>
                                    </div>
                                </div>
                                @if($contest->isVotingOpen() && auth()->check() && !$userVote)
                                    <form action="{{ route('contests.vote', $contest) }}" method="POST" class="ml-4">
                                        @csrf
                                        <input type="hidden" name="entry_id" value="{{ $entry->id }}">
                                        <button type="submit" 
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md">
                                            Vote
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-8">No entries yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

