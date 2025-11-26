@extends('layouts.app')

@section('title', __('affiliate.admin_title') . ' - ' . __('affiliate.recent_commissions'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('affiliate.recent_commissions') }}</h2>
                <p class="text-gray-600 mt-1">{{ __('messages.manage') }} {{ __('affiliate.recent_commissions') }}</p>
            </div>
            <a href="{{ route('admin.affiliate.index') }}" class="text-blue-600 hover:text-blue-800">
                ← {{ __('affiliate.admin_title') }}
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_commissions') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency($totalCommissions) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.pending_commissions') }}</p>
                <p class="text-2xl font-bold text-yellow-600 mt-2">{{ currency($pendingCommissions) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.approved_commissions') }}</p>
                <p class="text-2xl font-bold text-blue-600 mt-2">{{ currency($approvedCommissions) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.statuses.paid') }}</p>
                <p class="text-2xl font-bold text-green-600 mt-2">{{ currency($paidCommissions) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.affiliate.commissions') }}" class="flex gap-4 flex-wrap">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_status') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('affiliate.statuses.pending') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('affiliate.statuses.approved') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('affiliate.statuses.paid') }}</option>
                </select>
                <select name="tier" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_tiers') ?: 'All Tiers' }}</option>
                    <option value="1" {{ request('tier') === '1' ? 'selected' : '' }}>Tier 1</option>
                    <option value="2" {{ request('tier') === '2' ? 'selected' : '' }}>Tier 2</option>
                    <option value="3" {{ request('tier') === '3' ? 'selected' : '' }}>Tier 3</option>
                </select>
                @if($affiliates->count() > 0)
                    <select name="affiliate_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('messages.all_affiliates') ?: 'All Affiliates' }}</option>
                        @foreach($affiliates as $affiliate)
                            <option value="{{ $affiliate->id }}" {{ request('affiliate_id') == $affiliate->id ? 'selected' : '' }}>
                                {{ $affiliate->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('messages.filter') }}
                </button>
                @if(request('status') || request('tier') || request('affiliate_id'))
                    <a href="{{ route('admin.affiliate.commissions') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('messages.clear') }}
                    </a>
                @endif
            </form>
        </div>

        <!-- Approve Commissions Form -->
        @if($commissions->where('status', 'pending')->count() > 0)
            <form action="{{ route('admin.affiliate.commissions.approve') }}" method="POST" id="approve-form" class="mb-6">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    {{ __('affiliate.approve_selected') ?: 'Approve Selected' }}
                </button>
            </form>
        @endif

        <!-- Commissions Table -->
        @if($commissions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @if($commissions->where('status', 'pending')->count() > 0)
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300">
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.user') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.tier') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.rate') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($commissions as $commission)
                                <tr class="hover:bg-gray-50">
                                    @if($commissions->where('status', 'pending')->count() > 0)
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($commission->status === 'pending')
                                                <input type="checkbox" name="commission_ids[]" value="{{ $commission->id }}" 
                                                    class="commission-checkbox rounded border-gray-300">
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commission->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $commission->affiliate->name }}<br>
                                        <span class="text-xs text-gray-500">{{ $commission->affiliate->email }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ __('affiliate.tier') }} {{ $commission->tier }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $commission->commission_rate }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ currency($commission->commission_amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $commission->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                               ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ __('affiliate.statuses.' . $commission->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($commission->conversion)
                                            <a href="{{ route('admin.affiliate.conversions.show', $commission->conversion) }}" 
                                                class="text-blue-600 hover:text-blue-800">
                                                {{ __('messages.view') }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $commissions->links() }}
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-12 text-center">
                <p class="text-sm text-gray-500">{{ __('affiliate.no_commissions') }}</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.getElementById('select-all')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.commission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
@endpush
@endsection

