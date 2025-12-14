@extends('40-shared.layouts.app')

@section('title', __('messages.tickets'))

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-semibold">Ticket #{{ $ticket->id ?? '—' }}</h1>
                <p class="text-gray-600">{{ $ticket->subject ?? 'Sample ticket subject' }}</p>
            </div>
            <a href="{{ route('admin.tickets.index') }}"
                class="text-sm text-blue-600 hover:underline">{{ __('messages.back_to_admin') }}</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2 bg-white shadow rounded p-4 space-y-4">
                <div class="flex gap-3 items-center">
                    <span class="inline-block px-2 py-1 text-xs rounded bg-gray-100 text-gray-800">Status:
                        {{ $ticket->status ?? 'Pending' }}</span>
                    <span class="inline-block px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">Priority:
                        {{ $ticket->priority ?? 'Medium' }}</span>
                    <span class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">Updated:
                        {{ $ticket->updated_at ?? '2025-12-14 10:00' }}</span>
                </div>
                <div class="prose max-w-none">
                    <h2 class="text-lg font-semibold">Description</h2>
                    <p class="text-gray-700">{{ $ticket->description ?? 'Placeholder description for the ticket body...' }}
                    </p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold mb-2">Timeline</h2>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li>2025-12-14 09:45 — Ticket created</li>
                        <li>2025-12-14 10:00 — Assigned to agent</li>
                    </ul>
                </div>
            </div>
            <div class="bg-white shadow rounded p-4 space-y-3">
                <h3 class="font-semibold">Actions</h3>
                <form class="space-y-2">
                    <label class="block text-sm">Update Status</label>
                    <select class="border rounded px-3 py-2 w-full">
                        <option>Open</option>
                        <option>Pending</option>
                        <option>Resolved</option>
                        <option>Closed</option>
                    </select>
                    <label class="block text-sm">Update Priority</label>
                    <select class="border rounded px-3 py-2 w-full">
                        <option>Low</option>
                        <option selected>Medium</option>
                        <option>High</option>
                        <option>Urgent</option>
                    </select>
                    <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection
