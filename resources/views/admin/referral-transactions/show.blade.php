@extends('layouts.app')

@section('title', 'Referral Transaction Details')

@section('content')
    <div class="min-h-screen bg-gray-50 py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <a href="{{ route('admin.referral-transactions.index') }}"
                    class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center gap-1 mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Transactions
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Transaction Details</h1>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-8">
                    <!-- Status Badge -->
                    <div class="mb-6">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if ($referralTransaction->status === 'sent') bg-green-100 text-green-800
                        @elseif($referralTransaction->status === 'pending')
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-red-100 text-red-800 @endif">
                            {{ ucfirst($referralTransaction->status) }}
                        </span>
                    </div>

                    <!-- Amount Display -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600 font-medium mb-2">Commission Amount</p>
                        <p class="text-4xl font-bold text-gray-900">{{ currency($referralTransaction->amount) }}</p>
                    </div>

                    <!-- Details Grid -->
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Transaction ID -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Transaction ID</label>
                                <p class="text-gray-900 font-mono text-sm break-all">{{ $referralTransaction->id }}</p>
                            </div>

                            <!-- Type -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <p class="text-gray-900">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if ($referralTransaction->type === 'signup_bonus') bg-purple-100 text-purple-800
                                    @else
                                        bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $referralTransaction->type)) }}
                                    </span>
                                </p>
                            </div>

                            <!-- User -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Recipient User</label>
                                <div class="flex items-center gap-3">
                                    @if ($referralTransaction->user->avatar)
                                        <img src="{{ $referralTransaction->user->avatar }}"
                                            alt="{{ $referralTransaction->user->name }}" class="w-8 h-8 rounded-full">
                                    @else
                                        <div
                                            class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-sm">
                                            {{ substr($referralTransaction->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-gray-900 font-medium">{{ $referralTransaction->user->name }}</p>
                                        <p class="text-sm text-gray-600">{{ $referralTransaction->user->email }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Admin -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Processed By</label>
                                @if ($referralTransaction->admin)
                                    <div class="flex items-center gap-3">
                                        @if ($referralTransaction->admin->avatar)
                                            <img src="{{ $referralTransaction->admin->avatar }}"
                                                alt="{{ $referralTransaction->admin->name }}" class="w-8 h-8 rounded-full">
                                        @else
                                            <div
                                                class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-sm">
                                                {{ substr($referralTransaction->admin->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <p class="text-gray-900 font-medium">{{ $referralTransaction->admin->name }}
                                            </p>
                                            <p class="text-sm text-gray-600">Admin</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-gray-500">System</p>
                                @endif
                            </div>

                            <!-- Created At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                                <p class="text-gray-900">{{ $referralTransaction->created_at->format('Y-m-d H:i:s') }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $referralTransaction->created_at->diffForHumans() }}</p>
                            </div>

                            <!-- Sent At -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Sent At</label>
                                @if ($referralTransaction->sent_at)
                                    <p class="text-gray-900">{{ $referralTransaction->sent_at->format('Y-m-d H:i:s') }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $referralTransaction->sent_at->diffForHumans() }}</p>
                                @else
                                    <p class="text-gray-500">Not yet sent</p>
                                @endif
                            </div>
                        </div>

                        <!-- Notes -->
                        @if ($referralTransaction->notes)
                            <div class="pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ $referralTransaction->notes }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Related Referral -->
                        @if ($referralTransaction->referral)
                            <div class="pt-6 border-t border-gray-200">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Related Referral</label>
                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-gray-600">
                                        Referrer: <span
                                            class="font-medium text-gray-900">{{ $referralTransaction->referral->referrer->name ?? 'Unknown' }}</span>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Referred: <span
                                            class="font-medium text-gray-900">{{ $referralTransaction->referral->referred->name ?? 'Unknown' }}</span>
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
