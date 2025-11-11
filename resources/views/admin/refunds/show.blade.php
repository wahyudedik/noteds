@extends('layouts.app')

@section('title', __('Admin - Refund Details'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.refunds.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Refunds') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Refund Request Details') }}</h1>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            @if($refund->status === 'pending')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    {{ __('Pending Review') }}
                </span>
            @elseif($refund->status === 'approved')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ __('Approved') }}
                </span>
            @elseif($refund->status === 'rejected')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    {{ __('Rejected') }}
                </span>
            @elseif($refund->status === 'processed')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    {{ __('Processed') }}
                </span>
            @endif
        </div>

        <!-- Refund Details -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Refund Information') }}</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Buyer') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.users.show', $refund->buyer) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $refund->buyer->name }}
                        </a>
                        <div class="text-xs text-gray-500">{{ $refund->buyer->email }}</div>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Seller') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('admin.users.show', $refund->seller) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $refund->seller->name }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Note') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('marketplace.show', $refund->note) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $refund->note->title }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Amount') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ currency($refund->amount) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Reason') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $refund->reason)) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Requested Date') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $refund->created_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($refund->processed_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Processed Date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $refund->processed_at->format('M d, Y H:i') }}</dd>
                    </div>
                @endif
                @if($refund->processedBy)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Processed By') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $refund->processedBy->name }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Reason Description -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Reason Description') }}</h2>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $refund->reason_description }}</p>
        </div>

        <!-- Admin Notes -->
        @if($refund->admin_notes)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-blue-900 mb-4">{{ __('Admin Notes') }}</h2>
                <p class="text-sm text-blue-800 whitespace-pre-wrap">{{ $refund->admin_notes }}</p>
            </div>
        @endif

        <!-- Actions (if pending) -->
        @if($refund->status === 'pending')
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Actions') }}</h2>
                
                <!-- Approve Form -->
                <form action="{{ route('admin.refunds.approve', $refund) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="mb-4">
                        <label for="admin_notes_approve" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Admin Notes') }} ({{ __('Optional') }})
                        </label>
                        <textarea name="admin_notes" id="admin_notes_approve" rows="3"
                            placeholder="{{ __('Add notes about this approval...') }}"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                        {{ __('Approve Refund') }}
                    </button>
                </form>

                <!-- Reject Form -->
                <form action="{{ route('admin.refunds.reject', $refund) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="admin_notes_reject" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Rejection Reason') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea name="admin_notes" id="admin_notes_reject" rows="3" required
                            placeholder="{{ __('Please explain why this refund is being rejected...') }}"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ __('This message will be sent to the buyer.') }}</p>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700">
                        {{ __('Reject Refund') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection

