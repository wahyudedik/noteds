<!-- Enhanced Currency & Language Selector Component -->
<div x-data="currencyLanguageSelector()" class="flex items-center gap-2">
    <!-- Language Selector -->
    <div class="relative">
        <button @click="languageOpen = !languageOpen"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg group relative">
            <div class="relative">
                <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                </svg>
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
            </div>
            <span class="hidden sm:inline font-semibold">{{ strtoupper(app()->getLocale()) }}</span>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': languageOpen }" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Language Dropdown -->
        <div x-show="languageOpen" x-cloak @click.away="languageOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 py-2 z-50 overflow-hidden">

            <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ __('messages.select_language') }}
                </p>
            </div>

            <div class="py-1">
                <button @click="changeLanguage('en')"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm transition-all duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 {{ app()->getLocale() === 'en' ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🇺🇸</span>
                        <div class="text-left">
                            <p class="font-medium text-gray-900 dark:text-white">English</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">US Dollar (USD)</p>
                        </div>
                    </div>
                    @if (app()->getLocale() === 'en')
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>

                <button @click="changeLanguage('id')"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm transition-all duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 {{ app()->getLocale() === 'id' ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🇮🇩</span>
                        <div class="text-left">
                            <p class="font-medium text-gray-900 dark:text-white">Bahasa Indonesia</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rupiah (IDR)</p>
                        </div>
                    </div>
                    @if (app()->getLocale() === 'id')
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>

                <button @click="changeLanguage('ar')"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm transition-all duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 {{ app()->getLocale() === 'ar' ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🇸🇦</span>
                        <div class="text-left">
                            <p class="font-medium text-gray-900 dark:text-white">العربية</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Dirham (AED)</p>
                        </div>
                    </div>
                    @if (app()->getLocale() === 'ar')
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>
            </div>

            <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    Currency auto-syncs with language
                </p>
            </div>
        </div>
    </div>

    <!-- Currency Display Badge -->
    <div
        class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="currentCurrency"></span>
    </div>
</div>

<!-- Toast Notification -->
<div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-4 right-4 z-50">
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 p-4 max-w-sm flex items-start gap-3">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1" x-text="toastTitle"></h4>
            <p class="text-xs text-gray-600 dark:text-gray-400" x-html="toastMessage"></p>
        </div>
        <button @click="showToast = false"
            class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</div>

<script>
    function currencyLanguageSelector() {
        return {
            languageOpen: false,
            showToast: false,
            toastTitle: '',
            toastMessage: '',
            currentCurrency: '{{ auth()->check() ? auth()->user()->currency : config('currency.base_currency', 'IDR') }}',

            changeLanguage(locale) {
                const languageMap = {
                    'en': {
                        name: 'English',
                        currency: 'USD',
                        symbol: '$',
                        flag: '🇺🇸'
                    },
                    'id': {
                        name: 'Bahasa Indonesia',
                        currency: 'IDR',
                        symbol: 'Rp',
                        flag: '🇮🇩'
                    },
                    'ar': {
                        name: 'العربية',
                        currency: 'AED',
                        symbol: 'د.إ',
                        flag: '🇸🇦'
                    }
                };

                const lang = languageMap[locale];
                if (!lang) return;

                // Show loading state
                this.languageOpen = false;
                this.showToast = true;
                this.toastTitle = 'Switching...';
                this.toastMessage = `Changing to ${lang.name}`;

                // Navigate to switch locale route
                window.location.href = `/locale/switch/${locale}`;
            },

            init() {
                // Check for flash message from session (Laravel)
                @if (session('locale_changed'))
                    const locale = '{{ session('locale_changed') }}';
                    const languageMap = {
                        'en': {
                            name: 'English',
                            currency: 'USD',
                            symbol: '$'
                        },
                        'id': {
                            name: 'Bahasa Indonesia',
                            currency: 'IDR',
                            symbol: 'Rp'
                        },
                        'ar': {
                            name: 'العربية',
                            currency: 'AED',
                            symbol: 'د.إ'
                        }
                    };
                    const lang = languageMap[locale];
                    if (lang) {
                        this.currentCurrency = lang.currency;
                        setTimeout(() => {
                            this.showSuccessToast(lang);
                        }, 100);
                    }
                @endif
            },

            showSuccessToast(lang) {
                this.showToast = true;
                this.toastTitle = '✨ Language & Currency Updated!';
                this.toastMessage = `
                Language: <strong>${lang.name}</strong><br>
                Currency: <strong>${lang.currency} (${lang.symbol})</strong>
            `;

                setTimeout(() => {
                    this.showToast = false;
                }, 5000);
            }
        }
    }
</script>
