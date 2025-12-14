@extends('40-shared/layouts/app')

@section('title', __('Forum Preferences'))

@section('content')
    <div class="bg-gray-50 min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('Forum Preferences') }}</h1>
                <p class="text-lg text-gray-600">{{ __('Manage your forum email notifications') }}</p>
            </div>

            <!-- Settings Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <form action="{{ route('forum.preferences.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Email Preferences Section -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Email Notifications') }}</h2>
                        <p class="text-gray-600 mb-6">
                            {{ __('Choose which forum activities you want to be notified about via email') }}</p>

                        <!-- Post Liked -->
                        <div class="border-b border-gray-200 pb-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="post_liked" class="text-lg font-semibold text-gray-900">
                                        {{ __('Post Liked') }}
                                    </label>
                                    <p class="text-gray-600 text-sm mt-1">
                                        {{ __('Get notified when someone likes your post') }}</p>
                                </div>
                                <div class="ml-6">
                                    <input type="hidden" name="post_liked" value="0">
                                    <input type="checkbox" id="post_liked" name="post_liked" value="1"
                                        {{ isset($preferences['post_liked']) && $preferences['post_liked'] ? 'checked' : '' }}
                                        class="w-6 h-6 text-blue-600 rounded cursor-pointer focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Post Commented -->
                        <div class="border-b border-gray-200 pb-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="post_commented" class="text-lg font-semibold text-gray-900">
                                        {{ __('Post Commented') }}
                                    </label>
                                    <p class="text-gray-600 text-sm mt-1">
                                        {{ __('Get notified when someone comments on your post') }}</p>
                                </div>
                                <div class="ml-6">
                                    <input type="hidden" name="post_commented" value="0">
                                    <input type="checkbox" id="post_commented" name="post_commented" value="1"
                                        {{ isset($preferences['post_commented']) && $preferences['post_commented'] ? 'checked' : '' }}
                                        class="w-6 h-6 text-blue-600 rounded cursor-pointer focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Comment Replied -->
                        <div class="border-b border-gray-200 pb-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="comment_replied" class="text-lg font-semibold text-gray-900">
                                        {{ __('Comment Replied') }}
                                    </label>
                                    <p class="text-gray-600 text-sm mt-1">
                                        {{ __('Get notified when someone replies to your comment') }}</p>
                                </div>
                                <div class="ml-6">
                                    <input type="hidden" name="comment_replied" value="0">
                                    <input type="checkbox" id="comment_replied" name="comment_replied" value="1"
                                        {{ isset($preferences['comment_replied']) && $preferences['comment_replied'] ? 'checked' : '' }}
                                        class="w-6 h-6 text-blue-600 rounded cursor-pointer focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- Comment Liked -->
                        <div class="border-b border-gray-200 pb-6 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="comment_liked" class="text-lg font-semibold text-gray-900">
                                        {{ __('Comment Liked') }}
                                    </label>
                                    <p class="text-gray-600 text-sm mt-1">
                                        {{ __('Get notified when someone likes your comment') }}</p>
                                </div>
                                <div class="ml-6">
                                    <input type="hidden" name="comment_liked" value="0">
                                    <input type="checkbox" id="comment_liked" name="comment_liked" value="1"
                                        {{ isset($preferences['comment_liked']) && $preferences['comment_liked'] ? 'checked' : '' }}
                                        class="w-6 h-6 text-blue-600 rounded cursor-pointer focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- New Follower -->
                        <div class="pb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <label for="new_follower" class="text-lg font-semibold text-gray-900">
                                        {{ __('New Follower') }}
                                    </label>
                                    <p class="text-gray-600 text-sm mt-1">{{ __('Get notified when someone follows you') }}
                                    </p>
                                </div>
                                <div class="ml-6">
                                    <input type="hidden" name="new_follower" value="0">
                                    <input type="checkbox" id="new_follower" name="new_follower" value="1"
                                        {{ isset($preferences['new_follower']) && $preferences['new_follower'] ? 'checked' : '' }}
                                        class="w-6 h-6 text-blue-600 rounded cursor-pointer focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Help Text -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
                        <p class="text-sm text-blue-800">
                            <strong>{{ __('Note:') }}</strong>
                            {{ __('You can change these preferences at any time. Disabling notifications will not affect your account.') }}
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('forum.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                            {{ __('Back to Forum') }}
                        </a>
                        <button type="submit"
                            class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            {{ __('Save Preferences') }}
                        </button>
                    </div>
                </form>

                <!-- Success Message -->
                @if (session('success'))
                    <div class="mt-8 bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-green-800">{{ session('success') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
