@extends('40-shared.layouts.app')

@section('title', __('messages.tickets'))

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold">{{ __('messages.tickets') }}</h1>
            <a href="/admin" class="text-sm text-blue-600 hover:underline">{{ __('messages.back_to_admin') }}</a>
        </div>

        <p class="text-gray-600 mb-6">{{ __('messages.coming_soon') }}</p>

        <div class="bg-white shadow rounded p-4">
            <form class="flex flex-wrap gap-3 mb-4">
                <input type="text" name="q" placeholder="Search subject or ID" class="border rounded px-3 py-2 w-64">
                <select name="status" class="border rounded px-3 py-2">
                    <option value="">All Statuses</option>
                    <option>Open</option>
                    <option>Pending</option>
                    <option>Resolved</option>
                    <option>Closed</option>
                </select>
                <select name="priority" class="border rounded px-3 py-2">
                    <option value="">All Priorities</option>
                    <option>Low</option>
                    <option>Medium</option>
                    <option>High</option>
                    <option>Urgent</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2 px-3">ID</th>
                            <th class="py-2 px-3">Subject</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Priority</th>
                            <th class="py-2 px-3">Updated</th>
                            <th class="py-2 px-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-2 px-3">#1001</td>
                            <td class="py-2 px-3">Sample ticket subject</td>
                            <td class="py-2 px-3"><span
                                    class="inline-block px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800">Pending</span>
                            </td>
                            <td class="py-2 px-3">High</td>
                            <td class="py-2 px-3">2025-12-14 10:00</td>
                            <td class="py-2 px-3"><a href="#" class="text-blue-600">View</a></td>
                        </tr>
                        <tr>
                            <td class="py-2 px-3">#1000</td>
                            <td class="py-2 px-3">Another example issue</td>
                            <td class="py-2 px-3"><span
                                    class="inline-block px-2 py-1 text-xs rounded bg-green-100 text-green-800">Resolved</span>
                            </td>
                            <td class="py-2 px-3">Medium</td>
                            <td class="py-2 px-3">2025-12-13 16:20</td>
                            <td class="py-2 px-3"><a href="#" class="text-blue-600">View</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
