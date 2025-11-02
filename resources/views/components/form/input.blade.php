@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => null,
    'required' => false,
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block font-medium text-sm text-gray-700">
            {{ $label }} @if($required)<span class="text-red-600">*</span>@endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->class([
            'mt-1 block w-full rounded-md border-gray-300 shadow-sm',
            'ring-1 ring-red-500' => $errors->has($name),
        ]) }}
        @if($required) required @endif
    />

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>


