@extends('40-shared/layouts/app')

@section('title', 'Create Contest')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Create New Contest</h2>

                <form method="POST" action="{{ route('contests.store') }}" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Contest Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @else border @enderror"
                            placeholder="e.g., Best Photography Notes - December 2024">
                        @error('title')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="4" required
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                            placeholder="Describe your contest...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select id="type" name="type" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('type') border-red-500 @enderror">
                                <option value="">Select Type</option>
                                <option value="monthly" {{ old('type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="themed" {{ old('type') === 'themed' ? 'selected' : '' }}>Themed</option>
                                <option value="custom" {{ old('type') === 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('type')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Theme -->
                        <div>
                            <label for="theme" class="block text-sm font-medium text-gray-700">Theme (Optional)</label>
                            <input type="text" id="theme" name="theme" value="{{ old('theme') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Photography, Writing, Design">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                                <option value="">Select Status</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="open" {{ old('status') === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="voting" {{ old('status') === 'voting' ? 'selected' : '' }}>Voting</option>
                                <option value="closed" {{ old('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Entries -->
                        <div>
                            <label for="max_entries_per_user" class="block text-sm font-medium text-gray-700">Max Entries
                                Per User</label>
                            <input type="number" id="max_entries_per_user" name="max_entries_per_user"
                                value="{{ old('max_entries_per_user', 5) }}" min="1" max="20" required
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('max_entries_per_user') border-red-500 @enderror">
                            @error('max_entries_per_user')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Voting Start Date -->
                        <div>
                            <label for="voting_start_date" class="block text-sm font-medium text-gray-700">Voting Start
                                Date</label>
                            <input type="date" id="voting_start_date" name="voting_start_date"
                                value="{{ old('voting_start_date') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <!-- Voting End Date -->
                        <div>
                            <label for="voting_end_date" class="block text-sm font-medium text-gray-700">Voting End
                                Date</label>
                            <input type="date" id="voting_end_date" name="voting_end_date"
                                value="{{ old('voting_end_date') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Prizes -->
                    <div>
                        <label for="prizes_json" class="block text-sm font-medium text-gray-700">Prizes (JSON
                            Format)</label>
                        <textarea id="prizes_json" name="prizes_json" rows="4"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
                            placeholder='["Rp 500.000", "Rp 300.000", "Rp 200.000"]'>{{ old('prizes_json') }}</textarea>
                        <p class="text-sm text-gray-500 mt-1">Enter prizes as JSON array. Example: ["1st Prize", "2nd
                            Prize", "3rd Prize"]</p>
                        @error('prizes_json')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rules -->
                    <div>
                        <label for="rules_text" class="block text-sm font-medium text-gray-700">Rules (One Per
                            Line)</label>
                        <textarea id="rules_text" name="rules_text" rows="4"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Rule 1&#10;Rule 2&#10;Rule 3">{{ old('rules_text') }}</textarea>
                        <p class="text-sm text-gray-500 mt-1">Enter each rule on a new line</p>
                    </div>

                    <!-- Banner Image -->
                    <div>
                        <label for="banner_image" class="block text-sm font-medium text-gray-700">Banner Image URL</label>
                        <input type="url" id="banner_image" name="banner_image" value="{{ old('banner_image') }}"
                            class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="https://example.com/image.jpg">
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-6">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Create Contest
                        </button>
                        <a href="{{ route('contests.index') }}"
                            class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

