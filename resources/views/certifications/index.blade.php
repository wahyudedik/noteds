@extends('layouts.app')

@section('title', 'Certifications')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Certifications</h1>
            <p class="mt-2 text-base text-gray-600">Get certified to showcase your expertise and build trust with buyers.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @foreach($certifications as $category => $certs)
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-4 capitalize">{{ $category }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($certs as $certification)
                        @php
                            $userCert = $userCertifications[$certification->id] ?? null;
                        @endphp
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        @if($certification->icon)
                                            <span class="text-3xl">{{ $certification->icon }}</span>
                                        @endif
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $certification->name }}</h3>
                                        </div>
                                    </div>
                                    @if($userCert && $userCert->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            ✓ Certified
                                        </span>
                                    @elseif($userCert && $userCert->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-4">{{ $certification->description }}</p>

                                @if($certification->requirements)
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Requirements:</h4>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            @foreach($certification->requirements as $req)
                                                <li class="flex items-start">
                                                    <span class="mr-2">•</span>
                                                    <span>{{ $req }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="flex items-center justify-between mt-4">
                                    <a href="{{ route('certifications.show', $certification) }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View Details →
                                    </a>
                                    @if(!$userCert || $userCert->status === 'rejected')
                                        <a href="{{ route('certifications.show', $certification) }}" 
                                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md transition-colors">
                                            Apply Now
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

