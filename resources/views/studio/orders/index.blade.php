@extends('layouts.app')

@section('title', __('messages.my_orders') . ' — ' . __('messages.studio'))

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-900">{{ __('messages.my_orders') }}</h1>
            <a href="{{ route('studio.orders.create') }}" class="px-4 py-2 rounded-md bg-blue-600 text-white">{{ __('messages.create_order') }}</a>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-6">
            @if($orders->count() === 0)
                <p class="text-slate-600">{{ __('messages.no_orders') ?? 'Belum ada order.' }}</p>
            @else
                <div class="space-y-4">
                    @foreach($orders as $order)
                        <a href="{{ route('studio.orders.show', $order) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900">{{ $order->title }}</h2>
                                    <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ Str::limit($order->description, 140) }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs uppercase text-slate-500">{{ __('messages.order_status') }}</div>
                                    <div class="font-semibold">{{ ucfirst(str_replace('_',' ', $order->status)) }}</div>
                                    @if($order->budget > 0)
                                        <div class="text-xs text-slate-500 mt-1">{{ __('messages.order_budget') }}: {{ currency($order->budget) }}</div>
                                    @endif
                                </div>
                            </div>
                        </a>
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


