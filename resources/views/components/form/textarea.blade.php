@props(['name', 'label' => null, 'value' => null, 'required' => false, 'rows' => 4])

<div class="mb-4">
    @if ($label)
        <label for="{{ $name }}" class="block font-medium text-sm text-gray-700">
            {{ $label }} @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
        {{ $attributes->class([
            'mt-1 block w-full rounded-md border-gray-300 shadow-sm',
            'ring-1 ring-red-500' => $errors->has($name),
        ]) }}
        @if ($required) required @endif>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
