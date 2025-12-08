@extends('layouts.guest')

@section('content')
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold text-slate-900">{{ __('messages.confirm_password_title') ?? 'Konfirmasi kata sandi' }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('messages.confirm_password_message') ?? 'Silakan masukkan kata sandi Anda untuk melanjutkan. Kami membutuhkan verifikasi tambahan demi keamanan akun Anda.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div class="space-y-2">
                <x-input-label for="password" :value="__('messages.password')" />
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('messages.confirm') }}
            </x-primary-button>
        </form>
    </div>
@endsection
