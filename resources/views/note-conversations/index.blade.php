@extends('layouts.app')

@section('title', 'Percakapan Produk')

@section('content')
<div class="py-10 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Percakapan Produk</h1>
            <p class="mt-2 text-sm text-gray-600">
                Chat pribadi antara buyer dan seller setelah pembelian. Setiap produk memiliki forum percakapan sendiri.
            </p>
        </div>

        @if ($conversations->isEmpty())
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-10 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Belum ada percakapan</h2>
                <p class="text-sm text-gray-500">
                    Percakapan akan otomatis dibuat setelah transaksi berhasil. Anda bisa kembali ke sini untuk melanjutkan chat dengan lawan transaksi.
                </p>
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center mt-6 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Jelajahi Marketplace
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($conversations as $conversation)
                    @php
                        $lastMessage = $conversation->latestMessage;
                        $otherUser = $conversation->buyer_id === $user->id ? $conversation->seller : $conversation->buyer;
                        $timestamp = $conversation->last_message_at ?? $conversation->updated_at;
                    @endphp
                    <a href="{{ route('note-conversations.show', $conversation) }}"
                        class="block bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-xs uppercase text-gray-400">
                                    <span>{{ $conversation->note->title ?? 'Produk tidak tersedia' }}</span>
                                    <span>•</span>
                                    <span>{{ $conversation->buyer_id === $user->id ? 'Anda sebagai Buyer' : 'Anda sebagai Seller' }}</span>
                                </div>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ $otherUser->name }} ({{ $otherUser->role ?? 'User' }})
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    @if ($lastMessage)
                                        <span class="font-medium">
                                            {{ $lastMessage->sender_id === $user->id ? 'Anda:' : ($lastMessage->sender->name ?? 'Pengguna') . ':' }}
                                        </span>
                                        {{ \Illuminate\Support\Str::limit($lastMessage->message, 120) }}
                                    @else
                                        Belum ada pesan. Mulai percakapan dengan mengirim pesan pertama.
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400 block">
                                    {{ $timestamp?->diffForHumans() ?? '' }}
                                </span>
                                <span
                                    class="mt-2 inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600">
                                    Lanjutkan Chat →
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $conversations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

