@extends('40-shared/layouts/app')

@section('title', 'Contact Us - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Get in Touch</h1>
                <p class="text-xl text-gray-600">Have questions? We'd love to hear from you. Send us a message and we'll
                    respond as soon as possible.</p>
            </div>

            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-lg p-8 md:p-10">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-red-900 mb-2">Please fix the errors below:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-red-700">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-green-900">
                            <span class="inline-block mr-2">✓</span>
                            {{ session('success') }}
                        </p>
                    </div>
                @endif

                <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900 mb-2">Your Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="John Doe" required>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-900 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="you@example.com" required>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-900 mb-2">Subject</label>
                        <select id="subject" name="subject"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            required>
                            <option value="">Select a subject</option>
                            <option value="General Inquiry">General Inquiry</option>
                            <option value="Technical Support">Technical Support</option>
                            <option value="Billing">Billing</option>
                            <option value="Partnership">Partnership</option>
                            <option value="Bug Report">Bug Report</option>
                            <option value="Feature Request">Feature Request</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-900 mb-2">Message</label>
                        <textarea id="message" name="message" rows="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Tell us more about your inquiry..." required>{{ old('message') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Maximum 2000 characters</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 rounded-lg transition-colors duration-200">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>

            <!-- Contact Info -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Email</h3>
                    <p class="text-gray-600">support@{{ parse_url(config('app.url'), PHP_URL_HOST) }}</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Quick Response</h3>
                    <p class="text-gray-600">We typically respond within 24 hours</p>
                </div>

                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Support</h3>
                    <p class="text-gray-600">Available 24/7 to help you</p>
                </div>
            </div>
        </div>
    </div>
@endsection
