@extends('layouts.app')

@section('title', __('messages.user_verification_pending_title'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.verification_pending_title') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('messages.verification_pending_description') }}</p>
            </div>
            <div class="flex gap-4 items-center">
                @if($pendingCount > 0)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                        {{ $pendingCount }} {{ __('messages.pending_count') }}
                    </span>
                @endif
                <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_users') }}</a>
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

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.users.pending-verification') }}" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_name_email') }}"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="role" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_roles') }}</option>
                    <option value="seller" {{ request('role') === 'seller' ? 'selected' : '' }}>{{ __('messages.seller') }}</option>
                    <option value="buyer" {{ request('role') === 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer') }}</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('messages.filter') }}
                </button>
                @if(request()->hasAny(['search', 'role']))
                    <a href="{{ route('admin.users.pending-verification') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.verification_status_label') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.identity_document_selfie') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.upload_date') }}</th>
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
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Admin</span>
                                        @elseif($user->role === 'seller')
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Seller</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Buyer</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                            {{ __('messages.pending') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-col gap-2">
                                            @if($user->ktp_path)
                                                <div>
                                                    <span class="text-xs text-gray-600">
                                                        {{ __('messages.document_type_label_short') }}: {{ $user->document_type === 'kartu_pelajar' ? __('messages.student_card_short') : __('messages.ktp_short') }}
                                                    </span>
                                                    <a href="{{ route('admin.users.download-doc', ['user' => $user->id, 'type' => 'ktp']) }}" 
                                                       class="block text-blue-600 hover:text-blue-800 text-xs mt-1">
                                                        {{ __('messages.download') }} {{ $user->document_type === 'kartu_pelajar' ? __('messages.student_card_short') : __('messages.ktp_short') }}
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">{{ __('messages.document_not_available') }}</span>
                                            @endif
                                            @if($user->selfie_path)
                                                <a href="{{ route('admin.users.download-doc', ['user' => $user->id, 'type' => 'selfie']) }}" 
                                                   class="text-blue-600 hover:text-blue-800 text-xs">{{ __('messages.download') }} {{ __('messages.selfie') }}</a>
                                            @else
                                                <span class="text-gray-400 text-xs">{{ __('messages.selfie_not_available') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @php
                                            $ktpDate = $user->ktp_path ? \Carbon\Carbon::parse($user->updated_at) : null;
                                            $selfieDate = $user->selfie_path ? \Carbon\Carbon::parse($user->updated_at) : null;
                                            $latestDate = $ktpDate && $selfieDate ? max($ktpDate, $selfieDate) : ($ktpDate ?? $selfieDate ?? $user->updated_at);
                                        @endphp
                                        {{ $latestDate ? (is_object($latestDate) ? $latestDate->format('d M Y H:i') : \Carbon\Carbon::parse($latestDate)->format('d M Y H:i')) : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.users.show', $user) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-xs">{{ __('messages.detail') }}</a>
                                            <form method="POST" action="{{ route('admin.users.verify.approve', $user) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="notes" value="{{ __('messages.verified_by_admin', [], app()->getLocale()) }}">
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-800 text-xs"
                                                        onclick="return confirm('{{ __('messages.approve_verification_confirm') }}');">{{ __('messages.approve') }}</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.users.verify.reject', $user) }}" 
                                                  class="inline"
                                                  onsubmit="return confirmReject(this);">
                                                @csrf
                                                <input type="text" name="reason" placeholder="{{ __('messages.reject_reason_placeholder') }}" 
                                                       class="rounded border-gray-300 text-xs w-32" required>
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">{{ __('messages.reject') }}</button>
                                            </form>
                                        </div>
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
                <p class="text-gray-600">{{ __('messages.no_users_need_verification') }}</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function confirmReject(form) {
    const reason = form.querySelector('input[name="reason"]').value;
    if (!reason || reason.trim() === '') {
        alert('{{ __('messages.please_fill_reject_reason') }}');
        return false;
    }
    return confirm('{{ __('messages.confirm_reject_verification') }}');
}
</script>
@endpush
@endsection

