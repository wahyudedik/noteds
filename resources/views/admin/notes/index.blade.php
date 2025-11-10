@extends('layouts.app')

@section('title', __('messages.admin_notes'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.notes_management') }}</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_dashboard') }}</a>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.notes.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" value="{{ request('search') }}" :placeholder="__('messages.search_title_or_content')"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="is_public" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_visibility') }}</option>
                    <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>{{ __('messages.public') }}</option>
                    <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>{{ __('messages.private') }}</option>
                </select>
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_status') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>{{ __('messages.sold') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>
                <select name="sale_mode" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">Semua Sale Mode</option>
                    <option value="scarcity" {{ request('sale_mode') === 'scarcity' ? 'selected' : '' }}>Scarcity Mode</option>
                    <option value="standard" {{ request('sale_mode') === 'standard' ? 'selected' : '' }}>Standard Mode</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('messages.filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'is_public', 'status', 'sale_mode']))
                        <a href="{{ route('admin.notes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('messages.clear') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($notes->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.title') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.owner') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.price') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale Mode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.visibility') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.created') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($notes as $note)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ Str::limit($note->title, 50) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $note->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($note->price > 0)
                                            {{ currency($note->price) }}
                                        @else
                                            <span class="text-gray-400">{{ __('messages.free') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($note->sale_mode)
                                            @if($note->isScarcityMode())
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Scarcity</span>
                                            @elseif($note->isStandardMode())
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Standard</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($note->is_public)
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.public') }}</span>
                                        @else
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ __('messages.private') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ $note->status }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $note->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('marketplace.show', $note) }}" class="text-blue-600 hover:text-blue-800">{{ __('messages.view') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    {{ $notes->links() }}
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600">{{ __('messages.no_notes_found') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

