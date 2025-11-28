@extends('layouts.app')

@section('title', 'Badge Users')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.badges.index') }}" class="text-blue-600 hover:text-blue-800">
                    ← Back to Badges
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $badge->name }}</h2>
                            <p class="text-sm text-gray-600 mt-1">{{ $badge->description }}</p>
                        </div>
                        @if ($badge->icon)
                            <span class="text-4xl">{{ $badge->icon }}</span>
                        @endif
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Award Badge Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Award Badge to User</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.badges.award', $badge) }}" method="POST" class="flex gap-4">
                        @csrf
                        <div class="flex-1">
                            <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                User
                            </label>
                            <select name="user_id" id="user_id" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select user...</option>
                                @foreach (\App\Models\User::orderBy('name')->limit(200)->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Showing first 200 users. Type to search if user not found.
                            </p>
                        </div>
                        <div class="flex-1">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (optional)
                            </label>
                            <input type="text" name="notes" id="notes" placeholder="Reason for awarding..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                Award Badge
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Users with this Badge ({{ $users->total() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Earned At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->pivot->earned_at ? $user->pivot->earned_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $user->pivot->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No users have this badge
                                        yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
