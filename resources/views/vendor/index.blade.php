@extends('layouts.app')

@section('title', __('messages.vendor_dashboard'))

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('messages.vendor_dashboard') }}</h1>
            <p class="mt-2 text-slate-600">{{ __('messages.vendor_dashboard_description') ?? 'Lihat order yang ditugaskan dan quotes yang Anda kirim.' }}</p>
        </div>

        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('messages.assigned_orders') }}</h2>
            @if($assignedOrders->count() === 0)
                <p class="text-slate-600 text-sm">{{ __('messages.no_assigned_orders') ?? 'Belum ada order ditugaskan.' }}</p>
            @else
                <div class="space-y-3">
                    @foreach($assignedOrders as $order)
                        <a href="{{ route('studio.orders.show', $order) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-slate-600">{{ __('messages.service_orders') }}</div>
                                    <div class="font-semibold">{{ $order->title }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-500">{{ __('messages.order_status') }}</div>
                                    <div class="font-semibold">{{ ucfirst(str_replace('_',' ', $order->status)) }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $assignedOrders->links() }}
                </div>
            @endif
        </div>

        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('messages.my_quotes') }}</h2>
            @if($myQuotes->count() === 0)
                <p class="text-slate-600 text-sm">{{ __('messages.no_quotes') ?? 'Belum ada quotes dikirim.' }}</p>
            @else
                <div class="space-y-3">
                    @foreach($myQuotes as $quote)
                        <a href="{{ route('studio.orders.show', $quote->order) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-slate-600">{{ __('messages.service_orders') }}</div>
                                    <div class="font-semibold">{{ $quote->order?->title ?? 'N/A' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-500">{{ __('messages.quote_total_amount') }}</div>
                                    <div class="font-semibold">{{ currency($quote->total_amount) }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ __('messages.order_status') }}: {{ ucfirst($quote->status) }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $myQuotes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


