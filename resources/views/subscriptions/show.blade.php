@extends('layouts.app')

@section('title', 'Subscribe to ' . $plan->name)

@section('content')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Plan Details -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $plan->name }} Plan</h1>
                <p class="text-gray-600">{{ $plan->description }}</p>
            </div>

            <!-- Pricing Toggle -->
            <div class="flex justify-center mb-8">
                <div class="inline-flex rounded-lg border-2 border-gray-200 p-1 bg-gray-100">
                    <button id="monthly-btn" class="px-6 py-2 rounded-md font-semibold transition-all billing-toggle active">
                        Monthly
                    </button>
                    <button id="yearly-btn" class="px-6 py-2 rounded-md font-semibold transition-all billing-toggle">
                        Yearly
                        <span class="ml-2 text-xs bg-green-500 text-white px-2 py-0.5 rounded-full">
                            Save {{ $plan->yearly_discount_percent }}%
                        </span>
                    </button>
                </div>
            </div>

            <!-- Price Display -->
            <div class="text-center mb-8">
                <div id="monthly-price" class="price-display">
                    <span class="text-5xl font-bold text-gray-900">{{ currency($plan->monthly_price) }}</span>
                    <span class="text-gray-600 ml-2">/month</span>
                </div>
                <div id="yearly-price" class="price-display hidden">
                    <span class="text-5xl font-bold text-gray-900">{{ currency($plan->yearly_price) }}</span>
                    <span class="text-gray-600 ml-2">/year</span>
                    <p class="text-sm text-green-600 mt-2">
                        Save {{ currency($plan->getYearlySavings()) }} per year
                    </p>
                </div>
            </div>

            <!-- Features -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">What's Included</h3>
                <ul class="space-y-3">
                    @foreach($plan->features ?? [] as $feature)
                    <li class="flex items-start">
                        <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-gray-700">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>

            <!-- Subscription Form -->
            <form id="subscribe-form" action="{{ route('subscriptions.subscribe', $plan) }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="billing_cycle" id="billing_cycle" value="monthly">
                
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors payment-method">
                            <input type="radio" name="payment_method" value="wallet" class="sr-only" checked>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-900">Wallet Balance</span>
                                    <span class="text-sm text-gray-600">{{ currency(auth()->user()->wallet_balance) }}</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Pay from your wallet balance</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 ml-4 payment-check hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>

                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition-colors payment-method">
                            <input type="radio" name="payment_method" value="midtrans" class="sr-only">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-900">Credit Card / Bank Transfer</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">Pay via Midtrans (Visa, Mastercard, Bank Transfer, E-wallet)</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 ml-4 payment-check hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    </div>
                </div>

                <!-- Price Summary -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Plan</span>
                        <span class="font-semibold text-gray-900">{{ $plan->name }} (<span id="billing-cycle-text">Monthly</span>)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Total</span>
                        <span class="text-2xl font-bold text-gray-900" id="total-price">{{ currency($plan->monthly_price) }}</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                    Subscribe Now
                </button>

                <p class="text-xs text-center text-gray-500">
                    By subscribing, you agree to our Terms of Service and Privacy Policy. You can cancel anytime.
                </p>
            </form>
        </div>

        <!-- Benefits Reminder -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Benefits</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="text-gray-700">Unlimited access to premium notes</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-gray-700">Exclusive discounts ({{ match($plan->slug) { 'basic' => '10%', 'pro' => '20%', 'enterprise' => '30%', default => '0%' } }})</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="text-gray-700">Early access to new features</span>
                </div>
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-gray-700">Priority customer support</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyBtn = document.getElementById('monthly-btn');
    const yearlyBtn = document.getElementById('yearly-btn');
    const monthlyPrice = document.getElementById('monthly-price');
    const yearlyPrice = document.getElementById('yearly-price');
    const billingCycle = document.getElementById('billing_cycle');
    const billingCycleText = document.getElementById('billing-cycle-text');
    const totalPrice = document.getElementById('total-price');
    const monthlyPriceValue = {{ $plan->monthly_price }};
    const yearlyPriceValue = {{ $plan->yearly_price }};

    monthlyBtn.addEventListener('click', function() {
        monthlyBtn.classList.add('active', 'bg-white', 'text-gray-900');
        monthlyBtn.classList.remove('text-gray-600');
        yearlyBtn.classList.remove('active', 'bg-white', 'text-gray-900');
        yearlyBtn.classList.add('text-gray-600');
        monthlyPrice.classList.remove('hidden');
        yearlyPrice.classList.add('hidden');
        billingCycle.value = 'monthly';
        billingCycleText.textContent = 'Monthly';
        totalPrice.textContent = '{{ currency($plan->monthly_price) }}';
    });

    yearlyBtn.addEventListener('click', function() {
        yearlyBtn.classList.add('active', 'bg-white', 'text-gray-900');
        yearlyBtn.classList.remove('text-gray-600');
        monthlyBtn.classList.remove('active', 'bg-white', 'text-gray-900');
        monthlyBtn.classList.add('text-gray-600');
        monthlyPrice.classList.add('hidden');
        yearlyPrice.classList.remove('hidden');
        billingCycle.value = 'yearly';
        billingCycleText.textContent = 'Yearly';
        totalPrice.textContent = '{{ currency($plan->yearly_price) }}';
    });

    // Payment method selection
    document.querySelectorAll('.payment-method').forEach(method => {
        method.addEventListener('click', function() {
            document.querySelectorAll('.payment-method').forEach(m => {
                m.classList.remove('border-blue-500');
                m.querySelector('.payment-check').classList.add('hidden');
            });
            this.classList.add('border-blue-500');
            this.querySelector('.payment-check').classList.remove('hidden');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });
});
</script>

<style>
.billing-toggle.active {
    background-color: white;
    color: #111827;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>
@endsection

