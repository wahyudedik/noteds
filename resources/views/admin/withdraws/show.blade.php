@extends('layouts.app')

@section('title', 'Withdraw Detail - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.withdraws.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Withdraws</a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6">Withdraw Detail</h2>

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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Information</h3>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> {{ $withdraw->user->name }}</p>
                        <p><strong>Email:</strong> {{ $withdraw->user->email }}</p>
                        <p><strong>Current Balance:</strong> Rp {{ number_format($withdraw->user->wallet_balance ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Withdraw Details</h3>
                    <div class="space-y-2">
                        <p><strong>Amount:</strong> <span class="text-lg font-bold text-green-600">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</span></p>
                        <p><strong>Bank:</strong> {{ $withdraw->bank_name }}</p>
                        <p><strong>Account Number:</strong> {{ $withdraw->account_number }}</p>
                        <p><strong>Account Name:</strong> {{ $withdraw->account_name }}</p>
                        <p><strong>Status:</strong> 
                            @if($withdraw->status === 'approved')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Approved</span>
                            @elseif($withdraw->status === 'rejected')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Rejected</span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                            @endif
                        </p>
                        <p><strong>Requested:</strong> {{ $withdraw->created_at->format('d M Y, H:i') }}</p>
                        @if($withdraw->processed_at)
                            <p><strong>Processed:</strong> {{ $withdraw->processed_at->format('d M Y, H:i') }}</p>
                            <p><strong>Processed By:</strong> {{ $withdraw->processedBy->name }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($withdraw->admin_notes)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2">Admin Notes</h4>
                    <p class="text-gray-700">{{ $withdraw->admin_notes }}</p>
                </div>
            @endif

            @if($withdraw->status === 'pending')
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4">Process Withdraw</h4>
                    <form action="{{ route('admin.withdraws.update', $withdraw) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="mb-4">
                            <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Admin Notes (optional)
                            </label>
                            <textarea name="admin_notes" id="admin_notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                placeholder="Catatan untuk user..."></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" name="status" value="approved" 
                                class="approve-withdraw-btn bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                                Approve Withdraw
                            </button>
                            <button type="submit" name="status" value="rejected" 
                                class="reject-withdraw-btn bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                                Reject Withdraw
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

