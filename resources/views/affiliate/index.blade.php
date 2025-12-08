@extends('layouts.app')

@section('title', __('affiliate.title'))

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header dengan Gradient -->
            <div class="mb-12">
                <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-2xl p-8 shadow-2xl">
                    <h1 class="text-4xl md:text-5xl font-black text-white mb-3">{{ __('affiliate.title') }}</h1>
                    <p class="text-lg text-blue-100 max-w-2xl">{{ __('affiliate.description') }}</p>
                    <div class="mt-4 flex items-center gap-2 text-blue-200 text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                        <span>Track your affiliate performance in real-time</span>
                    </div>
                </div>
            </div>

            <!-- Alert Messages with better styling -->
            @if (session('success'))
                <div class="mb-6 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 border-l-4 border-emerald-500 rounded-lg p-4 backdrop-blur-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-emerald-200">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-gradient-to-r from-red-500/20 to-rose-500/20 border-l-4 border-red-500 rounded-lg p-4 backdrop-blur-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-red-200">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Stats Cards - Modern Design -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <!-- Total Links -->
                <div class="group relative bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl p-6 border border-slate-600 hover:border-blue-500 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-shrink-0 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Links</span>
                        </div>
                        <p class="text-slate-400 text-sm font-medium mb-1">{{ __('affiliate.total_links') }}</p>
                        <p class="text-4xl font-black text-white">{{ $stats['total_links'] }}</p>
                    </div>
                </div>

                <!-- Total Clicks -->
                <div class="group relative bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl p-6 border border-slate-600 hover:border-purple-500 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-shrink-0 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-3 shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-purple-400 uppercase tracking-wider">Clicks</span>
                        </div>
                        <p class="text-slate-400 text-sm font-medium mb-1">{{ __('affiliate.total_clicks') }}</p>
                        <p class="text-4xl font-black text-white">{{ number_format($stats['total_clicks']) }}</p>
                    </div>
                </div>

                <!-- Total Conversions -->
                <div class="group relative bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl p-6 border border-slate-600 hover:border-emerald-500 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-shrink-0 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-3 shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Conversions</span>
                        </div>
                        <p class="text-slate-400 text-sm font-medium mb-1">{{ __('affiliate.total_conversions') }}</p>
                        <p class="text-4xl font-black text-white">{{ number_format($stats['total_conversions']) }}</p>
                        @if ($stats['total_clicks'] > 0)
                            <p class="text-xs text-emerald-300 mt-2 font-semibold">{{ __('affiliate.conversion_rate') }}: {{ number_format($stats['conversion_rate'], 2) }}%</p>
                        @endif
                    </div>
                </div>

                <!-- Total Commissions -->
                <div class="group relative bg-gradient-to-br from-slate-700 to-slate-800 rounded-2xl p-6 border border-slate-600 hover:border-amber-500 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-600/10 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex-shrink-0 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-3 shadow-lg">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider">Earnings</span>
                        </div>
                        <p class="text-slate-400 text-sm font-medium mb-1">{{ __('affiliate.total_commissions') }}</p>
                        <p class="text-4xl font-black text-white">{{ currency($stats['total_commissions']) }}</p>
                        <p class="text-xs text-amber-300 mt-2 font-semibold">⏳ {{ __('affiliate.pending') }}: {{ currency($stats['pending_commissions']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Earnings & Payouts Section - Modern Design -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <!-- Available Balance -->
                <div class="group relative bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold text-emerald-200 uppercase tracking-widest">Available Balance</span>
                            <svg class="h-8 w-8 text-emerald-300 opacity-50" fill="currentColor" viewBox="0 0 20 20"><path d="M8.16 2.75a.75.75 0 00-1.32 0l-3.816 9.5A.75.75 0 003.5 13h13a.75.75 0 00.684-1.25L13.16 2.75z"/></svg>
                        </div>
                        <p class="text-4xl font-black text-white">{{ currency($stats['available_balance']) }}</p>
                        <p class="text-emerald-100 text-sm mt-2">Ready for withdrawal</p>
                    </div>
                </div>

                <!-- Approved Commissions -->
                <div class="group relative bg-gradient-to-br from-blue-600 to-cyan-700 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold text-blue-200 uppercase tracking-widest">Approved Commissions</span>
                            <svg class="h-8 w-8 text-blue-300 opacity-50" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        </div>
                        <p class="text-4xl font-black text-white">{{ currency($stats['approved_commissions']) }}</p>
                        <p class="text-blue-100 text-sm mt-2">Verified earnings</p>
                    </div>
                </div>

                <!-- Total Payouts -->
                <div class="group relative bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold text-indigo-200 uppercase tracking-widest">Total Payouts</span>
                            <svg class="h-8 w-8 text-indigo-300 opacity-50" fill="currentColor" viewBox="0 0 20 20"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>
                        </div>
                        <p class="text-4xl font-black text-white">{{ currency($stats['total_payouts']) }}</p>
                        @if ($stats['pending_payouts'] > 0)
                            <p class="text-indigo-100 text-sm mt-2">⏳ Pending: {{ currency($stats['pending_payouts']) }}</p>
                        @else
                            <p class="text-indigo-100 text-sm mt-2">All payouts completed</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Affiliate Links Section - Modern Header -->
            <div class="mb-12">
                <div class="bg-gradient-to-r from-slate-700 to-slate-800 rounded-t-2xl p-6 border border-slate-600 flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            {{ __('affiliate.affiliate_links') }}
                        </h3>
                        <p class="text-slate-300 text-sm mt-2">{{ __('affiliate.manage_links_description') }}</p>
                    </div>
                    <button onclick="document.getElementById('create-link-modal').classList.remove('hidden')"
                        class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ __('affiliate.create_link') }}
                    </button>
                </div>
                
                <!-- Links Container -->
                <div class="bg-slate-800 rounded-b-2xl p-6 border border-t-0 border-slate-600">
                    @if ($affiliateLinks->count() > 0)
                        <div class="space-y-4">
                            @foreach ($affiliateLinks as $link)
                                <!-- Link Card - Modern Design -->
                                <div class="group relative bg-gradient-to-r from-slate-700 to-slate-750 rounded-xl p-6 border border-slate-600 hover:border-blue-500 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-xl"></div>
                                    
                                    <div class="relative">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <h4 class="text-lg font-bold text-white">
                                                        {{ $link->name ?: __('affiliate.link') }} <code class="text-sm bg-slate-600 px-2 py-1 rounded ml-2 text-blue-300">#{{ $link->code }}</code>
                                                    </h4>
                                                </div>
                                                @if ($link->description)
                                                    <p class="text-slate-300 text-sm mb-3">{{ $link->description }}</p>
                                                @endif
                                                
                                                <!-- Link URL with copy button -->
                                                <div class="bg-slate-900/50 rounded-lg p-3 mb-4 flex items-center justify-between group/url">
                                                    <code class="text-xs text-slate-300 break-all font-mono">{{ $link->full_url }}</code>
                                                    <button onclick="copyLink('{{ $link->full_url }}')" class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium whitespace-nowrap transition-colors">
                                                        {{ __('affiliate.copy') }}
                                                    </button>
                                                </div>

                                                @if ($link->landing_page_slug)
                                                    <p class="text-xs text-slate-400 mb-3">
                                                        <span class="font-semibold text-slate-300">{{ __('affiliate.landing_page_url') }}:</span>
                                                        <a href="{{ $link->landing_page_url }}" target="_blank" class="text-blue-400 hover:text-blue-300 ml-2">{{ $link->landing_page_url }}</a>
                                                    </p>
                                                @endif

                                                <!-- Stats Grid -->
                                                <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-600">
                                                    <div class="text-center">
                                                        <p class="text-2xl font-bold text-blue-400">{{ number_format($link->clicks) }}</p>
                                                        <p class="text-xs text-slate-400 mt-1">{{ __('affiliate.clicks') }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-2xl font-bold text-emerald-400">{{ number_format($link->conversions) }}</p>
                                                        <p class="text-xs text-slate-400 mt-1">{{ __('affiliate.conversions') }}</p>
                                                        @if ($link->clicks > 0)
                                                            <p class="text-xs text-slate-500 mt-1">{{ number_format(($link->conversions / max($link->clicks, 1)) * 100, 1) }}%</p>
                                                        @endif
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-2xl font-bold text-amber-400">{{ currency($link->total_commission) }}</p>
                                                        <p class="text-xs text-slate-400 mt-1">{{ __('affiliate.commissions') }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Badge -->
                                            @if ($link->is_active)
                                                <span class="inline-block bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-full text-xs font-bold border border-emerald-500/50">✓ {{ __('affiliate.active') }}</span>
                                            @else
                                                <span class="inline-block bg-slate-600/50 text-slate-300 px-3 py-1 rounded-full text-xs font-bold border border-slate-500/50">✕ {{ __('affiliate.inactive') }}</span>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-slate-600">
                                            <button onclick="editLink('{{ $link->id }}')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                                ✎ {{ __('affiliate.edit') }}
                                            </button>
                                            <button onclick="editLandingPage('{{ $link->id }}')" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                                🎨 {{ __('affiliate.edit_landing_page') }}
                                            </button>
                                            <button onclick="managePromotionalMaterials('{{ $link->id }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                                📋 {{ __('affiliate.promotional_materials') }}
                                            </button>
                                            <form action="{{ route('affiliate.links.delete', $link) }}" method="POST" onsubmit="return confirm('{{ __('affiliate.delete_confirm') }}')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-red-500/50">
                                                    🗑️ {{ __('affiliate.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-16">
                            <div class="mb-4">
                                <svg class="mx-auto h-16 w-16 text-slate-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <p class="text-slate-300 text-lg font-medium mb-4">{{ __('affiliate.no_links') }}</p>
                            <button onclick="document.getElementById('create-link-modal').classList.remove('hidden')" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl transition-all duration-300 font-semibold shadow-lg hover:shadow-xl">
                                {{ __('affiliate.create_first_link') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Leaderboard Link -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('affiliate.leaderboard_title') }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ __('affiliate.leaderboard_description') }}</p>
                    <a href="{{ route('affiliate.leaderboard') }}"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                        {{ __('affiliate.view_leaderboard') }}
                    </a>
                </div>
            </div>

            <!-- Commission Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Commission by Tier -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.commission_by_tier') }}</h3>
                    </div>
                    <div class="p-6">
                        @if ($commissionByTier->count() > 0)
                            <div class="space-y-4">
                                @foreach ([1, 2, 3] as $tier)
                                    @php
                                        $tierData = $commissionByTier->get($tier);
                                        $amount = $tierData ? $tierData->total : 0;
                                        $count = $tierData ? $tierData->count : 0;
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ __('affiliate.tier') }}
                                                {{ $tier }}</p>
                                            <p class="text-sm text-gray-500">{{ $count }}
                                                {{ __('affiliate.commissions') }}</p>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900">{{ currency($amount) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-8">{{ __('affiliate.no_commissions') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Commission by Status -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.commission_by_status') }}</h3>
                    </div>
                    <div class="p-6">
                        @if ($commissionByStatus->count() > 0)
                            <div class="space-y-4">
                                @foreach (['pending', 'approved', 'paid'] as $status)
                                    @php
                                        $statusData = $commissionByStatus->get($status);
                                        $amount = $statusData ? $statusData->total : 0;
                                        $count = $statusData ? $statusData->count : 0;
                                    @endphp
                                    @if ($amount > 0 || $count > 0)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-medium 
                                                {{ $status === 'paid' ? 'bg-green-100 text-green-800' : ($status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ __('affiliate.statuses.' . $status) }}
                                                </span>
                                                <p class="text-sm text-gray-500 mt-1">{{ $count }}
                                                    {{ __('affiliate.commissions') }}</p>
                                            </div>
                                            <p class="text-lg font-bold text-gray-900">{{ currency($amount) }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-8">{{ __('affiliate.no_commissions') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Conversions & Commissions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Recent Conversions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_conversions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentConversions as $conversion)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $conversion->converter->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $conversion->conversion_type === 'purchase' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ __('affiliate.conversion_type.' . ($conversion->conversion_type ?? 'signup')) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($conversion->transaction_amount ?? 0) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ ($conversion->converted_at ?? $conversion->created_at)->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('affiliate.no_conversions') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Commissions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_commissions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.tier') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.rate') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentCommissions as $commission)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ __('affiliate.tier') }}
                                            {{ $commission->tier }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $commission->commission_rate }}%
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($commission->commission_amount) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $commission->status === 'paid' ? 'bg-green-100 text-green-800' : ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ __('affiliate.statuses.' . $commission->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('affiliate.no_commissions') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request Payout -->
            @if ($stats['available_balance'] > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.request_payout') }}</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('affiliate.payouts.request') }}" method="POST" id="payout-form">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('affiliate.amount') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="amount" id="amount" step="0.01"
                                            min="0.01" max="{{ $stats['available_balance'] }}"
                                            value="{{ old('amount', $stats['available_balance']) }}" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.available') }}:
                                            {{ currency($stats['available_balance']) }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="payout_method" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('affiliate.payout_method') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="payout_method" id="payout_method" required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        <option value="wallet">{{ __('affiliate.payout_methods.wallet') }}</option>
                                        <option value="bank_transfer">{{ __('affiliate.payout_methods.bank_transfer') }}
                                        </option>
                                        <option value="paypal">{{ __('affiliate.payout_methods.paypal') }}</option>
                                        <option value="other">{{ __('affiliate.payout_methods.other') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center justify-end">
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    {{ __('affiliate.submit_payout_request') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Recent Payouts -->
            @if ($recentPayouts->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_payouts') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.method') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentPayouts as $payout)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($payout->amount) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ __('affiliate.payout_methods.' . $payout->payout_method) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $payout->status === 'completed' ? 'bg-green-100 text-green-800' : ($payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ __('affiliate.payout_status.' . $payout->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $payout->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Link Modal -->
    <div id="create-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.create_link') }}</h3>
                <form action="{{ route('affiliate.links.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.link_name') }}</label>
                            <input type="text" name="name" id="name"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.description') }}</label>
                            <textarea name="description" id="description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label for="destination_url"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.destination_url') }}
                                ({{ __('affiliate.optional') }})</label>
                            <input type="url" name="destination_url" id="destination_url"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.destination_url_hint') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button"
                            onclick="document.getElementById('create-link-modal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            {{ __('affiliate.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ __('affiliate.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Link Modal -->
    <div id="edit-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.edit_link') }}</h3>
                <form id="edit-link-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit-name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.link_name') }}</label>
                            <input type="text" name="name" id="edit-name"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="edit-description"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.description') }}</label>
                            <textarea name="description" id="edit-description" rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label for="edit-destination_url"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.destination_url') }}
                                ({{ __('affiliate.optional') }})</label>
                            <input type="url" name="destination_url" id="edit-destination_url"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.destination_url_hint') }}</p>
                        </div>
                        <div>
                            <label for="edit-is_active" class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="edit-is_active" value="1"
                                    class="rounded">
                                <span class="text-sm font-medium text-gray-700">{{ __('affiliate.active') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button"
                            onclick="document.getElementById('edit-link-modal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            {{ __('affiliate.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ __('affiliate.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyLink(url) {
                navigator.clipboard.writeText(url).then(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('affiliate.link_copied') }}',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert('{{ __('affiliate.link_copied') }}');
                    }
                });
            }

            function editLink(linkId) {
                fetch(`/api/affiliate-links/${linkId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('edit-name').value = data.name || '';
                        document.getElementById('edit-description').value = data.description || '';
                        document.getElementById('edit-destination_url').value = data.destination_url || '';
                        document.getElementById('edit-is_active').checked = data.is_active;

                        const form = document.getElementById('edit-link-form');
                        form.action = `/affiliate/links/${linkId}`;

                        document.getElementById('edit-link-modal').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('affiliate.error_loading_link') }}');
                    });
            }

            function editLandingPage(linkId) {
                fetch(`/api/affiliate-links/${linkId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Load landing page data
                        document.getElementById('landing-link-id').value = linkId;
                        document.getElementById('landing-page-slug').value = data.landing_page_slug || '';
                        document.getElementById('landing-page-content').value = data.landing_page_content || '';
                        document.getElementById('landing-page-preview').innerHTML = data.landing_page_content || '';

                        const form = document.getElementById('landing-page-form');
                        form.action = `/affiliate/links/${linkId}/landing`;

                        document.getElementById('landing-page-modal').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('affiliate.error_loading_link') }}');
                    });
            }

            function updateLandingPagePreview() {
                const content = document.getElementById('landing-page-content').value;
                document.getElementById('landing-page-preview').innerHTML = content ||
                    '<p class="text-gray-400">{{ __('messages.preview') }}</p>';
            }

            function managPromotionalMaterials(linkId) {
                // Load promotional materials for this link
                const promoLinkIdElement = document.getElementById('promo-link-id');
                const promoMaterialsForm = document.getElementById('promo-materials-form');

                if (!promoLinkIdElement) {
                    console.error("Element with ID 'promo-link-id' not found.");
                    return;
                }

                if (!promoMaterialsForm) {
                    console.error("Element with ID 'promo-materials-form' not found.");
                    return;
                }

                promoLinkIdElement.value = linkId;
                promoMaterialsForm.action = `/affiliate/links/${linkId}/promotional-materials`;

                // Load existing materials
                fetch(`/affiliate/links/${linkId}/promotional-materials`)
                    .then(response => response.json())
                    .then(materials => {
                        const container = document.getElementById('existing-materials-list');
                        if (materials.length > 0) {
                            container.innerHTML = materials.map(material => `
                                <div class="border border-gray-200 rounded-lg p-4 flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">${material.name}</h4>
                                        <p class="text-sm text-gray-600 mt-1">${material.type.charAt(0).toUpperCase() + material.type.slice(1)}</p>
                                        ${material.size ? `<p class="text-xs text-gray-500 mt-1">${material.size}</p>` : ''}
                                        <div class="flex gap-2 mt-3">
                                            <button onclick="editPromoMaterial(${material.id})" class="text-blue-600 text-sm hover:underline">Edit</button>
                                            <button onclick="deletePromoMaterial(${material.id})" class="text-red-600 text-sm hover:underline">Delete</button>
                                            ${material.type === 'banner' && material.image_path ? `<a href="/storage/${material.image_path}" target="_blank" class="text-green-600 text-sm hover:underline">View Image</a>` : ''}
                                            ${material.html_code ? `<button onclick="copyPromoCode('${material.html_code}')" class="text-purple-600 text-sm hover:underline">Copy Code</button>` : ''}
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-medium ${material.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${material.is_active ? '{{ __('affiliate.active') }}' : '{{ __('affiliate.inactive') }}'}
                                    </span>
                                </div>
                            `).join('');
                        } else {
                            container.innerHTML =
                                '<p class="text-gray-500 text-center py-8">{{ __('affiliate.no_materials') }}</p>';
                        }
                    })
                    .catch(error => console.error('Error loading materials:', error));

                document.getElementById('promotional-materials-modal').classList.remove('hidden');
            }

            function copyPromoCode(code) {
                navigator.clipboard.writeText(code).then(() => {
                    alert('{{ __('affiliate.html_code_copied') }}');
                }).catch(err => console.error('Copy failed:', err));
            }

            function deletePromoMaterial(materialId) {
                if (!confirm('{{ __('affiliate.delete_confirm') }}')) return;

                fetch(`/affiliate/promotional-materials/${materialId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            // Reload materials list
                            const promoLinkIdElement = document.getElementById('promo-link-id');
                            if (promoLinkIdElement && promoLinkIdElement.value) {
                                managPromotionalMaterials(promoLinkIdElement.value);
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        </script>
    @endpush

    <!-- Landing Page Builder Modal -->
    <div id="landing-page-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.edit_landing_page') }}</h3>

                <form id="landing-page-form" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="landing-link-id">

                    <!-- Landing Page Slug -->
                    <div>
                        <label for="landing-page-slug" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('affiliate.landing_page_slug') }}
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">{{ url('/a') }}/</span>
                            <input type="text" id="landing-page-slug" name="landing_page_slug"
                                placeholder="my-affiliate-link" class="flex-1 rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.slug_hint') }}</p>
                    </div>

                    <!-- Landing Page Content Editor -->
                    <div>
                        <label for="landing-page-content" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('affiliate.landing_page_content') }}
                        </label>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Content Editor -->
                            <div>
                                <textarea id="landing-page-content" name="landing_page_content" rows="10" onchange="updateLandingPagePreview()"
                                    oninput="updateLandingPagePreview()" placeholder="{{ __('affiliate.landing_page_html_hint') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm font-mono text-sm"></textarea>
                            </div>

                            <!-- Preview -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.preview') }}
                                </label>
                                <div id="landing-page-preview"
                                    class="border border-gray-300 rounded-lg p-4 bg-gray-50 overflow-auto h-64 prose prose-sm">
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('affiliate.html_content_allowed') }}</p>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium">{{ __('affiliate.landing_page_info') }}</p>
                                <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                                    <li>{{ __('affiliate.landing_page_info_1') }}</li>
                                    <li>{{ __('affiliate.landing_page_info_2') }}</li>
                                    <li>{{ __('affiliate.landing_page_info_3') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <button type="button"
                            onclick="document.getElementById('landing-page-modal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            {{ __('affiliate.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            {{ __('affiliate.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Promotional Materials Manager Modal -->
    <div id="promotional-materials-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('affiliate.promotional_materials') }}</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Create New Material -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">
                            {{ __('affiliate.create_promotional_material') }}</h4>
                        <form id="promo-materials-form" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <!-- Hidden field to store the link ID -->
                            <input type="hidden" id="promo-link-id" name="link_id" value="">

                            <div>
                                <label for="promo-material-name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.material_name') }} *
                                </label>
                                <input type="text" id="promo-material-name" name="name" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label for="promo-material-type" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.material_type') }} *
                                </label>
                                <select id="promo-material-type" name="type" required
                                    onchange="updatePromoMaterialFields()"
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Select Type</option>
                                    <option value="banner">{{ __('affiliate.banner_image') }}</option>
                                    <option value="link">{{ __('affiliate.link_code') }}</option>
                                    <option value="text">{{ __('affiliate.text_ad') }}</option>
                                </select>
                            </div>

                            <div id="promo-size-field" class="hidden">
                                <label for="promo-material-size" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.material_size') }}
                                </label>
                                <select id="promo-material-size" name="size"
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Select Size</option>
                                    <option value="728x90">Leaderboard (728x90)</option>
                                    <option value="300x250">Medium Rectangle (300x250)</option>
                                    <option value="468x60">Banner (468x60)</option>
                                    <option value="custom">{{ __('affiliate.custom_size') }}</option>
                                </select>
                            </div>

                            <div id="promo-image-field" class="hidden">
                                <label for="promo-material-image" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.banner_image') }}
                                </label>
                                <input type="file" id="promo-material-image" name="image" accept="image/*"
                                    class="w-full">
                                <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.max_2mb') }}</p>
                            </div>

                            <div id="promo-code-field" class="hidden">
                                <label for="promo-material-code" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.html_code') }}
                                </label>
                                <textarea id="promo-material-code" name="html_code" rows="4" placeholder="<a href='...'>Click here</a>"
                                    class="w-full rounded-lg border-gray-300 shadow-sm font-mono text-sm"></textarea>
                            </div>

                            <div>
                                <label for="promo-material-description"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.description') }}
                                </label>
                                <textarea id="promo-material-description" name="description" rows="2"
                                    class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                {{ __('affiliate.create') }}
                            </button>
                        </form>
                    </div>

                    <!-- Existing Materials -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">{{ __('affiliate.existing_materials') }}
                        </h4>
                        <div id="existing-materials-list" class="space-y-3 max-h-96 overflow-y-auto">
                            <p class="text-gray-500 text-center py-8">{{ __('affiliate.no_materials') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Close Button -->
                <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-gray-200">
                    <button type="button"
                        onclick="document.getElementById('promotional-materials-modal').classList.add('hidden')"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        {{ __('affiliate.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updatePromoMaterialFields() {
            const type = document.getElementById('promo-material-type').value;
            document.getElementById('promo-size-field').classList.toggle('hidden', type !== 'banner');
            document.getElementById('promo-image-field').classList.toggle('hidden', type !== 'banner');
            document.getElementById('promo-code-field').classList.toggle('hidden', type !== 'link' && type !== 'text');
        }

        // Fix function name typo
        function managePromotionalMaterials(linkId) {
            managPromotionalMaterials(linkId);
        }
    </script>

@endsection
