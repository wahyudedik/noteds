@extends('40-shared/layouts/app')

@section('title', 'Create Studio Order - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-700">← Back</a>
                <h1 class="text-3xl font-bold text-gray-900">New Studio Order</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Order Title</label>
                        <input type="text" name="title" value="{{ old('title') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                        <textarea name="description" rows="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none" required>{{ old('description') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Service Type</label>
                            <select name="service_type"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">Select type</option>
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="writing">Writing</option>
                                <option value="editing">Editing</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Budget</label>
                            <input type="number" name="budget" value="{{ old('budget') }}" step="0.01"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Attachments</label>
                        <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Max 5 files, 10MB total</p>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Create Order
                        </button>
                        <a href="{{ route('dashboard') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-2 rounded-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
