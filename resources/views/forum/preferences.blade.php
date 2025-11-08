@extends('layouts.app')

@section('title', 'Forum Email Preferences')

@section('content')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-5 border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900">Forum Email Notifications</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Pilih email notifikasi yang ingin kamu terima untuk aktivitas forum. Kamu masih akan menerima notifikasi in-app meskipun email dimatikan.
                </p>
            </div>

            <form action="{{ route('forum.preferences.update') }}" method="POST" class="px-6 py-6 space-y-6">
                @if (session('success'))
                    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif

                @csrf
                @method('PUT')

                @php
                    $options = [
                        'post_liked' => [
                            'title' => 'Post Likes',
                            'description' => 'Receive an email when someone likes your post.',
                        ],
                        'post_commented' => [
                            'title' => 'Post Comments',
                            'description' => 'Receive an email when someone comments on your post.',
                        ],
                        'comment_replied' => [
                            'title' => 'Comment Replies',
                            'description' => 'Receive an email when someone replies to your comment.',
                        ],
                        'comment_liked' => [
                            'title' => 'Comment Likes',
                            'description' => 'Receive an email when someone likes your comment.',
                        ],
                        'new_follower' => [
                            'title' => 'New Followers',
                            'description' => 'Receive an email when someone follows you.',
                        ],
                    ];
                @endphp

                <div class="space-y-4">
                    @foreach($options as $key => $option)
                        <div class="flex items-start justify-between gap-4 p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ $option['title'] }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $option['description'] }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox"
                                       name="{{ $key }}"
                                       value="1"
                                       class="sr-only peer"
                                       {{ ($preferences[$key] ?? false) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:bg-blue-600 transition-colors duration-200"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

