<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('messages.name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- I want to be -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('messages.i_want_to_be')" />

            <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="buyer" {{ old('role') === 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer_option') }}</option>
                <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>{{ __('messages.seller_option') }}</option>
            </select>

            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Referral Code (Optional) -->
        @if(!isset($refCode))
        <div class="mt-4">
            <x-input-label for="referral_code" :value="__('messages.referral_code_optional')" />
            <x-text-input id="referral_code" class="block mt-1 w-full" type="text" name="referral_code" :value="old('referral_code')" autocomplete="off" :placeholder="__('messages.enter_referral_code')" />
            <x-input-error :messages="$errors->get('referral_code')" class="mt-2" />
        </div>
        @else
        <input type="hidden" name="referral_code" value="{{ $refCode }}">
        @endif

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('messages.already_registered') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('messages.register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
