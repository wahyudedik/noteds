@php($note = optional($heroFeaturedNote)->note)
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="md:flex">
        <div class="md:w-2/3 p-6">
            <h1 class="text-3xl font-bold mb-2">{{ $note->title ?? __('messages.welcome') }}</h1>
            <p class="text-gray-700 mb-4">{{ Str::limit($note->excerpt ?? ($note->description ?? ''), 160) }}</p>
            <div class="flex items-center gap-3 text-sm text-gray-600 mb-4">
                <span>{{ optional($note->user)->username ?? '—' }}</span>
                <span>•</span>
                <span>{{ $note->reviews_count ?? 0 }} reviews</span>
                <span>•</span>
                <span>{{ optional($note->tags)->pluck('name')->take(3)->implode(', ') }}</span>
            </div>
            <a href="{{ route('marketplace.show', $note->id ?? null) }}"
                class="inline-block bg-blue-600 text-white px-4 py-2 rounded">{{ __('landing.view_note') }}</a>
        </div>
        <div class="md:w-1/3 bg-gray-50 p-6">
            <h3 class="font-semibold mb-2">{{ __('landing.highlights') }}</h3>
            <ul class="text-sm text-gray-700 space-y-1">
                <li>{{ __('landing.impressions') }}: {{ $heroFeaturedNote->impressions ?? 0 }}</li>
                <li>{{ __('landing.location') }}: {{ $heroFeaturedNote->location ?? 'landing_hero' }}</li>
                <li>{{ __('landing.active') }}: {{ ($heroFeaturedNote->status ?? 'inactive') === 'active' ? __('landing.yes') : __('landing.no') }}</li>
            </ul>
        </div>
    </div>
</div>
