<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('messages.confirm_password_title') ?? 'Konfirmasi kata sandi' }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                {{ __('messages.confirm_password_message') ?? 'Silakan masukkan kata sandi Anda untuk melanjutkan. Kami membutuhkan verifikasi tambahan demi keamanan akun Anda.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
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
</x-guest-layout>
