@extends('layouts.app')

@section('title', 'Contest Report')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Contest Report</h2>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Contests</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_contests'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Active Contests</div>
                    <div class="text-2xl font-bold text-green-600">{{ $stats['active_contests'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Entries</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_entries'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Entries</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_entries'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved Entries</div>
                    <div class="text-2xl font-bold text-purple-600">{{ $stats['approved_entries'] }}</div>
                </div>
            </div>

            <!-- Contests Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">All Contests</h3>

                    @if ($contests->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Title
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Creator
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status
                                        </th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-700 uppercase">
                                            Entries</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Created
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($contests as $contest)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $contest->title }}</div>
                                                <div class="text-xs text-gray-500">{{ $contest->theme ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ $contest->creator->name ?? 'Unknown' }}</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ '@' . ($contest->creator->username ?? 'N/A') }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-600">{{ ucfirst($contest->type) }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-3 py-1 text-xs font-semibold rounded-full
                                                    @if ($contest->status === 'draft') bg-gray-100 text-gray-800
                                                    @elseif($contest->status === 'open') bg-green-100 text-green-800
                                                    @elseif($contest->status === 'voting') bg-blue-100 text-blue-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst($contest->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span
                                                    class="text-sm font-medium text-gray-900">{{ $contest->entries()->count() }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm text-gray-600">{{ $contest->created_at->format('M d, Y') }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('admin.contests.report.entries', $contest) }}"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    View Entries
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $contests->links() }}
                        </div>
                    @else
                        <p class="text-gray-600 text-center py-8">No contests found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
