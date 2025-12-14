<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Referral Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Referral Overview -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg shadow-lg p-8 text-white">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-3xl font-bold mb-2">💰 {{ __('Earn with Referrals') }}</h3>
                            <p class="text-green-100">{{ __('Share your referral code and earn commissions') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-green-100 text-sm mb-1">{{ __('Total Earnings') }}</p>
                            <p class="text-4xl font-bold" id="total-earnings">Rp 0</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <p class="text-green-100 text-sm mb-1">{{ __('Total Referrals') }}</p>
                            <p class="text-3xl font-bold" id="total-referrals">0</p>
                        </div>
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <p class="text-green-100 text-sm mb-1">{{ __('This Month') }}</p>
                            <p class="text-3xl font-bold" id="month-referrals">0</p>
                        </div>
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <p class="text-green-100 text-sm mb-1">{{ __('Conversion Rate') }}</p>
                            <p class="text-3xl font-bold" id="conversion-rate">0%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral Code Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Referral Code -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">📋 {{ __('Your Referral Code') }}</h4>
                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-4">
                        <p class="text-sm text-gray-600 mb-2">{{ __('Your Code') }}</p>
                        <p class="text-3xl font-bold font-mono text-blue-600 mb-3" id="referral-code">LOADING...</p>
                        <button onclick="copyReferralCode()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            <i class="fas fa-copy"></i>
                            {{ __('Copy Code') }}
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 text-center">
                        {{ __('Share this code with friends to earn commissions on their purchases') }}
                    </p>
                </div>

                <!-- Referral Link -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">🔗 {{ __('Your Referral Link') }}</h4>
                    <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600 mb-2">{{ __('Share this link') }}</p>
                        <div class="flex items-center gap-2">
                            <input type="text" id="referral-link" readonly
                                class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded text-sm font-mono"
                                value="{{ url('/') }}?ref=LOADING">
                            <button onclick="copyReferralLink()"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition flex-shrink-0">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Social Share Buttons -->
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-gray-900">{{ __('Share on') }}</p>
                        <div class="flex gap-2">
                            <button onclick="shareWhatsApp()"
                                class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded font-medium transition">
                                <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                            </button>
                            <button onclick="shareTwitter()"
                                class="flex-1 px-4 py-2 bg-blue-400 hover:bg-blue-500 text-white rounded font-medium transition">
                                <i class="fab fa-twitter mr-1"></i> Twitter
                            </button>
                            <button onclick="shareFacebook()"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium transition">
                                <i class="fab fa-facebook mr-1"></i> Facebook
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Referrals -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h4 class="text-lg font-bold text-gray-900 mb-4">👥 {{ __('Recent Referrals') }}</h4>
                <div id="recent-referrals">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                        <p>{{ __('Loading referrals...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let referralCode = '';
        let referralLink = '';

        // Load referral stats
        async function loadReferralStats() {
            try {
                const response = await fetch('/api/growth/referrals');
                const data = await response.json();

                referralCode = data.referral_code || '';
                referralLink = `{{ url('/') }}?ref=${referralCode}`;

                document.getElementById('referral-code').textContent = referralCode;
                document.getElementById('referral-link').value = referralLink;
                document.getElementById('total-earnings').textContent =
                    `Rp ${(data.total_earnings || 0).toLocaleString()}`;
                document.getElementById('total-referrals').textContent = data.total_referrals || 0;
                document.getElementById('month-referrals').textContent = data.month_referrals || 0;
                document.getElementById('conversion-rate').textContent = `${(data.conversion_rate || 0).toFixed(1)}%`;

                // Load recent referrals
                displayRecentReferrals(data.recent_referrals || []);
            } catch (error) {
                console.error('Failed to load referral stats:', error);
            }
        }

        function displayRecentReferrals(referrals) {
            const container = document.getElementById('recent-referrals');

            if (referrals.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-user-friends text-4xl mb-3"></i>
                        <p class="font-semibold">{{ __('No referrals yet') }}</p>
                        <p class="text-sm">{{ __('Start sharing your referral code to earn commissions') }}</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('User') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Earnings') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            ${referrals.map(ref => `
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">${ref.name || 'User'}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">${new Date(ref.created_at).toLocaleDateString()}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                ref.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                            }">
                                                ${ref.status || 'pending'}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">
                                            Rp ${(ref.earnings || 0).toLocaleString()}
                                        </td>
                                    </tr>
                                `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        function copyReferralCode() {
            navigator.clipboard.writeText(referralCode);
            alert('{{ __('Referral code copied to clipboard!') }}');
        }

        function copyReferralLink() {
            navigator.clipboard.writeText(referralLink);
            alert('{{ __('Referral link copied to clipboard!') }}');
        }

        function shareWhatsApp() {
            const text = encodeURIComponent(`Join NotesDS using my referral code: ${referralCode}\n${referralLink}`);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }

        function shareTwitter() {
            const text = encodeURIComponent(`Join NotesDS using my referral code: ${referralCode}`);
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${encodeURIComponent(referralLink)}`, '_blank');
        }

        function shareFacebook() {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink)}`, '_blank');
        }

        // Load data on page load
        document.addEventListener('DOMContentLoaded', loadReferralStats);
    </script>
</x-app-layout>
