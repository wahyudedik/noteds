@php($commissionTier = $commissionTier ?? null)

@csrf

<div class="space-y-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            {{ __('messages.tier_name') }} <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" value="{{ old('name', $commissionTier->name ?? '') }}" required
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            {{ __('messages.tier_description') }}
        </label>
        <textarea name="description" id="description" rows="3"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('description', $commissionTier->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="volume_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.volume_threshold_label') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">Rp</span>
                </div>
                <input type="number" name="volume_threshold" id="volume_threshold"
                    value="{{ old('volume_threshold', $commissionTier->volume_threshold ?? 0) }}" min="0" step="1000" required
                    class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('volume_threshold') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
            </div>
            <p class="mt-1 text-xs text-gray-500">{{ __('messages.volume_threshold_help') }}</p>
            @error('volume_threshold')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.sort_order') }}
            </label>
            <input type="number" name="sort_order" id="sort_order"
                value="{{ old('sort_order', $commissionTier->sort_order ?? 0) }}" min="0"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('sort_order') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
            <p class="mt-1 text-xs text-gray-500">{{ __('messages.sort_order_help') }}</p>
            @error('sort_order')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="platform_fee_percent" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.platform_fee_percent') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="number" name="platform_fee_percent" id="platform_fee_percent"
                    value="{{ old('platform_fee_percent', $commissionTier->platform_fee_percent ?? 0) }}" min="0" max="100" step="0.1" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('platform_fee_percent') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">%</span>
                </div>
            </div>
            @error('platform_fee_percent')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="creator_commission_percent" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.creator_commission_percent') }} <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="number" name="creator_commission_percent" id="creator_commission_percent"
                    value="{{ old('creator_commission_percent', $commissionTier->creator_commission_percent ?? 0) }}" min="0" max="100" step="0.1" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('creator_commission_percent') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 text-sm">%</span>
                </div>
            </div>
            @error('creator_commission_percent')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', ($commissionTier->is_active ?? true)) ? 'checked' : '' }}
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="is_active" class="ml-2 text-sm text-gray-700">
            {{ __('messages.tier_is_active_label') }}
        </label>
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-4">
    <a href="{{ route('admin.commission-tiers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
        {{ __('messages.cancel') }}
    </a>
    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
        {{ $submitLabel }}
    </button>
</div>

