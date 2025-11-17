@extends('layouts.app')

@section('title', 'Admin Tutorials')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Tutorial Management</h2>
            <div class="flex gap-4 items-center">
                <a href="{{ route('admin.tutorials.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    + Tambah Tutorial
                </a>
                <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
            </div>
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

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.tutorials.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, description..."
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="category" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Categories</option>
                    <option value="design" {{ request('category') === 'design' ? 'selected' : '' }}>Design</option>
                    <option value="web" {{ request('category') === 'web' ? 'selected' : '' }}>Web</option>
                    <option value="photo" {{ request('category') === 'photo' ? 'selected' : '' }}>Photo</option>
                    <option value="business" {{ request('category') === 'business' ? 'selected' : '' }}>Business</option>
                </select>
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
                <select name="featured" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'category', 'status', 'featured']))
                        <a href="{{ route('admin.tutorials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($tutorials->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Featured</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tutorials as $tutorial)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ Str::limit($tutorial->title, 50) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $tutorial->author->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $tutorial->category === 'design' ? 'bg-purple-100 text-purple-800' : '' }}
                                            {{ $tutorial->category === 'web' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $tutorial->category === 'photo' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $tutorial->category === 'business' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ $tutorial->category_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($tutorial->status === 'published')
                                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Published</span>
                                        @else
                                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($tutorial->featured)
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">⭐ Featured</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($tutorial->views_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tutorial->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex gap-2">
                                            <a href="{{ route('tuts.show', $tutorial->slug) }}" target="_blank" class="text-blue-600 hover:text-blue-800">View</a>
                                            <a href="{{ route('admin.tutorials.edit', $tutorial) }}" class="text-green-600 hover:text-green-800">Edit</a>
                                            <form method="POST" action="{{ route('admin.tutorials.destroy', $tutorial) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tutorial ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    {{ $tutorials->links() }}
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600">Tidak ada tutorial ditemukan.</p>
                <a href="{{ route('admin.tutorials.create') }}" class="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Buat Tutorial Pertama
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

