@extends('layouts.app')

@section('title', __('messages.admin_user_detail'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">{{ __('messages.back_to_users') }}</a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.identity_verification_title') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <p>
                        <strong>{{ __('messages.status_label') }}</strong>
                        @if($user->verification_status === 'approved')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">{{ __('messages.approved') }}</span>
                        @elseif($user->verification_status === 'rejected')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">{{ __('messages.rejected') }}</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">{{ __('messages.pending') }}</span>
                        @endif
                    </p>
                    @if($user->agreement_accepted_at)
                        <p><strong>{{ __('messages.agreement_label') }}</strong> {{ __('messages.accepted') }} {{ $user->agreement_accepted_at->format('d M Y H:i') }} ({{ $user->agreement_version ?? 'v1' }})</p>
                    @endif
                    @if($user->verification_reviewed_at)
                        <p><strong>{{ __('messages.reviewed_label') }}</strong> {{ $user->verification_reviewed_at->format('d M Y H:i') }}</p>
                    @endif
                    @if($user->verification_notes)
                        <p class="text-sm"><strong>{{ __('messages.notes_label') }}</strong> {{ $user->verification_notes }}</p>
                    @endif
                </div>
                <div class="space-y-3">
                    <div class="flex gap-3">
                        <a href="{{ $user->ktp_path ? route('admin.users.download-doc', ['user' => $user->id, 'type' => 'ktp']) : '#' }}" class="px-3 py-2 text-xs rounded-md {{ $user->ktp_path ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-slate-50 text-slate-400 cursor-not-allowed' }}" @if(!$user->ktp_path) aria-disabled="true" @endif>{{ __('messages.download_ktp') }}</a>
                        <a href="{{ $user->selfie_path ? route('admin.users.download-doc', ['user' => $user->id, 'type' => 'selfie']) : '#' }}" class="px-3 py-2 text-xs rounded-md {{ $user->selfie_path ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-slate-50 text-slate-400 cursor-not-allowed' }}" @if(!$user->selfie_path) aria-disabled="true" @endif>{{ __('messages.download_selfie') }}</a>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.users.verify.approve', $user) }}">
                            @csrf
                            <input type="hidden" name="notes" value="">
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md" @if($user->verification_status==='approved') disabled @endif>{{ __('messages.approve') }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.users.verify.reject', $user) }}" onsubmit="return confirmRejectIdentity(this);">
                            @csrf
                            <input type="text" name="reason" placeholder="{{ __('messages.rejection_reason_required') }}" class="rounded-md border-gray-300 text-sm">
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md">{{ __('messages.reject') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.user_detail', ['name' => $user->name]) }}</h2>
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('messages.edit_user') }}
            </a>
        </div>

        @if(session('success'))
            @push('scripts')
            <script>
                (function () {
                    if (typeof window.NotedsToast === 'function') {
                        window.NotedsToast('success', @json(session('success')), { skipBackForward: true });
                    } else {
                        setTimeout(arguments.callee, 100);
                    }
                })();
            </script>
            @endpush
        @endif

        @if(session('error'))
            @push('scripts')
            <script>
                (function () {
                    if (typeof window.NotedsToast === 'function') {
                        window.NotedsToast('error', @json(session('error')), { skipBackForward: true });
                    } else {
                        setTimeout(arguments.callee, 100);
                    }
                })();
            </script>
            @endpush
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.user_information') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.name') }}:</strong> {{ $user->name }}</p>
                        <p><strong>{{ __('messages.email') }}:</strong> {{ $user->email }}</p>
                        <p><strong>{{ __('messages.role') }}:</strong>
                            @if($user->role === 'admin')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.admin') }}</span>
                            @elseif($user->role === 'seller')
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ __('messages.seller') }}</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ __('messages.buyer') }}</span>
                            @endif
                        </p>
                        <p><strong>{{ __('messages.wallet_balance_label') }}:</strong> {{ currency($user->wallet_balance ?? 0) }}</p>
                        <p><strong>{{ __('messages.joined') }}:</strong> {{ $user->created_at->format('d M Y, H:i') }}</p>
                        <p>
                            <strong>{{ __('messages.status') }}:</strong>
                            @if($user->suspended_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    {{ __('messages.user_status_suspended') }}
                                </span>
                                <span class="block text-xs text-gray-500 mt-1">
                                    {{ __('messages.suspended_at_label') }}: {{ $user->suspended_at->format('d M Y H:i') }}
                                </span>
                            @elseif(! $user->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                    {{ __('messages.user_status_inactive') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    {{ __('messages.user_status_active') }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.statistics') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.total_notes_label') }}:</strong> {{ $user->notes->count() }}</p>
                        <p><strong>{{ __('messages.public_notes_label') }}:</strong> {{ $user->notes->where('is_public', true)->count() }}</p>
                        <p><strong>{{ __('messages.total_withdraws') }}:</strong> {{ $user->withdraws->count() }}</p>
                        <p><strong>{{ __('messages.pending_withdraws') }}:</strong> {{ $user->withdraws->where('status', 'pending')->count() }}</p>
                        <p><strong>{{ __('messages.transactions_buyer') }}:</strong> {{ $user->transactionsAsBuyer->count() }}</p>
                        <p><strong>{{ __('messages.transactions_seller') }}:</strong> {{ $user->transactionsAsSeller->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.account_actions') }}</h3>

            @if($user->id === auth()->id())
                <p class="text-sm text-gray-600">{{ __('messages.cannot_modify_self_status') }}</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="border rounded-lg p-4 shadow-sm space-y-3" onsubmit="return confirmDeactivate(this);">
                        @csrf
                        <h4 class="text-sm font-semibold text-gray-800">{{ __('messages.deactivate_account') }}</h4>
                        <p class="text-xs text-gray-600">{{ __('messages.deactivate_account_help') }}</p>
                        <input type="text" name="reason" placeholder="{{ __('messages.optional_reason_placeholder') }}"
                            class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                        <button type="submit" class="mt-2 inline-flex items-center px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-semibold rounded-md">
                            {{ __('messages.deactivate') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ $user->suspended_at ? route('admin.users.release', $user) : route('admin.users.suspend', $user) }}" class="border rounded-lg p-4 shadow-sm space-y-3" onsubmit="return confirmSuspend(this, {{ $user->suspended_at ? 'true' : 'false' }});">
                        @csrf
                        @if($user->suspended_at)
                            <h4 class="text-sm font-semibold text-gray-800">{{ __('messages.release_suspend') }}</h4>
                            <p class="text-xs text-gray-600">{{ __('messages.release_suspend_help') }}</p>
                            <button type="submit" class="mt-2 inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md">
                                {{ __('messages.release_suspend') }}
                            </button>
                        @else
                            <h4 class="text-sm font-semibold text-gray-800">{{ __('messages.suspend_account') }}</h4>
                            <p class="text-xs text-gray-600">{{ __('messages.suspend_account_help') }}</p>
                            <input type="text" name="reason" placeholder="{{ __('messages.optional_reason_placeholder') }}"
                                class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                            <button type="submit" class="mt-2 inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md">
                                {{ __('messages.suspend') }}
                            </button>
                        @endif
                    </form>
                </div>

                @if(!$user->isAccessible())
                    <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="mt-4" onsubmit="return confirmActivate(this);">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md">
                            {{ __('messages.activate_account') }}
                        </button>
                    </form>
                @endif
            @endif
        </div>

        @if($user->withdraws->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.recent_withdraws') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->withdraws->take(5) as $withdraw)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $withdraw->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ currency($withdraw->amount) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($withdraw->status === 'approved')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.approved') }}</span>
                                        @elseif($withdraw->status === 'rejected')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.rejected') }}</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.admin_action_logs') }}</h3>

            @if($actionLogs->isEmpty())
                <p class="text-sm text-gray-600">{{ __('messages.no_admin_actions_found') }}</p>
            @else
                <div class="space-y-4">
                    @foreach($actionLogs as $log)
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ __('messages.admin_action_label') }}: {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ __('messages.performed_at') }}: {{ $log->created_at->format('d M Y H:i') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">
                                {{ __('messages.performed_by') }}: {{ $log->admin?->name ?? __('messages.admin') }}
                            </p>
                            @if($log->reason)
                                <p class="text-xs text-red-600 mt-1">
                                    {{ __('messages.admin_action_reason') }}: {{ $log->reason }}
                                </p>
                            @endif
                            @php
                                $previous = $log->metadata['previous_status'] ?? [];
                                $current = $log->metadata['current_status'] ?? [];
                            @endphp
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-600">
                                <div class="bg-white border rounded p-3">
                                    <p class="font-semibold text-gray-700 mb-1">{{ __('messages.previous_status') }}</p>
                                    <p>{{ __('messages.user_status_active') }}: {{ ($previous['is_active'] ?? false) ? __('messages.yes') : __('messages.no') }}</p>
                                    <p>{{ __('messages.user_status_suspended') }}: {{ ($previous['suspended_at'] ?? null) ? __('messages.yes') : __('messages.no') }}</p>
                                </div>
                                <div class="bg-white border rounded p-3">
                                    <p class="font-semibold text-gray-700 mb-1">{{ __('messages.current_status') }}</p>
                                    <p>{{ __('messages.user_status_active') }}: {{ ($current['is_active'] ?? false) ? __('messages.yes') : __('messages.no') }}</p>
                                    <p>{{ __('messages.user_status_suspended') }}: {{ ($current['suspended_at'] ?? null) ? __('messages.yes') : __('messages.no') }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function confirmDeactivate(form) {
        if (typeof Swal === 'undefined') {
            return confirm(@json(__('messages.confirm_deactivate_user')));
        }

        Swal.fire({
            icon: 'warning',
            title: @json(__('messages.deactivate_account')),
            text: @json(__('messages.confirm_deactivate_user')),
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6b7280',
            confirmButtonText: @json(__('messages.deactivate')),
            cancelButtonText: @json(__('messages.cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }

    function confirmSuspend(form, isRelease) {
        if (typeof Swal === 'undefined') {
            return confirm(isRelease ? @json(__('messages.confirm_release_user')) : @json(__('messages.confirm_suspend_user')));
        }

        const title = isRelease ? @json(__('messages.release_suspend')) : @json(__('messages.suspend_account'));
        const text = isRelease ? @json(__('messages.confirm_release_user')) : @json(__('messages.confirm_suspend_user'));
        const confirmText = isRelease ? @json(__('messages.release_suspend')) : @json(__('messages.suspend'));
        const confirmColor = isRelease ? '#2563eb' : '#dc2626';

        Swal.fire({
            icon: 'warning',
            title,
            text,
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmText,
            cancelButtonText: @json(__('messages.cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }

    function confirmActivate(form) {
        if (typeof Swal === 'undefined') {
            return confirm(@json(__('messages.confirm_activate_user')));
        }

        Swal.fire({
            icon: 'question',
            title: @json(__('messages.activate_account')),
            text: @json(__('messages.confirm_activate_user')),
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: @json(__('messages.activate_account')),
            cancelButtonText: @json(__('messages.cancel')),
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }
</script>
@endpush

