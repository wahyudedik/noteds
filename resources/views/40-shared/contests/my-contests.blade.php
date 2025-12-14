@extends('40-shared/layouts/app')

@section('title', 'My Contests')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">My Contests</h2>
                <a href="{{ route('contests.create') }}"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Create Contest
                </a>
            </div>

            @if ($contests->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($contests as $contest)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            @if ($contest->banner_image)
                                <img src="{{ $contest->banner_image }}" alt="{{ $contest->title }}"
                                    class="w-full h-48 object-cover">
                            @else
                                <div
                                    class="w-full h-48 bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="p-6">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 flex-1">{{ $contest->title }}</h3>
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full
                                        @if ($contest->status === 'draft') bg-gray-100 text-gray-800
                                        @elseif($contest->status === 'open') bg-green-100 text-green-800
                                        @elseif($contest->status === 'voting') bg-blue-100 text-blue-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($contest->status) }}
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $contest->description }}</p>

                                <div class="space-y-2 mb-4 text-sm text-gray-600">
                                    @if ($contest->theme)
                                        <p><strong>Theme:</strong> {{ $contest->theme }}</p>
                                    @endif
                                    <p><strong>Type:</strong> {{ ucfirst($contest->type) }}</p>
                                    <p><strong>Entries:</strong> {{ $contest->entries()->count() }} /
                                        {{ $contest->max_entries_per_user * 50 }} (est.)</p>
                                </div>

                                <div class="flex gap-2 pt-4 border-t">
                                    <a href="{{ route('contests.show', $contest) }}"
                                        class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 text-center text-sm font-medium transition">
                                        View
                                    </a>
                                    @if ($contest->status === 'draft')
                                        <a href="{{ route('contests.edit', $contest) }}"
                                            class="flex-1 px-3 py-2 bg-yellow-50 text-yellow-600 rounded hover:bg-yellow-100 text-center text-sm font-medium transition">
                                            Edit
                                        </a>
                                        <form action="{{ route('contests.destroy', $contest) }}" method="POST"
                                            class="flex-1" onsubmit="return confirm('Delete this contest?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full px-3 py-2 bg-red-50 text-red-600 rounded hover:bg-red-100 text-sm font-medium transition">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $contests->links() }}
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-600 text-lg mb-4">You haven't created any contests yet.</p>
                    <a href="{{ route('contests.create') }}"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition inline-block">
                        Create Your First Contest
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

