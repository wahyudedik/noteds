<div>
    <h2 class="text-xl font-semibold mb-3">{{ __('landing.latest') }}</h2>
    <div class="grid md:grid-cols-3 gap-4">
        @foreach ($recentCmsPages as $page)
            <a href="{{ route('cms.show', $page->id) }}"
                class="block bg-white shadow rounded p-4 hover:shadow-md transition">
                <div class="font-semibold">{{ $page->title }}</div>
                <div class="text-sm text-gray-600">{{ Str::limit($page->excerpt ?? strip_tags($page->content), 90) }}
                </div>
                <div class="mt-2 text-xs text-gray-500">{{ optional($page->created_at)->diffForHumans() }}</div>
            </a>
        @endforeach
    </div>
</div>
