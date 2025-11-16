@extends('layouts.app')

@section('title', __('messages.vendor_list') . ' — ' . __('messages.admin'))

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">{{ __('messages.vendor_list') }}</h1>
                    <p class="text-sm text-slate-600">{{ __('messages.manage_vendors') }}</p>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline text-sm">{{ __('messages.back_to_dashboard') }}</a>
            </div>

            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 p-4 rounded border border-yellow-200 bg-yellow-50">
                <div class="text-sm font-medium text-yellow-800">{{ __('messages.quick_assign_order') }}</div>
                <form method="POST" action="{{ route('admin.vendors.assign') }}" class="mt-3 flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                    @csrf
                    <input type="text" name="order_id" placeholder="{{ __('messages.order_id') ?? 'Order ID (UUID)' }}" class="w-full sm:w-72 rounded-lg border-gray-300" required>
                    <select name="vendor_id" class="w-full sm:w-72 rounded-lg border-gray-300" required>
                        <option value="">{{ __('messages.select_vendor') }}</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->email }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-md bg-yellow-600 text-white text-sm">{{ __('messages.assign') }}</button>
                </form>
                <p class="mt-2 text-xs text-yellow-700">{{ __('messages.enter_order_id') }}</p>
            </div>

            <div class="mb-6 p-4 rounded border border-blue-200 bg-blue-50">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-800">{{ __('messages.bulk_assign_orders') }}</div>
                        <div class="text-xs text-blue-700">{{ __('messages.bulk_assign_description') }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.vendors.bulk-assign') }}" class="mt-3 space-y-3">
                    @csrf
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2"><input type="checkbox" onclick="document.querySelectorAll('input[name^=order_ids]').forEach(cb=>cb.checked=this.checked)"></th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('messages.title') }}</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('messages.buyer') }}</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('messages.order_status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($unassignedOrders as $o)
                                    <tr>
                                        <td class="px-4 py-2"><input type="checkbox" name="order_ids[]" value="{{ $o->id }}"></td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium text-slate-900"><a href="{{ route('studio.orders.show', $o) }}" class="text-blue-600 hover:underline">{{ $o->title }}</a></div>
                                        </td>
                                        <td class="px-4 py-2 text-slate-700">{{ $o->user?->name }}</td>
                                        <td class="px-4 py-2 text-slate-700">{{ ucfirst(str_replace('_',' ',$o->status)) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">{{ __('messages.no_unassigned_orders') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                        <select name="vendor_id" class="w-full sm:w-72 rounded-lg border-gray-300" required>
                            <option value="">{{ __('messages.select_vendor') }}</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }} ({{ $vendor->email }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm">{{ __('messages.bulk_assign') ?? 'Bulk Assign' }}</button>
                        <div class="ml-auto">{{ $unassignedOrders->links() }}</div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('messages.name') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('messages.email') }}</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700">{{ __('messages.assigned_orders') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($vendors as $vendor)
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900">{{ $vendor->name }}</td>
                                <td class="px-4 py-2 text-slate-700">{{ $vendor->email }}</td>
                                <td class="px-4 py-2 text-slate-700">
                                    @php
                                        $count = \App\Models\ServiceOrder::where('assigned_user_id', $vendor->id)->count();
                                    @endphp
                                    {{ $count }} {{ __('messages.order') ?? 'order' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $vendors->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


