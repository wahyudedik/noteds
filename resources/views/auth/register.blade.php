@extends('layouts.guest')

@section('content')
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
                        <option value="buyer" {{ old('role', 'buyer') === 'buyer' ? 'selected' : '' }}>
                            {{ __('messages.buyer_option') }}</option>
                        <option value="seller" {{ old('role', 'buyer') === 'seller' ? 'selected' : '' }}>
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

        <!-- Social Login Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-slate-500">Or sign up with</span>
            </div>
        </div>

        <!-- Social Login Buttons -->
        <div class="space-y-3">
            @if (config('services.google.client_id'))
                <a href="{{ route('auth.social.redirect', 'google') }}?role={{ old('role', 'buyer') }}"
                    class="flex items-center justify-center w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-white text-slate-700 hover:bg-slate-50 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Sign up with Google
                </a>
            @endif

            @if (config('services.facebook.client_id'))
                <a href="{{ route('auth.social.redirect', 'facebook') }}?role={{ old('role', 'buyer') }}"
                    class="flex items-center justify-center w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-white text-slate-700 hover:bg-slate-50 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="#1877F2" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                    Sign up with Facebook
                </a>
            @endif

            @if (config('services.github.client_id'))
                <a href="{{ route('auth.social.redirect', 'github') }}?role={{ old('role', 'buyer') }}"
                    class="flex items-center justify-center w-full px-4 py-2.5 border border-slate-300 rounded-lg bg-white text-slate-700 hover:bg-slate-50 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482C19.138 20.197 22 16.425 22 12.017 22 6.484 17.522 2 12 2z"
                            clip-rule="evenodd" />
                    </svg>
                    Sign up with GitHub
                </a>
            @endif
        </div>
    </div>
@endsection
