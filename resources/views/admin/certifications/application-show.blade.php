@extends('layouts.app')

@section('title', 'Review Certification Application')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.certifications.applications') }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Applications
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Review Certification Application</h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">User</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ $application->user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $application->user->email }}</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Certification</h3>
                        <p class="text-lg font-semibold text-gray-900">{{ $application->certification->name }}</p>
                        <p class="text-sm text-gray-600">{{ $application->certification->description }}</p>
                    </div>

                    @if($application->application_notes)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Application Notes</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-line">{{ $application->application_notes }}</p>
                            </div>
                        </div>
                    @endif

                    @if($application->evidence && count($application->evidence) > 0)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Evidence/Portfolio</h3>
                            <ul class="space-y-2">
                                @foreach($application->evidence as $link)
                                    <li>
                                        <a href="{{ $link }}" target="_blank" rel="noopener noreferrer" 
                                           class="text-blue-600 hover:text-blue-800 underline">
                                            {{ $link }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Applied At</h3>
                        <p class="text-gray-900">{{ $application->applied_at ? $application->applied_at->format('F d, Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Review Action</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Approve Form -->
                    <div class="border border-green-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-green-900 mb-4">Approve Application</h3>
                        <form action="{{ route('admin.certifications.applications.approve', $application) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Admin Notes (optional)
                                </label>
                                <textarea name="admin_notes" id="approve_notes" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                                    Expires At (optional)
                                </label>
                                <input type="date" name="expires_at" id="expires_at" min="{{ date('Y-m-d') }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>
                            <button type="submit" 
                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
                                Approve Certification
                            </button>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="border border-red-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-red-900 mb-4">Reject Application</h3>
                        <form action="{{ route('admin.certifications.applications.reject', $application) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rejection Reason <span class="text-red-600">*</span>
                                </label>
                                <textarea name="admin_notes" id="reject_notes" rows="3" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                            </div>
                            <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md">
                                Reject Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

