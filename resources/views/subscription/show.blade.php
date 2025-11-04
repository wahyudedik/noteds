@extends('layouts.app')

@section('title', __('messages.subscription_details'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.subscription_details') }}</h1>
                <p class="mt-2 text-base text-gray-600">{{ __('messages.view_subscription_information') }}</p>
            </div>
            <a href="{{ route('subscription.index') }}" class="text-gray-600 hover:text-gray-800">
                {{ __('messages.back_to_subscriptions') }}
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.subscription_information') }}</h2>
            </div>
            <div class="p-6">
                <!-- Plan -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.plan') }}</label>
                    @if($subscription->plan === 'premium')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                            {{ __('messages.premium_plan') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                            {{ __('messages.basic') }}
                        </span>
                    @endif
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.status') }}</label>
                    @if($subscription->status === 'active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            {{ __('messages.active') }}
                        </span>
                    @elseif($subscription->status === 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            {{ __('messages.pending_approval') }}
                        </span>
                    @elseif($subscription->status === 'expired')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">
                            {{ __('messages.expired') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                            {{ __('messages.cancelled') }}
                        </span>
                    @endif
                </div>

                <!-- Expiration -->
                @if($subscription->expired_at)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.expires_on') }}</label>
                        <p class="text-sm text-gray-900">{{ $subscription->expired_at->format('F d, Y') }}</p>
                    </div>
                @endif

                <!-- Payment Proof -->
                @if($subscription->payment_proof)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.payment_proof') }}</label>
                        <a href="{{ $subscription->payment_proof }}" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">
                            {{ __('messages.view_payment_proof') }}
                        </a>
                    </div>
                @endif

                <!-- Admin Notes -->
                @if($subscription->admin_notes)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.admin_notes') }}</label>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-gray-700">{{ $subscription->admin_notes }}</p>
                        </div>
                    </div>
                @endif

                <!-- Approved By -->
                @if($subscription->approvedBy)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.approved_by') }}</label>
                        <p class="text-sm text-gray-900">{{ $subscription->approvedBy->name }}</p>
                        @if($subscription->approved_at)
                            <p class="text-xs text-gray-500">{{ $subscription->approved_at->format('F d, Y H:i') }}</p>
                        @endif
                    </div>
                @endif

                <!-- Dates -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.submitted') }}</label>
                            <p class="text-sm text-gray-600">{{ $subscription->created_at->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.last_updated') }}</label>
                            <p class="text-sm text-gray-600">{{ $subscription->updated_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

