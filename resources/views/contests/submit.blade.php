@extends('layouts.app')

@section('title', 'Submit Entry - ' . $contest->title)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('contests.show', $contest) }}" class="text-blue-600 hover:text-blue-800">
                ← Back to Contest
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Submit Entry to {{ $contest->title }}</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('contests.submit-entry', $contest) }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="note_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Note <span class="text-red-600">*</span>
                        </label>
                        <select name="note_id" id="note_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('note_id') border-red-500 @enderror">
                            <option value="">Choose a note...</option>
                            @foreach($userNotes as $note)
                                <option value="{{ $note->id }}">{{ $note->title }}</option>
                            @endforeach
                        </select>
                        @error('note_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if($userNotes->isEmpty())
                            <p class="mt-2 text-sm text-yellow-600">You don't have any eligible notes to submit. Create a public note first!</p>
                        @endif
                    </div>

                    <div class="mb-6">
                        <label for="submission_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Why should this note win? (optional)
                        </label>
                        <textarea name="submission_notes" id="submission_notes" rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Tell us why this note deserves to win..."></textarea>
                        <p class="mt-1 text-sm text-gray-500">This will help judges understand your submission better.</p>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('contests.show', $contest) }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors"
                            @if($userNotes->isEmpty()) disabled @endif>
                            Submit Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

