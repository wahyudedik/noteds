@extends('40-shared/layouts/app')

@section('title', 'Compose Message')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">New Message</h1>

                <form action="{{ route('messages.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="recipient_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Send To
                        </label>
                        <select name="recipient_id" id="recipient_id" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select a user...</option>
                            {{-- You can populate this with users or make it searchable --}}
                        </select>
                        @error('recipient_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Message
                        </label>
                        <textarea name="message" id="message" rows="8" maxlength="2000" required placeholder="Type your message..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 2000 characters</p>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Send Message
                        </button>
                        <a href="{{ route('messages.index') }}"
                            class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

