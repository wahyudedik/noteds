<div class="space-y-6">
    <p class="text-sm text-gray-600">
        {{ __('messages.delete_account_warning') }}
    </p>

    <div x-data="{ open: false }">
        <button type="button" @click="open = true" 
            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
            {{ __('messages.delete_account') }}
        </button>

        <!-- Modal -->
        <div x-show="open" x-cloak @keydown.escape.window="open = false"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-900 opacity-50" @click="open = false"></div>

                <!-- Modal -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('messages.are_you_sure_delete_account') }}
                    </h2>

                    <p class="text-sm text-gray-600 mb-6">
                        {{ __('messages.delete_account_confirmation') }}
                    </p>

                    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                        @csrf
                        @method('delete')

                        <div>
                            <label for="password" class="sr-only">{{ __('messages.password') }}</label>
                            <input type="password" name="password" id="password" required
                                :placeholder="__('messages.enter_your_password')"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition-all duration-200 @error('password', 'userDeletion') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('password', 'userDeletion')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <button type="button" @click="open = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                {{ __('messages.cancel') }}
                            </button>

                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                {{ __('messages.delete_account') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
