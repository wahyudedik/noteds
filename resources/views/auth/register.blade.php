<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('messages.name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Workspace Invitation Notice -->
        @if(isset($invitation) && $invitation)
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800">
                            Anda diundang untuk bergabung dengan workspace: <strong>{{ $invitation->workspace->name }}</strong>
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            Setelah mendaftar, Anda akan otomatis bergabung sebagai {{ $invitation->role === 'admin' ? 'Admin' : 'Member' }}.
                        </p>
                    </div>
                </div>
            </div>
            <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
        @endif

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('messages.email')" />
            @if(isset($invitation) && $invitation)
                <x-text-input id="email" 
                              class="block mt-1 w-full bg-gray-100" 
                              type="email" 
                              name="email" 
                              value="{{ old('email', $invitation->email) }}" 
                              readonly
                              required 
                              autocomplete="username" />
            @else
                <x-text-input id="email" 
                              class="block mt-1 w-full" 
                              type="email" 
                              name="email" 
                              value="{{ old('email') }}" 
                              required 
                              autocomplete="username" />
            @endif
            @if(isset($invitation) && $invitation)
                <p class="mt-1 text-xs text-gray-500">Email ini sudah ditentukan oleh invitation.</p>
            @endif
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- I want to be -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('messages.i_want_to_be')" />

            @if(isset($invitation) && $invitation)
                <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-100" required readonly>
                    <option value="user_workspaces" selected>Workspace User (Team Collaboration)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">Role ini ditentukan oleh invitation workspace.</p>
                <input type="hidden" name="role" value="user_workspaces">
            @else
                <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="buyer" {{ old('role') === 'buyer' ? 'selected' : '' }}>{{ __('messages.buyer_option') }}</option>
                    <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>{{ __('messages.seller_option') }}</option>
                </select>
            @endif

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
