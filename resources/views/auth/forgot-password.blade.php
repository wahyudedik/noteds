<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('messages.forgot_password_title') ?? 'Lupa kata sandi?' }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                {{ __('messages.forgot_password_message') ?? 'Masukkan alamat email Anda dan kami akan mengirim tautan untuk mengatur ulang kata sandi.' }}
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus placeholder="name@example.com" />
            </div>
            <x-input-error :messages="$errors->get('email')" />

            <div class="space-y-4">
                <x-primary-button class="w-full justify-center">
                    {{ __('messages.email_password_reset_link') }}
                </x-primary-button>
                <p class="text-center text-sm text-slate-500">
                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-500">
                        {{ __('messages.back_to_login') ?? 'Kembali ke halaman masuk' }}
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
