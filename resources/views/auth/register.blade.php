<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold text-slate-900">
                {{ __('messages.register_title') }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('messages.register_subtitle') }}
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5" enctype="multipart/form-data">
            @csrf

            <div class="space-y-2">
                <x-input-label for="name" :value="__('messages.name')" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus
                    autocomplete="name" placeholder="{{ __('messages.name_placeholder') }}" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            @if (isset($invitation) && $invitation)
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <p class="font-semibold">{{ __('messages.workspace_invite_title') }}</p>
                    <p class="mt-1 text-blue-700">
                        {{ __('messages.workspace_invite_copy') }}
                        <strong>{{ $invitation->workspace->name }}</strong>
                        ({{ $invitation->role === 'admin' ? 'Admin' : 'Member' }})
                    </p>
                    <input type="hidden" name="invite_token" value="{{ $inviteToken }}">
                </div>
            @endif

            <div class="space-y-2">
                <x-input-label for="email" :value="__('messages.email')" />
                @if (isset($invitation) && $invitation)
                    <x-text-input id="email" type="email" name="email"
                        value="{{ old('email', $invitation->email) }}" readonly required autocomplete="username"
                        class="bg-slate-100 cursor-not-allowed" />
                    <p class="text-xs text-slate-500">
                        {{ __('messages.invited_email_hint') }}
                    </p>
                @else
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required
                        autocomplete="username" placeholder="name@example.com" />
                @endif
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="role" :value="__('messages.i_want_to_be')" />
                @if (isset($invitation) && $invitation)
                    <select id="role" name="role"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-600 focus:outline-none"
                        readonly required>
                        <option value="user_workspaces" selected>Workspace User (Team Collaboration)</option>
                    </select>
                    <input type="hidden" name="role" value="user_workspaces">
                @else
                    <select id="role" name="role"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-blue-500 focus:ring focus:ring-blue-500/20"
                        required>
                        <option value="buyer" {{ old('role') === 'buyer' ? 'selected' : '' }}>
                            {{ __('messages.buyer_option') }}</option>
                        <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>
                            {{ __('messages.seller_option') }}</option>
                    </select>
                @endif
                <x-input-error :messages="$errors->get('role')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="password" :value="__('messages.password')" />
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="{{ __('messages.password_placeholder') }}" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password" placeholder="{{ __('messages.password_confirm_placeholder') }}" />
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>

            @if (!isset($refCode))
                <div class="space-y-2">
                    <x-input-label for="referral_code" :value="__('messages.referral_code_optional')" />
                    <x-text-input id="referral_code" type="text" name="referral_code" :value="old('referral_code')"
                        autocomplete="off" placeholder="{{ __('messages.enter_referral_code') }}" />
                    <x-input-error :messages="$errors->get('referral_code')" />
                </div>
            @else
                <input type="hidden" name="referral_code" value="{{ $refCode }}">
            @endif

            <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                <p class="font-semibold">{{ __('messages.verification_info_title') }}</p>
                <p class="mt-1 text-blue-700">
                    {{ __('messages.verification_info_description') }}
                </p>
            </div>

            <div class="space-y-2">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="agree_terms" value="1" required
                        class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-slate-700">
                        {!! __('messages.agreement_consent_copy', [
                            'agreement_url' => route('cms.show', ['cmsPage' => 'user-agreement']),
                            'terms_url' => route('cms.show', ['cmsPage' => 'terms-and-conditions']),
                        ]) !!}
                    </span>
                </label>
                <x-input-error :messages="$errors->get('agree_terms')" />
            </div>

            <div class="space-y-4">
                <x-primary-button class="w-full justify-center">
                    {{ __('messages.register') }}
                </x-primary-button>
                <p class="text-center text-sm text-slate-600">
                    {{ __('messages.already_registered_prompt') }}
                    <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                        {{ __('messages.log_in') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
