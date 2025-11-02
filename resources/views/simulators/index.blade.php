@extends('layouts.app')

@section('title', 'Marketing Simulators')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Marketing Simulators</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Calculate your potential earnings and plan your strategy on Noteds
            </p>
        </div>

        <!-- Simulator Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            
            <!-- Earnings Calculator -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Earnings Calculator</h3>
                            <p class="text-blue-100 text-sm">Calculate seller earnings</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <form id="earnings-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Note Price (Rp)</label>
                            <input type="number" id="earnings-price" name="price" value="50000" min="0" step="1000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sales per Month</label>
                            <input type="number" id="earnings-sales" name="sales" value="10" min="0" step="1"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" id="earnings-calculate" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all duration-200">
                            Calculate Earnings
                        </button>
                    </form>
                    <div id="earnings-result" class="mt-6 hidden">
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-green-800 mb-2">Your Potential Earnings</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">Monthly:</span>
                                            <span class="font-bold text-green-700" id="earnings-monthly"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">Yearly:</span>
                                            <span class="font-bold text-green-700" id="earnings-yearly"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">After 20% commission:</span>
                                            <span class="font-bold text-green-700" id="earnings-net"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral ROI Calculator -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Referral ROI</h3>
                            <p class="text-purple-100 text-sm">Track referral earnings</p>
                        </div>
                        <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <form id="referral-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Referrals</label>
                            <input type="number" id="referral-count" name="count" value="20" min="0" step="1"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Avg. Transaction (Rp)</label>
                            <input type="number" id="referral-transaction" name="transaction" value="50000" min="0" step="1000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        </div>
                        <button type="button" id="referral-calculate" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition-all duration-200">
                            Calculate ROI
                        </button>
                    </form>
                    <div id="referral-result" class="mt-6 hidden">
                        <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-purple-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-purple-800 mb-2">Your Referral Rewards</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">Signup rewards:</span>
                                            <span class="font-bold text-purple-700" id="referral-signup"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">Transaction commissions:</span>
                                            <span class="font-bold text-purple-700" id="referral-commission"></span>
                                        </div>
                                        <div class="flex justify-between border-t-2 border-purple-200 pt-2">
                                            <span class="text-gray-700">Total monthly:</span>
                                            <span class="font-bold text-purple-800" id="referral-total"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Premium vs Basic Comparison -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Plan Comparison</h3>
                            <p class="text-green-100 text-sm">Compare features</p>
                        </div>
                        <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Basic Plan -->
                        <div class="border-2 border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-lg font-bold text-gray-900">Basic Plan</h4>
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">FREE</span>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600">10 notes total</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Standard support</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600">Public marketplace</span>
                                </div>
                            </div>
                            <a href="{{ route('register') }}" class="block w-full bg-gray-600 hover:bg-gray-700 text-white text-center font-semibold py-2 rounded-lg transition-all duration-200">
                                Get Started
                            </a>
                        </div>

                        <!-- Premium Plan -->
                        <div class="border-2 border-green-500 rounded-lg p-4 bg-gradient-to-br from-green-50 to-transparent relative">
                            <div class="absolute top-0 right-0 bg-green-500 text-white px-3 py-1 rounded-bl-lg rounded-tr-lg text-xs font-bold">
                                BEST VALUE
                            </div>
                            <div class="flex items-center justify-between mb-3 mt-2">
                                <h4 class="text-lg font-bold text-gray-900">Premium Plan</h4>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Rp25k/mo</span>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">Unlimited notes</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">Priority support</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">Featured in marketplace</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">Advanced analytics</span>
                                </div>
                            </div>
                            <a href="{{ route('subscription.create') }}" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-2 rounded-lg transition-all duration-200">
                                Upgrade Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-12 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">Ready to Start Earning?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Join thousands of creators sharing knowledge and monetizing their expertise on Noteds
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" 
                    class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Create Free Account
                </a>
                <a href="{{ route('marketplace.index') }}" 
                    class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-blue-600 transition-all duration-200">
                    Browse Marketplace
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format number to Rupiah
    function formatRupiah(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    // Earnings Calculator
    const earningsForm = document.getElementById('earnings-calculate');
    earningsForm.addEventListener('click', function() {
        const price = parseFloat(document.getElementById('earnings-price').value) || 0;
        const sales = parseFloat(document.getElementById('earnings-sales').value) || 0;
        
        const grossMonthly = price * sales;
        const grossYearly = grossMonthly * 12;
        const commission = grossMonthly * 0.20; // 20% platform commission
        const netMonthly = grossMonthly - commission;
        
        document.getElementById('earnings-monthly').textContent = formatRupiah(grossMonthly);
        document.getElementById('earnings-yearly').textContent = formatRupiah(grossYearly);
        document.getElementById('earnings-net').textContent = formatRupiah(netMonthly) + '/mo';
        document.getElementById('earnings-result').classList.remove('hidden');
    });

    // Referral ROI Calculator
    const referralForm = document.getElementById('referral-calculate');
    referralForm.addEventListener('click', function() {
        const count = parseFloat(document.getElementById('referral-count').value) || 0;
        const avgTransaction = parseFloat(document.getElementById('referral-transaction').value) || 0;
        
        // Signup reward: Rp 5,000 per referral
        const signupReward = count * 5000;
        
        // Transaction commission: 5% of each transaction
        const commissionPerTransaction = avgTransaction * 0.05;
        const totalCommission = count * commissionPerTransaction;
        
        const totalRewards = signupReward + totalCommission;
        
        document.getElementById('referral-signup').textContent = formatRupiah(signupReward);
        document.getElementById('referral-commission').textContent = formatRupiah(totalCommission);
        document.getElementById('referral-total').textContent = formatRupiah(totalRewards);
        document.getElementById('referral-result').classList.remove('hidden');
    });

    // Auto-calculate on input change
    document.getElementById('earnings-price').addEventListener('input', function() {
        if (document.getElementById('earnings-price').value && document.getElementById('earnings-sales').value) {
            earningsForm.click();
        }
    });
    document.getElementById('earnings-sales').addEventListener('input', function() {
        if (document.getElementById('earnings-price').value && document.getElementById('earnings-sales').value) {
            earningsForm.click();
        }
    });
    document.getElementById('referral-count').addEventListener('input', function() {
        if (document.getElementById('referral-count').value && document.getElementById('referral-transaction').value) {
            referralForm.click();
        }
    });
    document.getElementById('referral-transaction').addEventListener('input', function() {
        if (document.getElementById('referral-count').value && document.getElementById('referral-transaction').value) {
            referralForm.click();
        }
    });
});
</script>
@endpush
@endsection

