@extends('layouts.app')

@section('title', 'Contests Management')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Contests Management</h2>
            <div class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
                <a href="{{ route('admin.contests.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                    Create Contest
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entries</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($contests as $contest)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $contest->title }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($contest->description, 50) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 capitalize">
                                        {{ $contest->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($contest->status === 'open') bg-green-100 text-green-800
                                        @elseif($contest->status === 'voting') bg-blue-100 text-blue-800
                                        @elseif($contest->status === 'closed') bg-gray-100 text-gray-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($contest->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="{{ route('admin.contests.entries', $contest) }}" class="text-blue-600 hover:text-blue-900">
                                        {{ $contest->entries()->count() }} entries
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $contest->start_date ? $contest->start_date->format('M d') : 'TBD' }}</div>
                                    <div class="text-xs">{{ $contest->end_date ? $contest->end_date->format('M d') : 'TBD' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.contests.show', $contest) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="{{ route('admin.contests.edit', $contest) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                        <form action="{{ route('admin.contests.destroy', $contest) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No contests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $contests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

