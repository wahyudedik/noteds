@extends('40-shared/layouts/app')

@section('title', __('Refund Request Details'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('refunds.index') }}"
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
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Pending Review') }}
                </span>
            @elseif($refund->status === 'approved')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Approved') }}
                </span>
            @elseif($refund->status === 'rejected')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Rejected') }}
                </span>
            @elseif($refund->status === 'processed')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Processed') }}
                </span>
            @endif
        </div>

        <!-- Details Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Refund Information') }}</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-blue-900 mb-4">{{ __('Admin Response') }}</h2>
                <p class="text-sm text-blue-800 whitespace-pre-wrap">{{ $refund->admin_notes }}</p>
            </div>
        @endif
    </div>
</div>
@endsection


