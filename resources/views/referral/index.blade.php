@extends('layouts.app')

@section('title', __('messages.referral_program'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.referral_program') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.invite_friends_earn_rewards') }}</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Referrals -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.total_referrals') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalReferrals }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Earned -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.total_earnings') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ currency($totalEarned) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signup Rewards -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.signup_rewards') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ currency($signupRewards) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Rewards -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.transaction_rewards') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ currency($transactionRewards) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Referral Link Card -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 overflow-hidden shadow-sm rounded-lg border border-purple-500 mb-8">
            <div class="p-8">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white mb-2">{{ __('messages.your_referral_link') }}</h2>
                        <p class="text-purple-100 mb-4">{{ __('messages.share_link_earn_rewards') }}</p>
                        <div class="bg-white rounded-lg p-4 flex items-center justify-between">
                            <code class="text-sm text-gray-800 flex-1 mr-4">{{ $referralLink }}</code>
                            <button onclick="copyReferralLink()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                {{ __('messages.copy') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reward Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ __('referral.signup_reward_title') }}</h3>
                        <p class="text-blue-700">
                            {!! __('referral.signup_reward_description', ['amount' => '<strong>' . currency($rewardConfig['signup_reward']) . '</strong>']) !!}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-green-900 mb-2">{{ __('referral.transaction_commission_title') }}</h3>
                        <p class="text-green-700">
                            {!! __('referral.transaction_commission_description', ['percent' => $rewardConfig['transaction_reward_percent']]) !!}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Referrals -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('referral.recent_referrals') }}</h3>
                    <a href="{{ route('referral.statistics') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        {{ __('referral.view_all') }}
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.user') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.reward_type') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.amount') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentReferrals as $referral)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                        @if($referral->referred->avatar)
                                            <img src="{{ $referral->referred->avatar }}" alt="{{ $referral->referred->name }}" class="h-10 w-10 rounded-full object-cover">
                                        @else
                                            <span class="text-xs font-semibold text-white">{{ strtoupper(substr($referral->referred->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $referral->referred->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $referral->referred->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $referral->reward_type === 'signup' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ __('referral.reward_types.' . $referral->reward_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ currency($referral->reward_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $referral->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ __('referral.statuses.' . $referral->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $referral->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="mt-4 text-sm text-gray-500">
                                    {{ __('referral.empty_state_title') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('referral.empty_state_message') }}
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralLink() {
    const link = '{{ $referralLink }}';
    navigator.clipboard.writeText(link).then(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '{{ __('messages.success', [], app()->getLocale()) ?: 'Success' }}',
                text: '{{ __('messages.referral_link_copied', [], app()->getLocale()) ?: 'Referral link copied to clipboard!' }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert('{{ __('messages.referral_link_copied', [], app()->getLocale()) ?: 'Referral link copied to clipboard!' }}');
        }
    }).catch(() => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = link;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '{{ __('messages.success', [], app()->getLocale()) ?: 'Success' }}',
                text: '{{ __('messages.referral_link_copied', [], app()->getLocale()) ?: 'Referral link copied to clipboard!' }}',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert('{{ __('messages.referral_link_copied', [], app()->getLocale()) ?: 'Referral link copied to clipboard!' }}');
        }
    });
}
</script>
@endpush
@endsection

