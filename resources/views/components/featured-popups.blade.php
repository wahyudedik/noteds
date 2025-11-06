@php
    // Get featured notes for popups
    $popupWelcome = \App\Models\FeaturedNote::active()
        ->byLocation('popup_welcome')
        ->with(['note.tags', 'note.user'])
        ->inRandomOrder()
        ->limit(3)
        ->get();

    $popupExit = \App\Models\FeaturedNote::active()
        ->byLocation('popup_exit')
        ->with(['note.tags', 'note.user'])
        ->inRandomOrder()
        ->limit(3)
        ->get();

    $popupInterstitial = \App\Models\FeaturedNote::active()
        ->byLocation('popup_interstitial')
        ->with(['note.tags', 'note.user'])
        ->inRandomOrder()
        ->limit(3)
        ->get();
@endphp

<!-- Welcome Popup (for new users) -->
@if ($popupWelcome->count() > 0 && !auth()->check())
    <div x-data="{ show: !localStorage.getItem('popup_welcome_shown'), tracked: false }" x-show="show" x-init="if (show && !tracked) {
        tracked = true;
        const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');
        const impressionUrls = @js($popupWelcome->map(fn($f) => route('api.featured-notes.impression', $f->id)));
        impressionUrls.forEach(url => {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            }).catch(err => console.error('Failed to track impression:', err));
        });
    }" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-black/50"
            @click="show = false; localStorage.setItem('popup_welcome_shown', 'true')"></div>
        <div class="relative bg-white rounded-lg shadow-2xl max-w-4xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <button @click="show = false; localStorage.setItem('popup_welcome_shown', 'true')"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="text-center mb-6">
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">🎉 {{ __('messages.welcome_to_noteds') }}
                </h3>
                <p class="text-gray-600">{{ __('messages.check_out_featured_notes') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($popupWelcome as $featured)
                    @php($note = $featured->note)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-lg transition">
                        <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="hover:text-blue-600 featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">
                                {{ $note->title }}
                            </a>
                        </h4>
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                            {{ Str::limit(strip_tags($note->content), 60) }}
                        </p>
                        <div class="flex items-center justify-between">
                            @if ($note->price > 0)
                                <span class="text-sm font-bold text-green-600">Rp
                                    {{ number_format($note->price, 0, ',', '.') }}</span>
                            @else
                                <span class="text-sm font-bold text-gray-600">FREE</span>
                            @endif
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="text-sm text-blue-600 hover:underline featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">{{ __('messages.view') }} →</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <button @click="show = false; localStorage.setItem('popup_welcome_shown', 'true')"
                    class="px-6 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-medium">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Exit Intent Popup -->
@if ($popupExit->count() > 0 && auth()->check())
    <div x-data="{ show: false, tracked: false }" x-init="document.addEventListener('mouseout', function(e) {
        if (!e.toElement && !e.relatedTarget && e.clientY < 10) {
            if (!localStorage.getItem('popup_exit_shown_today')) {
                show = true;
                localStorage.setItem('popup_exit_shown_today', new Date().toDateString());
                if (!tracked) {
                    tracked = true;
                    const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                    const impressionUrls = @js($popupExit->map(fn($f) => route('api.featured-notes.impression', $f->id)));
                    impressionUrls.forEach(url => {
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).catch(err => console.error('Failed to track impression:', err));
                    });
                }
            }
        }
    });" x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div class="fixed inset-0 bg-black/50" @click="show = false"></div>
        <div class="relative bg-white rounded-lg shadow-2xl max-w-4xl w-full p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <button @click="show = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="text-center mb-6">
                <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ __('messages.wait_dont_go') }}</h3>
                <p class="text-gray-600">{{ __('messages.check_out_before_leave') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach ($popupExit as $featured)
                    @php($note = $featured->note)
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:shadow-lg transition">
                        <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="hover:text-blue-600 featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">
                                {{ $note->title }}
                            </a>
                        </h4>
                        <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                            {{ Str::limit(strip_tags($note->content), 60) }}
                        </p>
                        <div class="flex items-center justify-between">
                            @if ($note->price > 0)
                                <span class="text-sm font-bold text-green-600">Rp
                                    {{ number_format($note->price, 0, ',', '.') }}</span>
                            @else
                                <span class="text-sm font-bold text-gray-600">FREE</span>
                            @endif
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="text-sm text-blue-600 hover:underline featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">{{ __('messages.view') }} →</a>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6 text-center">
                <button @click="show = false"
                    class="px-6 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 font-medium">
                    {{ __('messages.close') }}
                </button>
            </div>
        </div>
    </div>
@endif

<!-- Interstitial Popup (appears after scrolling) -->
@if ($popupInterstitial->count() > 0 && auth()->check())
    <div x-data="{ show: false, shown: false, tracked: false }" x-init="window.addEventListener('scroll', function() {
        if (!shown && window.scrollY > window.innerHeight * 0.5) {
            if (!localStorage.getItem('popup_interstitial_shown_today')) {
                setTimeout(() => {
                    show = true;
                    shown = true;
                    localStorage.setItem('popup_interstitial_shown_today', new Date().toDateString());
                    if (!tracked) {
                        tracked = true;
                        const csrfToken = document.querySelector('meta[name=csrf-token]').getAttribute('content');
                        const impressionUrls = @js($popupInterstitial->map(fn($f) => route('api.featured-notes.impression', $f->id)));
                        impressionUrls.forEach(url => {
                            fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            }).catch(err => console.error('Failed to track impression:', err));
                        });
                    }
                }, 2000);
            }
        }
    });" x-show="show" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95" class="fixed bottom-4 right-4 z-50 max-w-md w-full"
        style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl border-2 border-yellow-400 p-4">
            <div class="flex items-start justify-between mb-3">
                <h4 class="font-bold text-gray-900">{{ __('messages.featured_notes') }}</h4>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="space-y-3">
                @foreach ($popupInterstitial->take(2) as $featured)
                    @php($note = $featured->note)
                    <div class="bg-gray-50 rounded p-3 border border-gray-200">
                        <h5 class="font-semibold text-sm text-gray-900 mb-1 line-clamp-1">
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="hover:text-blue-600 featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">
                                {{ $note->title }}
                            </a>
                        </h5>
                        <div class="flex items-center justify-between text-xs">
                            @if ($note->price > 0)
                                <span class="font-bold text-green-600">Rp
                                    {{ number_format($note->price, 0, ',', '.') }}</span>
                            @else
                                <span class="font-bold text-gray-600">FREE</span>
                            @endif
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="text-blue-600 hover:underline featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">{{ __('messages.view') }} →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
