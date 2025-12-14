@extends('40-shared/layouts/app')

@section('title', __('Pending User Verification'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Pending User Verification') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Review KTP and selfie uploads that need approval.') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <p class="text-sm text-gray-600">{{ __('Pending users') }}</p>
                    <p class="text-4xl font-bold text-gray-900 mt-2">{{ $pendingCount ?? 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ __('KTP & selfie uploaded') }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <form method="GET" action="{{ route('admin.users.pending-verification') }}"
                    class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    @php
                        $searchFilter = is_string(request('search')) ? request('search') : '';
                        $roleFilter = is_string(request('role')) ? request('role') : '';
                    @endphp
                    <div class="lg:col-span-2">
                        <label for="search"
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('Search name or email') }}</label>
                        <input type="text" id="search" name="search" value="{{ $searchFilter }}"
                            placeholder="{{ __('Search...') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="role"
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('Role') }}</label>
                        <select id="role" name="role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">{{ __('All') }}</option>
                            <option value="seller" @selected($roleFilter === 'seller')>{{ __('Seller') }}</option>
                            <option value="buyer" @selected($roleFilter === 'buyer')>{{ __('Buyer') }}</option>
                            <option value="admin" @selected($roleFilter === 'admin')>{{ __('Admin') }}</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">{{ __('Apply') }}</button>
                        @if (request()->has('search') || request()->has('role'))
                            <a href="{{ route('admin.users.pending-verification') }}"
                                class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">{{ __('Reset') }}</a>
                        @endif
                    </div>
                </form>
            </div>

            @if (($users ?? collect())->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('User') }}</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Email') }}</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Document Type') }}
                                    </th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Status') }}</th>
                                    <th class="text-center py-3 px-6 font-semibold text-gray-900">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    @php
                                        $avatarUrl = is_string($user->profile_photo_url ?? null)
                                            ? $user->profile_photo_url
                                            : null;
                                        $displayName = is_string($user->name ?? null) ? $user->name : '';
                                        $displayUsername = is_string($user->username ?? null) ? $user->username : '';
                                        $displayEmail = is_string($user->email ?? null) ? $user->email : '';
                                        $documentType = is_string($user->document_type ?? null)
                                            ? $user->document_type
                                            : 'ktp';
                                    @endphp
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                @if ($avatarUrl)
                                                    <img src="{{ $avatarUrl }}" alt="{{ $displayName }}"
                                                        class="w-10 h-10 rounded-full object-cover">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                                                @endif
                                                <div>
                                                    <p class="font-semibold text-gray-900">{{ $displayName }}</p>
                                                    <p class="text-sm text-gray-500">{{ '@' . $displayUsername }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <p class="text-gray-900">{{ $displayEmail }}</p>
                                            <p class="text-sm text-gray-500">{{ $user->created_at?->format('M d, Y') }}</p>
                                        </td>
                                        <td class="py-4 px-6 capitalize text-gray-900">{{ $documentType }}</td>
                                        <td class="py-4 px-6">
                                            @php $status = $user->verification_status ?? 'pending'; @endphp
                                            <span
                                                class="px-3 py-1 rounded-full text-sm font-medium
                                                @if ($status === 'approved') bg-green-100 text-green-800
                                                @elseif($status === 'rejected') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <div class="flex items-center justify-center gap-2 flex-wrap">
                                                <a href="{{ route('admin.users.download-doc', [$user, 'ktp']) }}"
                                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200"
                                                    target="_blank">{{ __('Download KTP') }}</a>
                                                <a href="{{ route('admin.users.download-doc', [$user, 'selfie']) }}"
                                                    class="px-3 py-2 text-sm bg-gray-100 text-gray-800 rounded hover:bg-gray-200"
                                                    target="_blank">{{ __('Download Selfie') }}</a>
                                                <form method="POST"
                                                    action="{{ route('admin.users.verify.approve', $user) }}"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-3 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                                                        onclick="return confirm('{{ __('Approve this user?') }}')">{{ __('Approve') }}</button>
                                                </form>
                                                <form method="POST"
                                                    action="{{ route('admin.users.verify.reject', $user) }}"
                                                    class="inline-flex items-center gap-2">
                                                    @csrf
                                                    <input type="text" name="reason" required maxlength="500"
                                                        placeholder="{{ __('Reason') }}"
                                                        class="px-2 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-red-500 focus:border-transparent text-sm">
                                                    <button type="submit"
                                                        class="px-3 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                                                        onclick="return confirm('{{ __('Reject this user?') }}')">{{ __('Reject') }}</button>
                                                </form>
                                                <a href="{{ route('admin.users.show', $user) }}"
                                                    class="px-3 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('Details') }}</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if (
                    $users instanceof \Illuminate\Contracts\Pagination\Paginator ||
                        $users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="mt-8">
                        {{ $users->links() }}
                    </div>
                @endif
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No pending verifications') }}</h3>
                    <p class="text-gray-600">{{ __('All users are processed or none uploaded yet.') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
