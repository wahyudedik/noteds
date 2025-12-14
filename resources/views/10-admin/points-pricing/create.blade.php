@extends('40-shared/layouts/app')

@section('title', 'Create Points Pricing - Admin')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('admin.index') }}" class="text-blue-600 hover:text-blue-700">← Back</a>
                <h1 class="text-3xl font-bold text-gray-900">Create Points Pricing</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Amount</label>
                            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Points</label>
                            <input type="number" name="points" value="{{ old('points') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Bonus Percentage (%)</label>
                        <input type="number" name="bonus_percentage" value="{{ old('bonus_percentage', 0) }}"
                            step="0.01"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" {{ old('is_active') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Create Pricing
                        </button>
                        <a href="{{ route('admin.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-2 rounded-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
