@extends('layouts.app')

@section('title', __('messages.withdraw_detail_admin'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.withdraws.index') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_withdraws') }}</a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('messages.withdraw_detail') }}</h2>

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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.user_information') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.name') }}:</strong> {{ $withdraw->user->name }}</p>
                        <p><strong>{{ __('messages.email') }}:</strong> {{ $withdraw->user->email }}</p>
                        <p><strong>{{ __('messages.current_balance') }}:</strong> Rp {{ number_format($withdraw->user->wallet_balance ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.withdraw_detail') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.amount') }}:</strong> <span class="text-lg font-bold text-green-600">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</span></p>
                        <p><strong>{{ __('messages.bank') }}:</strong> {{ $withdraw->bank_name }}</p>
                        <p><strong>{{ __('messages.account_number') }}:</strong> {{ $withdraw->account_number }}</p>
                        <p><strong>{{ __('messages.account_name') }}:</strong> {{ $withdraw->account_name }}</p>
                        <p><strong>{{ __('messages.status') }}:</strong> 
                            @if($withdraw->status === 'approved')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.approved') }}</span>
                            @elseif($withdraw->status === 'rejected')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.rejected') }}</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                            @endif
                        </p>
                        <p><strong>{{ __('messages.requested') }}:</strong> {{ $withdraw->created_at->format('d M Y, H:i') }}</p>
                        @if($withdraw->status === 'pending')
                            @php
                                $hoursSinceRequest = $withdraw->created_at->diffInHours(now());
                                $canApprove = $hoursSinceRequest >= 24;
                            @endphp
                            <p><strong>{{ __('messages.time_elapsed') }}:</strong> 
                                @if($canApprove)
                                    <span class="text-green-600 font-semibold">{{ $hoursSinceRequest }} {{ __('messages.hours') }} ({{ __('messages.can_approve') }})</span>
                                @else
                                    <span class="text-yellow-600 font-semibold">{{ $hoursSinceRequest }} {{ __('messages.hours') }} / 24 {{ __('messages.hours') }} ({{ __('messages.minimum_wait_required') }})</span>
                                @endif
                            </p>
                        @endif
                        @if($withdraw->processed_at)
                            <p><strong>{{ __('messages.processed') }}:</strong> {{ $withdraw->processed_at->format('d M Y, H:i') }}</p>
                            <p><strong>{{ __('messages.processed_by') }}:</strong> {{ $withdraw->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($withdraw->admin_notes)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2">{{ __('messages.admin_notes') }}</h4>
                    <p class="text-gray-700">{{ $withdraw->admin_notes }}</p>
                </div>
            @endif

            @if($withdraw->status === 'pending')
                @php
                    $hoursSinceRequest = $withdraw->created_at->diffInHours(now());
                    $canApprove = $hoursSinceRequest >= 24;
                @endphp
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4">{{ __('messages.process_withdraw') }}</h4>
                    @if(!$canApprove)
                        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-yellow-800">
                                        {{ __('messages.minimum_24_hours_required') }}
                                    </p>
                                    <p class="text-xs text-yellow-700 mt-1">
                                        {{ __('messages.withdraw_approval_wait_time') }}: {{ 24 - $hoursSinceRequest }} {{ __('messages.hours_remaining') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <form action="{{ route('admin.withdraws.update', $withdraw) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-4">
                            <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.admin_notes_optional') }}
                            </label>
                            <textarea name="admin_notes" id="admin_notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                :placeholder="__('messages.notes_for_user')"></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" name="status" value="approved" 
                                class="approve-withdraw-btn bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded {{ !$canApprove ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !$canApprove ? 'disabled' : '' }}>
                                {{ __('messages.approve_withdraw') }}
                            </button>
                            <button type="submit" name="status" value="rejected" 
                                class="reject-withdraw-btn bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                                {{ __('messages.reject_withdraw') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle approve confirmation with SweetAlert2
    document.querySelectorAll('.approve-withdraw-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = 'approved';
            form.appendChild(statusInput);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '{{ __('messages.approve_withdraw_title', [], app()->getLocale()) ?: 'Approve Withdraw?' }}',
                    text: '{{ __('messages.approve_withdraw_text', [], app()->getLocale()) ?: 'Saldo will be deducted from user wallet.' }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '{{ __('messages.approve', [], app()->getLocale()) ?: 'Yes, Approve' }}',
                    cancelButtonText: '{{ __('messages.no_cancel') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Approve withdraw ini? Saldo akan dikurangi dari wallet user.')) {
                    form.submit();
                }
            }
        });
    });

    // Handle reject confirmation with SweetAlert2
    document.querySelectorAll('.reject-withdraw-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = 'rejected';
            form.appendChild(statusInput);
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '{{ __('messages.reject_withdraw_title', [], app()->getLocale()) ?: 'Reject Withdraw?' }}',
                    text: '{{ __('messages.reject_withdraw_text', [], app()->getLocale()) ?: 'Are you sure you want to reject this withdraw request?' }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '{{ __('messages.reject', [], app()->getLocale()) ?: 'Yes, Reject' }}',
                    cancelButtonText: '{{ __('messages.no_cancel') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            } else {
                if (confirm('Reject withdraw ini?')) {
                    form.submit();
                }
            }
        });
    });
});
</script>
@endpush
@endsection

