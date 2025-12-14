<div class="space-y-6">
    @foreach ($sections as $section)
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-2">
                {{ is_array($section->title ?? null) ? implode(' ', array_filter(array_map('strval', \Illuminate\Support\Arr::flatten($section->title)))) : $section->title ?? '' }}
            </h3>
            @php($content = $section->content ?? '')
            @if (is_array($content))
                <div class="prose max-w-none">
                    {{ implode(' ', array_filter(array_map('strval', \Illuminate\Support\Arr::flatten($content)))) }}
                </div>
            @else
                <div class="prose max-w-none">{!! $content !!}</div>
            @endif
        </div>
    @endforeach
</div>
