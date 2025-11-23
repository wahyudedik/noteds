@extends('layouts.app')

@section('title', 'Edit Badge')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.badges.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Badges
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Edit Badge</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.badges.update', $badge) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $badge->name) }}" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                Slug
                            </label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $badge->slug) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $badge->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                                    Icon (emoji)
                                </label>
                                <input type="text" name="icon" id="icon" value="{{ old('icon', $badge->icon) }}" placeholder="🏆"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Color <span class="text-red-600">*</span>
                                </label>
                                <select name="color" id="color" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="blue" {{ old('color', $badge->color) === 'blue' ? 'selected' : '' }}>Blue</option>
                                    <option value="green" {{ old('color', $badge->color) === 'green' ? 'selected' : '' }}>Green</option>
                                    <option value="red" {{ old('color', $badge->color) === 'red' ? 'selected' : '' }}>Red</option>
                                    <option value="yellow" {{ old('color', $badge->color) === 'yellow' ? 'selected' : '' }}>Yellow</option>
                                    <option value="purple" {{ old('color', $badge->color) === 'purple' ? 'selected' : '' }}>Purple</option>
                                    <option value="orange" {{ old('color', $badge->color) === 'orange' ? 'selected' : '' }}>Orange</option>
                                    <option value="gold" {{ old('color', $badge->color) === 'gold' ? 'selected' : '' }}>Gold</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-600">*</span>
                            </label>
                            <select name="category" id="category" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="milestone" {{ old('category', $badge->category) === 'milestone' ? 'selected' : '' }}>Milestone</option>
                                <option value="quality" {{ old('category', $badge->category) === 'quality' ? 'selected' : '' }}>Quality</option>
                                <option value="community" {{ old('category', $badge->category) === 'community' ? 'selected' : '' }}>Community</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="criteria_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Criteria Type
                                </label>
                                <select name="criteria_type" id="criteria_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Manual (no auto-award)</option>
                                    <option value="sales_count" {{ old('criteria_type', $badge->criteria_type) === 'sales_count' ? 'selected' : '' }}>Sales Count</option>
                                    <option value="rating" {{ old('criteria_type', $badge->criteria_type) === 'rating' ? 'selected' : '' }}>Rating</option>
                                    <option value="helpful_reviews" {{ old('criteria_type', $badge->criteria_type) === 'helpful_reviews' ? 'selected' : '' }}>Helpful Reviews</option>
                                    <option value="activity" {{ old('criteria_type', $badge->criteria_type) === 'activity' ? 'selected' : '' }}>Activity</option>
                                </select>
                            </div>

                            <div>
                                <label for="criteria_value" class="block text-sm font-medium text-gray-700 mb-2">
                                    Criteria Value
                                </label>
                                <input type="number" name="criteria_value" id="criteria_value" value="{{ old('criteria_value', $badge->criteria_value) }}" min="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $badge->sort_order) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $badge->is_active) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>

                            @if($badge->is_custom)
                                <span class="text-sm text-gray-500">Custom Badge</span>
                            @endif
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.badges.index') }}" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                Update Badge
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

