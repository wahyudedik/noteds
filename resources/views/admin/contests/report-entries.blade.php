@extends('layouts.app')

@section('title', 'Contest Entries - ' . $contest->title)

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.contests.report') }}" class="text-blue-600 hover:underline">← Back to Contests</a>
                <h2 class="text-2xl font-bold text-gray-900 mt-2">{{ $contest->title }}</h2>
                <p class="text-gray-600">Viewing all entries for this contest</p>
            </div>

            <!-- Entry Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Entries</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $entries->total() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Review</div>
                    <div class="text-2xl font-bold text-yellow-600">
                        {{ $entries->pluck('status')->filter(fn($s) => $s === 'pending')->count() }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $entries->pluck('status')->filter(fn($s) => $s === 'approved')->count() }}
                    </div>
                </div>
            </div>

            <!-- Entries Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($entries->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">User
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Note
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">Votes
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">
                                            Submitted</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($entries as $entry)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $entry->user->name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ '@' . ($entry->user->username ?? 'N/A') }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $entry->note->title }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ Str::limit($entry->note->description, 50) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span
                                                    class="text-sm font-semibold text-blue-600">{{ $entry->vote_count ?? 0 }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full
                                                    @if ($entry->status === 'pending') bg-yellow-100 text-yellow-800
                                                    @elseif($entry->status === 'approved') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst($entry->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm text-gray-600">{{ $entry->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('admin.notes.show', $entry->note) }}"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    View Note
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $entries->links() }}
                        </div>
                    @else
                        <p class="text-gray-600 text-center py-8">No entries found for this contest</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
