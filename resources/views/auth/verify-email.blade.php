@extends('layouts.guest')

@section('content')
    <div class="space-y-6">
        <div class="text-center">
            <h2 class="text-3xl font-semibold text-slate-900">{{ __('messages.verify_email_title') ?? 'Verifikasi email Anda' }}</h2>
            <p class="mt-2 text-sm text-slate-600">
                {{ __('messages.verify_email_message') ?? 'Kami telah mengirimkan tautan verifikasi ke email Anda. Jika belum menerima, kirim ulang menggunakan tombol di bawah.' }}
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                {{ __('messages.verification_link_sent') ?? 'Tautan verifikasi baru telah dikirim ke email Anda.' }}
            </div>
        @endif

        <div class="space-y-4">
            <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                @csrf

                <x-primary-button class="w-full justify-center">
                    {{ __('messages.resend_verification_email') }}
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="text-center">
                @csrf

                <button type="submit" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                    {{ __('messages.log_out') }}
                </button>
            </form>
        </div>
    </div>
@endsection
