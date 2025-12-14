<a href="{{ $notification->link ?? '#' }}"
    class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-150 {{ !$notification->is_read ? 'bg-blue-50' : '' }}">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ $notification->title }}</p>
            <p class="text-xs text-gray-500 truncate">{{ $notification->message }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
        @if (!$notification->is_read)
            <div class="flex-shrink-0">
                <span class="w-2 h-2 bg-blue-600 rounded-full block"></span>
            </div>
        @endif
    </div>
</a>


