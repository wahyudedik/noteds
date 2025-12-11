@extends('layouts.app')

@section('title', __('messages.pending_approvals') . ' — ' . __('messages.studio'))

@section('content')
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-slate-900">{{ __('messages.pending_approvals') }}</h1>
                <a href="{{ route('studio.orders.index') }}" class="text-sm text-blue-600 hover:text-blue-700">← Back to My
                    Orders</a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                @if ($orders->count() === 0)
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_pending_approvals') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('messages.no_pending_approvals_description') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($orders as $order)
                            <div class="border border-amber-200 bg-amber-50 rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1">
                                        <h2 class="text-lg font-semibold text-slate-900">{{ $order->title }}</h2>
                                        <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                                            {{ Str::limit($order->description, 140) }}</p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <div
                                            class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                            ⏳ Awaiting Your Approval
                                        </div>
                                    </div>
                                </div>

                                <!-- Work Submission Details -->
                                @php
                                    $latestSubmission = $order->workSubmissions()->latest('submitted_at')->first();
                                @endphp

                                @if ($latestSubmission)
                                    <div class="border-t border-amber-200 pt-3 mt-3">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                                            <div>
                                                <span class="text-xs text-gray-500 uppercase">Vendor</span>
                                                <p class="font-medium text-sm">
                                                    {{ $order->assignedVendor?->name ?? 'Assigned' }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500 uppercase">Submitted</span>
                                                <p class="font-medium text-sm">
                                                    {{ $latestSubmission->submitted_at->format('M d, Y') }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500 uppercase">Budget</span>
                                                <p class="font-medium text-sm">{{ currency($order->budget) }}</p>
                                            </div>
                                            <div>
                                                <span class="text-xs text-gray-500 uppercase">Status</span>
                                                <p class="font-medium text-sm text-amber-700">Review Needed</p>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex gap-2 pt-3 border-t border-amber-200">
                                            <a href="{{ route('studio.orders.show', $order) }}"
                                                class="flex-1 text-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
                                                Review Work & Approve
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
