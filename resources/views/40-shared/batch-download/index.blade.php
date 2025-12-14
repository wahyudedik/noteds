@extends('40-shared.layouts.app')

@section('title', __('messages.batch_download'))

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.batch_download') }}</h1>
                <p class="text-gray-600">Select multiple purchased notes to download in one zip.</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600">Coming soon: bulk selection UI and progress indicator.</p>
                <form action="{{ route('batch-download.download') }}" method="POST" class="mt-4">
                    @csrf
                    <button disabled class="px-4 py-2 bg-gray-300 text-gray-700 rounded">Prepare Download (Coming
                        Soon)</button>
                </form>
            </div>
        </div>
    </div>
@endsection
