@extends('layouts.guest')

@section('content')
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold text-slate-900">
                {{ __('messages.reset_password_title') ?? 'Atur ulang kata sandi' }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('messages.reset_password_subtitle') ?? 'Masukkan kata sandi baru Anda dan konfirmasi untuk menyelesaikan proses pemulihan akun.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="space-y-2">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus
                    autocomplete="username" placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="password" :value="__('messages.password')" />
                <x-text-input id="password" type="password" name="password" required autocomplete="new-password"
                    placeholder="{{ __('messages.password_placeholder') ?? 'Minimal 8 karakter' }}" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="space-y-2">
                <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
                <x-text-input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password"
                    placeholder="{{ __('messages.password_confirm_placeholder') ?? 'Ulangi kata sandi' }}" />
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('messages.reset_password') }}
            </x-primary-button>
        </form>
    </div>
@endsection
