<div>
    <h2 class="text-xl font-semibold mb-3">{{ __('landing.featured') }}</h2>
    <div class="grid md:grid-cols-5 gap-4">
        @foreach ($carouselFeaturedNotes as $featured)
            @php($note = optional($featured)->note)
            <a href="{{ route('marketplace.show', $note->id ?? null) }}"
                class="block bg-white shadow rounded p-4 hover:shadow-md transition">
                <div class="font-semibold">{{ Str::limit($note->title ?? 'Untitled', 40) }}</div>
                <div class="text-sm text-gray-600">{{ Str::limit($note->excerpt ?? ($note->description ?? ''), 80) }}
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    {{ optional($note->tags)->pluck('name')->take(2)->implode(', ') }}</div>
            </a>
        @endforeach
    </div>
</div>
