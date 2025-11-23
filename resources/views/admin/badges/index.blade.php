@extends('layouts.app')

@section('title', 'Badges Management')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Badges Management</h2>
            <div class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
                <a href="{{ route('admin.badges.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                    Create Custom Badge
                </a>
            </div>
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

        @foreach($badges as $category => $categoryBadges)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 capitalize">{{ $category }} Badges</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Badge</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criteria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($categoryBadges as $badge)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($badge->icon)
                                                <span class="text-2xl mr-2">{{ $badge->icon }}</span>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $badge->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $badge->description }}</div>
                                                @if($badge->is_custom)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">
                                                        Custom
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($badge->criteria_type && $badge->criteria_value)
                                            {{ ucfirst(str_replace('_', ' ', $badge->criteria_type)) }}: {{ $badge->criteria_value }}
                                        @elseif($badge->is_custom && $badge->custom_criteria)
                                            Custom criteria
                                        @else
                                            Manual
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ route('admin.badges.users', $badge) }}" class="text-blue-600 hover:text-blue-900">
                                            {{ $badge->users()->count() }} users
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($badge->is_active)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex gap-2">
                                            <a href="{{ route('admin.badges.edit', $badge) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                            @if($badge->is_custom)
                                                <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

