@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.welcome') }}, {{ auth()->user()->name }}!</h1>
                        @if(auth()->user()->hasRole('admin'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                👑 {{ __('messages.admin', [], app()->getLocale()) }}
                            </span>
                        @elseif(auth()->user()->hasRole('seller'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                💰 Seller
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                🛒 Buyer
                            </span>
                        @endif
                    </div>
                    <p class="mt-2 text-base text-gray-600">{{ __('messages.dashboard_subtitle', [], app()->getLocale()) ?: "Here's what's happening with your notes today." }}</p>
                </div>
                
                <!-- Currency & Timezone Selector -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Currency Selector -->
                    <form action="{{ route('locale.set-currency') }}" method="POST" class="inline">
                        @csrf
                        <select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="IDR" {{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>Rp IDR</option>
                            <option value="USD" {{ (auth()->user()->currency ?? 'IDR') === 'USD' ? 'selected' : '' }}>$ USD</option>
                        </select>
                    </form>
                    
                    <!-- Timezone Selector -->
                    <form action="{{ route('locale.set-timezone') }}" method="POST" class="inline">
                        @csrf
                        <select name="timezone" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="Asia/Jakarta" {{ (auth()->user()->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Jakarta)</option>
                            <option value="Asia/Riyadh" {{ (auth()->user()->timezone ?? 'Asia/Jakarta') === 'Asia/Riyadh' ? 'selected' : '' }}>AST (Riyadh)</option>
                            <option value="UTC" {{ (auth()->user()->timezone ?? 'Asia/Jakarta') === 'UTC' ? 'selected' : '' }}>UTC</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.total_notes', [], app()->getLocale()) ?: 'Total Notes' }}</p>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ auth()->user()->notes()->count() }}
                            @if(!auth()->user()->hasPremium())
                                / {{ auth()->user()->getNoteCreationLimit() }}
                            @endif
                        </p>
                        @if(!auth()->user()->hasPremium())
                            <p class="text-xs text-gray-500 mt-1">{{ __('messages.basic_plan', [], app()->getLocale()) ?: 'Basic Plan' }}</p>
                        @else
                            <p class="text-xs text-green-600 mt-1">✓ {{ __('messages.unlimited', [], app()->getLocale()) ?: 'Unlimited' }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.public_notes', [], app()->getLocale()) ?: 'Public Notes' }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->notes()->where('is_public', true)->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">{{ __('messages.wallet_balance', [], app()->getLocale()) ?: 'Wallet Balance' }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ currency(auth()->user()->wallet_balance ?? 0, auth()->user()->currency) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ auth()->user()->transactionsAsSeller()->where('status', 'success')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('notes.create') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors duration-200">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">Create Note</h3>
                        <p class="text-sm text-gray-500 mt-1">Start writing a new note</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('marketplace.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors duration-200">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">Browse Marketplace</h3>
                        <p class="text-sm text-gray-500 mt-1">Discover public notes</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('wallet.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3 group-hover:bg-yellow-200 transition-colors duration-200">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">Manage Wallet</h3>
                        <p class="text-sm text-gray-500 mt-1">Top-up or withdraw funds</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Notes -->
        @php
            $recentNotes = auth()->user()->notes()->latest()->limit(5)->get();
        @endphp

        @if($recentNotes->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Notes</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($recentNotes as $note)
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('notes.show', $note) }}" class="text-base font-medium text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                        {{ $note->title }}
                                    </a>
                                    <p class="mt-1 text-sm text-gray-500">{!! Str::limit(strip_tags($note->content), 80) !!}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        @if($note->is_public)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                Public
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $note->status }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ localized_diff_for_humans($note->created_at) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('notes.show', $note) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        View →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <a href="{{ route('notes.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        View all notes →
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 text-center py-12 px-6">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No notes yet</h3>
                <p class="mt-2 text-sm text-gray-500">Get started by creating your first note.</p>
                <div class="mt-6">
                    <a href="{{ route('notes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Create Note
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
