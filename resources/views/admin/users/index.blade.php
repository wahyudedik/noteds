@extends('layouts.app')

@section('title', __('messages.admin_users'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.users_management') }}</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_dashboard') }}</a>
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

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" :placeholder="__('messages.search_name_or_email')"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="role" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_roles') }}</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                    <option value="seller" {{ request('role') === 'seller' ? 'selected' : '' }}>{{ __('messages.seller') }}</option>
                    <option value="buyer" {{ request('role') === 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer') }}</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('messages.filter') }}
                </button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('messages.clear') }}
                    </a>
                @endif
            </form>
        </div>

        @if($users->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.email') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.role') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.balance') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.joined') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->role === 'admin')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.admin') }}</span>
                                        @elseif($user->role === 'seller')
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ __('messages.seller') }}</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ __('messages.buyer') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ currency($user->wallet_balance ?? 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-800 mr-3">{{ __('messages.view') }}</a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-green-600 hover:text-green-800">{{ __('messages.edit') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600">{{ __('messages.no_users_found') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

