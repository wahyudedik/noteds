<section>
    <header>
        <h3 class="text-base font-semibold text-gray-900">
            {{ __('messages.update_password') }}
        </h3>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('messages.ensure_account_secure') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.current_password') }}
            </label>
            <input type="password" id="update_password_current_password" name="current_password"
                autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('current_password', 'updatePassword') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
            @error('current_password', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.new_password') }}
            </label>
            <input type="password" id="update_password_password" name="password" autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('password', 'updatePassword') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
            @error('password', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('messages.confirm_password') }}
            </label>
            <input type="password" id="update_password_password_confirmation" name="password_confirmation"
                autocomplete="new-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('password_confirmation', 'updatePassword') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
            @error('password_confirmation', 'updatePassword')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('messages.save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600">
                    {{ __('messages.saved') }}
                </p>
            @endif
        </div>
    </form>
</section>
