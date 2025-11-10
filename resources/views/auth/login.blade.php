<x-guest-layout>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('messages.log_in_title') ?? 'Masuk ke akun Anda' }}</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                {{ __('messages.log_in_subtitle') ?? 'Selamat datang kembali! Masukkan email dan kata sandi untuk melanjutkan ke dashboard Noteds.' }}
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('messages.password')" />
                    @if (Route::has('password.request'))
                        <a class="text-xs font-semibold text-blue-600 hover:text-blue-500" href="{{ route('password.request') }}">
                            {{ __('messages.forgot_password') }}
                        </a>
                    @endif
                </div>
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-500">
                    <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="remember">
                    <span>{{ __('messages.remember_me') }}</span>
                </label>
                <a href="{{ route('register') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">
                    {{ __('messages.create_account') ?? 'Buat akun baru' }}
                </a>
            </div>

            <x-primary-button class="w-full justify-center">
                {{ __('messages.log_in') }}
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
