@extends('layouts.app')

@section('title', __('messages.create_quote') . ' — ' . __('messages.studio'))

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('messages.create_quote') }}</h1>
            <form action="{{ route('studio.orders.quotes.store', $order) }}" method="POST" class="space-y-6">
                @csrf
                @php $user = auth()->user(); @endphp
                @if($user->hasRole('admin'))
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.vendor') }}</label>
                        <input type="text" list="vendors" name="vendor_id" class="w-full rounded-lg border-gray-300" placeholder="{{ __('messages.enter_vendor_id') }}" required>
                        <datalist id="vendors">
                            @foreach(\App\Models\User::role('vendor')->limit(50)->get() as $v)
                                <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->email }})</option>
                            @endforeach
                        </datalist>
                        @error('vendor_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                @else
                    <div class="p-3 rounded-md bg-gray-50 border text-sm text-gray-700">
                        {{ __('messages.quote_as_vendor') }}: <strong>{{ $user->name }}</strong>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.quote_total_amount') }}</label>
                    <input type="number" step="0.01" min="1" name="total_amount" value="{{ old('total_amount', 0) }}" class="w-full rounded-lg border-gray-300" required>
                    @error('total_amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div x-data="{ rows: 1 }">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.milestones') }} ({{ __('messages.optional') }})</label>
                        <button type="button" class="text-sm text-blue-600" @click="rows++">+ {{ __('messages.add_milestone') }}</button>
                    </div>
                    <template x-for="i in rows" :key="i">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                            <input type="text" name="milestones[][title]" placeholder="{{ __('messages.milestone_title') }}" class="rounded-lg border-gray-300 md:col-span-1">
                            <input type="number" name="milestones[][amount]" step="0.01" min="0" placeholder="{{ __('messages.amount') }}" class="rounded-lg border-gray-300">
                            <input type="text" name="milestones[][description]" placeholder="{{ __('messages.order_description') }}" class="rounded-lg border-gray-300 md:col-span-1">
                        </div>
                    </template>
                    @error('milestones.*.title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                    @error('milestones.*.amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('studio.orders.show', $order) }}" class="px-4 py-2 rounded-md border">{{ __('messages.cancel') }}</a>
                    <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white">{{ __('messages.send') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


