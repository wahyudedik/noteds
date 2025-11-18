@props(['audioUrl', 'title' => null, 'duration' => null])

<div class="mb-6 bg-gradient-to-r from-yellow-50 via-amber-50 to-orange-50 rounded-xl border-2 border-yellow-200 p-6 shadow-lg">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-bold text-gray-900">🎵 Audio Preview</h3>
            @if($title)
                <p class="text-sm text-gray-600 truncate">{{ $title }}</p>
            @endif
        </div>
    </div>
    
    <audio 
        controls 
        class="w-full rounded-lg shadow-md"
        preload="metadata"
        x-data="{ 
            duration: null,
            currentTime: 0,
            formatTime(seconds) {
                if (!seconds) return '0:00';
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            }
        }"
        @loadedmetadata="$el.duration && (duration = $el.duration)"
        @timeupdate="currentTime = $el.currentTime"
    >
        <source src="{{ $audioUrl }}" type="audio/mpeg">
        <source src="{{ $audioUrl }}" type="audio/ogg">
        <source src="{{ $audioUrl }}" type="audio/wav">
        Your browser does not support the audio element.
    </audio>
    
    @if($duration)
        <p class="mt-2 text-xs text-gray-600 text-center">Duration: {{ gmdate('i:s', $duration) }}</p>
    @endif
</div>

