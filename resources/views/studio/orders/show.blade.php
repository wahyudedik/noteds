@extends('layouts.app')

@section('title', __('messages.order_detail') . ' — ' . __('messages.studio'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $order->title }}</h1>
                    <div class="mt-1 text-sm text-slate-600">{{ __('messages.order_status') }}: <span class="font-semibold">{{ ucfirst(str_replace('_',' ', $order->status)) }}</span></div>
                </div>
                <div class="text-right">
                    @if($order->budget > 0)
                        <div class="text-sm text-slate-600">{{ __('messages.order_budget') }}</div>
                        <div class="text-lg font-semibold">{{ currency($order->budget) }}</div>
                    @endif
                </div>
            </div>
            <div class="mt-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-2">{{ __('messages.order_description') }}</h2>
                <div class="prose max-w-none">
                    <p class="text-slate-700 whitespace-pre-wrap">{{ $order->description }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('messages.milestones') }}</h2>
            @if(empty($order->milestones))
                <p class="text-slate-600 text-sm">{{ __('messages.no_milestones') }}</p>
            @else
                <ol class="list-decimal pl-5 space-y-2 text-slate-700">
                    @foreach($order->milestones as $m)
                        <li>
                            <div class="font-semibold">{{ $m['title'] ?? __('messages.milestone') }}</div>
                            <div class="text-sm">{{ $m['description'] ?? '' }}</div>
                            @if(isset($m['amount']))<div class="text-xs text-slate-500">{{ __('messages.milestone_amount') }}: {{ currency($m['amount']) }}</div>@endif
                            @if(isset($m['status']))<div class="text-xs text-slate-500">{{ __('messages.order_status') }}: {{ ucfirst($m['status']) }}</div>@endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('messages.escrow_amount') }}</h2>
            <p class="text-sm text-slate-600">{{ __('messages.current_escrow') }}: <strong>{{ currency($order->escrow_amount) }}</strong></p>
            <p class="text-xs text-slate-500 mt-1">{{ __('messages.escrow_note') }}</p>
            <div class="mt-3">
                <details class="text-sm">
                    <summary class="cursor-pointer text-slate-700">{{ __('messages.escrow_history') }}</summary>
                    @php($ledger = \App\Models\EscrowLedger::where('service_order_id', $order->id)->latest()->get())
                    @if($ledger->isEmpty())
                        <p class="text-slate-500 mt-2">{{ __('messages.no_escrow_history') }}</p>
                    @else
                        <div class="mt-2 space-y-1">
                            @foreach($ledger as $row)
                                <div class="flex items-center justify-between p-2 rounded border">
                                    <div>
                                        <span class="font-medium">{{ ucfirst($row->type) }}</span>
                                        @if(!is_null($row->milestone_index))
                                            <span class="text-xs text-slate-500"> ({{ __('messages.milestone') }} #{{ $row->milestone_index + 1 }})</span>
                                        @endif
                                        <div class="text-xs text-slate-500">{{ $row->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="font-semibold">{{ currency($row->amount) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </details>
            </div>

            <div class="mt-4">
                <details class="text-sm">
                    <summary class="cursor-pointer text-slate-700">{{ __('messages.activity_timeline') }}</summary>
                    @php($activities = \App\Models\OrderActivity::where('service_order_id', $order->id)->latest()->get())
                    @if($activities->isEmpty())
                        <p class="text-slate-500 mt-2">{{ __('messages.no_activities') }}</p>
                    @else
                        <div class="mt-2 space-y-2">
                            @foreach($activities as $act)
                                <div class="p-2 rounded border">
                                    <div class="flex items-center justify-between">
                                        <div class="font-medium">{{ str_replace('_', ' ', ucfirst($act->action)) }}</div>
                                        <div class="text-xs text-slate-500">{{ $act->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                    @if($act->description)
                                        <div class="text-xs text-slate-600 mt-1">{{ $act->description }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </details>
            </div>
            @auth
                @if(isset($vendors) && count($vendors) > 0 && empty($order->assigned_user_id))
                    <div class="mt-4 p-4 border border-yellow-200 bg-yellow-50 rounded-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-yellow-800">{{ __('messages.assign_vendor') }} ({{ __('messages.admin') }})</p>
                                <p class="text-xs text-yellow-700">{{ __('messages.assign_vendor_description') }}</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('studio.orders.assign-vendor', $order) }}" class="mt-3 flex gap-2 items-center">
                            @csrf
                            <select name="vendor_id" class="rounded-lg border-gray-300">
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-2 rounded-md bg-yellow-600 text-white text-sm">{{ __('messages.assign') }}</button>
                        </form>
                    </div>
                @endif
                @if (auth()->id() === $order->user_id)
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <form method="POST" action="{{ route('studio.orders.fund-escrow', $order) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="1" class="w-full rounded-lg border-gray-300" placeholder="{{ __('messages.amount') }}">
                        <button type="submit" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">{{ __('messages.fund_escrow') }}</button>
                    </form>
                    <form method="POST" action="{{ route('studio.orders.release-escrow', $order) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="1" class="w-full rounded-lg border-gray-300" placeholder="{{ __('messages.amount') }}">
                        @if(!empty($order->milestones))
                            <select name="milestone_index" class="rounded-lg border-gray-300">
                                <option value="">— {{ __('messages.milestone') }} —</option>
                                @foreach(($order->milestones ?? []) as $i => $m)
                                    <option value="{{ $i }}">#{{ $i + 1 }} - {{ $m['title'] ?? __('messages.milestone') }} ({{ isset($m['amount']) ? currency($m['amount']) : '—' }})</option>
                                @endforeach
                            </select>
                        @endif
                        <button type="submit" class="px-3 py-2 rounded-md bg-green-600 text-white text-sm">{{ __('messages.release_escrow') }}</button>
                    </form>
                    <form method="POST" action="{{ route('studio.orders.refund-escrow', $order) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="1" class="w-full rounded-lg border-gray-300" placeholder="{{ __('messages.amount') }}">
                        <button type="submit" class="px-3 py-2 rounded-md bg-red-600 text-white text-sm">{{ __('messages.refund_escrow') }}</button>
                    </form>
                </div>
                @endif
            @endauth
        </div>

        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ __('messages.quotes') }}</h2>
                @if(auth()->user()?->hasRole('admin'))
                    <a href="{{ route('studio.orders.quotes.create', $order) }}" class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm">{{ __('messages.create_quote') }}</a>
                @endif
            </div>
            @php($quotes = \App\Models\ServiceQuote::where('service_order_id', $order->id)->latest()->get())
            @if($quotes->isEmpty())
                <p class="text-slate-600 text-sm">{{ __('messages.no_quotes_found') }}</p>
            @else
                <div class="space-y-3">
                    @foreach($quotes as $quote)
                        <div class="p-4 rounded-lg border border-slate-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-slate-600">{{ __('messages.vendor') }}</div>
                                    <div class="font-semibold">{{ $quote->vendor?->name ?? 'N/A' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-slate-600">{{ __('messages.quote_total_amount') }}</div>
                                    <div class="font-semibold">{{ currency($quote->total_amount) }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ __('messages.order_status') }}: {{ ucfirst($quote->status) }}</div>
                                </div>
                            </div>
                            @if(!empty($quote->milestones))
                                <div class="mt-3">
                                    <div class="text-sm font-semibold text-slate-900 mb-1">{{ __('messages.milestones') }}</div>
                                    <ol class="list-decimal pl-5 space-y-1 text-slate-700">
                                        @foreach($quote->milestones as $m)
                                            <li>
                                                <span class="font-medium">{{ $m['title'] ?? __('messages.milestone') }}</span>
                                                @if(isset($m['amount']))<span class="text-xs text-slate-500"> — {{ currency($m['amount']) }}</span>@endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            @endif
                            @auth
                                @if(auth()->id() === $order->user_id && $quote->status === 'pending')
                                    <div class="mt-3 flex items-center gap-2">
                                        <form method="POST" action="{{ route('studio.quotes.accept', $quote) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-md bg-green-600 text-white text-sm">{{ __('messages.accept_quote') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('studio.quotes.reject', $quote) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-md bg-red-600 text-white text-sm">{{ __('messages.reject_quote') }}</button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


