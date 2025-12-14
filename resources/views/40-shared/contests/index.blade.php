@extends('40-shared/layouts/app')

@section('title', 'Contests')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Contests</h1>
            <p class="mt-2 text-base text-gray-600">Participate in monthly challenges and themed contests to win prizes!</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($contests as $contest)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    @if($contest->banner_image)
                        <img src="{{ Storage::url($contest->banner_image) }}" alt="{{ $contest->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <span class="text-6xl">🏆</span>
                        </div>
                    @endif
                    
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $contest->title }}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($contest->status === 'open') bg-green-100 text-green-800
                                @elseif($contest->status === 'voting') bg-blue-100 text-blue-800
                                @elseif($contest->status === 'closed') bg-gray-100 text-gray-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($contest->status) }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $contest->description }}</p>

                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div>
                                @if($contest->type === 'monthly')
                                    <span class="inline-flex items-center">
                                        📅 Monthly Challenge
                                    </span>
                                @elseif($contest->type === 'themed')
                                    <span class="inline-flex items-center">
                                        🎨 {{ $contest->theme }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center">
                                        🏆 Custom Contest
                                    </span>
                                @endif
                            </div>
                            <div>
                                {{ $contest->entries()->count() }} entries
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('contests.show', $contest) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details →
                            </a>
                            @if($contest->status === 'open' && auth()->check())
                                <a href="{{ route('contests.submit', $contest) }}" 
                                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md transition-colors">
                                    Submit Entry
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No contests available at the moment.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $contests->links() }}
        </div>
    </div>
</div>
@endsection


