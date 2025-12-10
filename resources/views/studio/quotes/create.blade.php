@extends('layouts.app')

@section('title', __('messages.create_quote') . ' — ' . __('messages.studio'))

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-2xl p-8">
                <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('messages.create_quote') }}</h1>
                <form action="{{ route('studio.orders.quotes.store', $order) }}" method="POST" class="space-y-6">
                    @csrf
                    @php $user = auth()->user(); @endphp
                    @if ($user->hasRole('admin'))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.vendor') }} <span
                                    class="text-red-600">*</span></label>
                            <select name="vendor_id" id="vendor_select"
                                class="w-full rounded-lg border-gray-300 seller-search" required
                                data-placeholder="Search seller by name or email...">
                                <option></option>
                                @php
                                    $sellers = \App\Models\User::role('seller')->orderBy('name')->get();
                                @endphp
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}" data-name="{{ $seller->name }}"
                                        data-email="{{ $seller->email }}">
                                        {{ $seller->name }} ({{ $seller->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('vendor_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-2">Cari seller berdasarkan nama atau email</p>
                        </div>
                    @else
                        <div class="p-3 rounded-md bg-gray-50 border text-sm text-gray-700">
                            {{ __('messages.quote_as_vendor') }}: <strong>{{ $user->name }}</strong>
                        </div>
                    @endif
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.quote_total_amount') }}</label>
                        <input type="number" step="0.01" min="1" name="total_amount"
                            value="{{ old('total_amount', 0) }}" class="w-full rounded-lg border-gray-300" required>
                        @error('total_amount')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-data="{ rows: 1 }">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.milestones') }}
                                ({{ __('messages.optional') }})</label>
                            <button type="button" class="text-sm text-blue-600" @click="rows++">+
                                {{ __('messages.add_milestone') }}</button>
                        </div>
                        <template x-for="i in rows" :key="i">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <input type="text" name="milestones[][title]"
                                    placeholder="{{ __('messages.milestone_title') }}"
                                    class="rounded-lg border-gray-300 md:col-span-1">
                                <input type="number" name="milestones[][amount]" step="0.01" min="0"
                                    placeholder="{{ __('messages.amount') }}" class="rounded-lg border-gray-300">
                                <input type="text" name="milestones[][description]"
                                    placeholder="{{ __('messages.order_description') }}"
                                    class="rounded-lg border-gray-300 md:col-span-1">
                            </div>
                        </template>
                        @error('milestones.*.title')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        @error('milestones.*.amount')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('studio.orders.show', $order) }}"
                            class="px-4 py-2 rounded-md border">{{ __('messages.cancel') }}</a>
                        <button type="submit"
                            class="px-4 py-2 rounded-md bg-blue-600 text-white">{{ __('messages.send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (auth()->user()?->hasRole('admin'))
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('#vendor_select').select2({
                    allowClear: true,
                    placeholder: "Search seller by name or email...",
                    width: '100%',
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }

                        var term = params.term.toLowerCase();
                        var text = data.text.toLowerCase();

                        if (text.indexOf(term) > -1) {
                            return data;
                        }

                        return null;
                    }
                });
            });
        </script>

        <style>
            .select2-container--default .select2-selection--single {
                border-color: #d1d5db;
                border-radius: 0.5rem;
                height: 42px;
                display: flex;
                align-items: center;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #374151;
                padding: 0 12px;
            }

            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }

            .select2-dropdown {
                border-color: #d1d5db;
                border-radius: 0.5rem;
                margin-top: 0.25rem;
            }

            .select2-results__option {
                padding: 12px;
                color: #374151;
            }

            .select2-results__option--highlighted {
                background-color: #3b82f6 !important;
                color: white;
            }
        </style>
    @endif
@endsection
