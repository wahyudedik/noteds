@extends('layouts.app')

@section('title', __('affiliate.admin_title') . ' - ' . __('messages.withdraw_detail'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.affiliate.payouts') }}" class="text-blue-600 hover:text-blue-800">
                ← {{ __('affiliate.recent_payouts') }}
            </a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('messages.withdraw_detail') }}</h2>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.user_information') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.name') }}:</strong> {{ $payout->affiliate->name }}</p>
                        <p><strong>{{ __('messages.email') }}:</strong> {{ $payout->affiliate->email }}</p>
                        <p><strong>{{ __('messages.current_balance') }}:</strong> {{ currency($payout->affiliate->wallet_balance ?? 0) }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.withdraw_detail') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('affiliate.amount') }}:</strong> 
                            <span class="text-lg font-bold text-green-600">{{ currency($payout->amount) }}</span>
                        </p>
                        <p><strong>{{ __('affiliate.method') }}:</strong> {{ __('affiliate.payout_methods.' . $payout->payout_method) }}</p>
                        <p><strong>{{ __('affiliate.commissions') }}:</strong> {{ $payout->commission_count }}</p>
                        <p><strong>{{ __('affiliate.status') }}:</strong> 
                            <span class="px-2 py-1 rounded text-xs font-medium 
                                {{ $payout->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                   ($payout->status === 'failed' ? 'bg-red-100 text-red-800' : 
                                   ($payout->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800'))) }}">
                                {{ __('affiliate.payout_status.' . $payout->status) }}
                            </span>
                        </p>
                        <p><strong>{{ __('messages.requested') }}:</strong> {{ $payout->created_at->format('d M Y, H:i') }}</p>
                        @if($payout->processed_at)
                            <p><strong>{{ __('messages.processed') }}:</strong> {{ $payout->processed_at->format('d M Y, H:i') }}</p>
                            <p><strong>{{ __('messages.processed_by') }}:</strong> {{ $payout->processedBy->name ?? '-' }}</p>
                        @endif
                        @if($payout->payout_reference)
                            <p><strong>{{ __('affiliate.payout_reference') }}:</strong> {{ $payout->payout_reference }}</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($payout->payout_details)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2">{{ __('affiliate.payout_details') }}</h4>
                    <pre class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700">{{ json_encode($payout->payout_details, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif

            @if($payout->notes)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2">{{ __('messages.notes') }}</h4>
                    <p class="text-gray-700">{{ $payout->notes }}</p>
                </div>
            @endif

            <!-- Commissions included in this payout -->
            @if($payout->commissions->count() > 0)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4">{{ __('affiliate.commissions') }} ({{ $payout->commissions->count() }})</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.tier') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.rate') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($payout->commissions as $commission)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ __('affiliate.tier') }} {{ $commission->tier }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ currency($commission->commission_amount) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $commission->commission_rate }}%</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $commission->created_at->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if(in_array($payout->status, ['pending', 'processing']))
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4">{{ __('messages.process_payout') }}</h4>
                    <form action="{{ route('admin.affiliate.payouts.update', $payout) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('affiliate.status') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="pending" {{ $payout->status === 'pending' ? 'selected' : '' }}>{{ __('affiliate.payout_status.pending') }}</option>
                                    <option value="processing" {{ $payout->status === 'processing' ? 'selected' : '' }}>{{ __('affiliate.payout_status.processing') }}</option>
                                    <option value="completed" {{ $payout->status === 'completed' ? 'selected' : '' }}>{{ __('affiliate.payout_status.completed') }}</option>
                                    <option value="failed" {{ $payout->status === 'failed' ? 'selected' : '' }}>{{ __('affiliate.payout_status.failed') }}</option>
                                    <option value="cancelled" {{ $payout->status === 'cancelled' ? 'selected' : '' }}>{{ __('affiliate.payout_status.cancelled') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="payout_reference" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('affiliate.payout_reference') }} ({{ __('affiliate.optional') }})
                                </label>
                                <input type="text" name="payout_reference" id="payout_reference" 
                                    value="{{ old('payout_reference', $payout->payout_reference) }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.payout_reference_hint') }}</p>
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.notes') }} ({{ __('affiliate.optional') }})
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ old('notes', $payout->notes) }}</textarea>
                            </div>
                            <div class="flex items-center justify-end gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    {{ __('messages.update') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

