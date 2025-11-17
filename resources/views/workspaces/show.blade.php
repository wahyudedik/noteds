@extends('layouts.app')

@section('title', $workspace->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <a href="{{ route('workspaces.index') }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">{{ $workspace->name }}</h1>
                        <p class="text-xs text-gray-500">{{ __('messages.workspace') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Marketplace Badge -->
                    @if($workspace->isForSale())
                        <div class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                            💰 {{ __('messages.for_sale') }} - {{ currency($workspace->price) }}
                        </div>
                    @elseif($workspace->isSold())
                        <div class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                            ✅ {{ __('messages.sold') }}
                        </div>
                    @endif
                    
                    <!-- Owner/Admin Actions -->
                    @if($workspace->canManage(auth()->user()) && !$workspace->isSold())
                        <!-- Quick Actions -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('workspaces.invite', $workspace) }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Invite Team
                            </a>
                            <a href="{{ route('folders.create', array_filter(['workspace_id' => $workspace->id, 'parent_id' => $currentFolder?->id])) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('messages.new_folder') }}
                            </a>
                            <a href="{{ route('notes.create', array_filter(['workspace_id' => $workspace->id, 'folder_id' => $currentFolder?->id])) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('messages.new_note') }}
                            </a>
                            @if(!$workspace->isForSale())
                                <button onclick="document.getElementById('sell-workspace-modal').classList.remove('hidden')" 
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('messages.sell_workspace') }}
                                </button>
                            @else
                                <a href="{{ route('workspaces.edit', $workspace) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    {{ __('messages.edit_listing') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <!-- Quick Actions for non-owners -->
                        <div class="flex items-center gap-2">
                            @if($workspace->isForSale())
                                <form action="{{ route('workspaces.purchase', $workspace) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        {{ __('messages.buy_workspace') }} - {{ currency($workspace->price) }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="flex max-w-full">
        <!-- Sidebar - Folder Tree -->
        <div class="w-64 bg-white border-r border-gray-200 min-h-screen">
            <div class="p-4 space-y-6">
                <!-- Folders Section -->
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">{{ __('messages.folders') }}</h3>
                    <div class="space-y-1">
                        <!-- Root/Workspace Level -->
                        <a href="{{ route('workspaces.show', $workspace) }}" 
                           class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('workspaces.show') && !request()->has('folder') ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="text-sm font-medium">{{ __('messages.workspace_root') }}</span>
                        </a>
                        
                        @foreach($workspace->folders as $folder)
                            @include('workspaces.partials.folder-tree-item', ['folder' => $folder, 'workspace' => $workspace, 'level' => 0])
                        @endforeach
                    </div>
                </div>

                <!-- AI Features removed - all features are now free and accessible -->
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif
            
            <!-- Breadcrumb -->
            <nav class="flex items-center gap-2 mb-6 text-sm text-gray-600">
                <a href="{{ route('workspaces.index') }}" class="hover:text-gray-900">{{ __('messages.workspaces') }}</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-gray-900 font-medium">{{ $workspace->name }}</span>
                @if($currentFolder)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-900 font-medium">{{ $currentFolder->name }}</span>
                @endif
            </nav>

            <!-- Grid View -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Folders -->
                @php
                    $displayFolders = $currentFolder ? $currentFolder->children : $workspace->folders->whereNull('parent_id');
                @endphp
                
                @foreach($displayFolders as $folder)
                    <a href="{{ route('workspaces.show', ['workspace' => $workspace->id, 'folder' => $folder->id]) }}" 
                       class="group bg-white rounded-lg border border-gray-200 p-4 hover:shadow-lg transition-all duration-200 hover:border-blue-300">
                        <div class="flex flex-col items-center justify-center h-32">
                            @if($folder->color)
                                <div class="w-16 h-16 rounded-lg flex items-center justify-center mb-3" style="background-color: {{ $folder->color }}20;">
                                    <svg class="w-10 h-10" style="color: {{ $folder->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-16 h-16 rounded-lg bg-yellow-100 flex items-center justify-center mb-3">
                                    <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                            @endif
                            <h3 class="text-sm font-medium text-gray-900 text-center line-clamp-2 group-hover:text-blue-600 transition-colors">
                                {{ $folder->name }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $folder->notes()->count() }} {{ __('messages.items') }}
                            </p>
                        </div>
                    </a>
                @endforeach

                <!-- Notes -->
                @php
                    $displayNotes = $currentFolder ? $currentFolder->notes : $workspace->notes->whereNull('folder_id');
                @endphp
                
                @foreach($displayNotes as $note)
                    <a href="{{ route('notes.show', $note) }}" 
                       class="group bg-white rounded-lg border border-gray-200 p-4 hover:shadow-lg transition-all duration-200 hover:border-green-300">
                        <div class="flex flex-col items-center justify-center h-32">
                            <div class="w-16 h-16 rounded-lg bg-blue-100 flex items-center justify-center mb-3">
                                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-medium text-gray-900 text-center line-clamp-2 group-hover:text-green-600 transition-colors">
                                {{ $note->title }}
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $note->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach

                <!-- Empty State -->
                @if($displayFolders->count() === 0 && $displayNotes->count() === 0)
                    <div class="col-span-full text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.empty_workspace') }}</h3>
                        <p class="text-gray-600 mb-6">{{ __('messages.start_creating_folders_notes') }}</p>
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('folders.create', array_filter(['workspace_id' => $workspace->id, 'parent_id' => $currentFolder?->id])) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('messages.create_folder') }}
                            </a>
                            <a href="{{ route('notes.create', array_filter(['workspace_id' => $workspace->id, 'folder_id' => $currentFolder?->id])) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('messages.create_note') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sell Workspace Modal -->
<div id="sell-workspace-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white my-10">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.sell_workspace') }}</h3>
                <button onclick="document.getElementById('sell-workspace-modal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('workspaces.sell', $workspace) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="sale_mode" class="block text-sm font-medium text-gray-700 mb-2">
                            Mode Penjualan
                        </label>
                        <select name="sale_mode" id="sale_mode" required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                            <option value="scarcity" {{ old('sale_mode', $workspace->sale_mode ?: 'scarcity') === 'scarcity' ? 'selected' : '' }}>
                                Scarcity Mode (1x pembelian per user, bisa resell)
                            </option>
                            <option value="standard" {{ old('sale_mode', $workspace->sale_mode) === 'standard' ? 'selected' : '' }}>
                                Standard Mode (Multiple sales, tidak bisa resell)
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Scarcity: Setiap user hanya bisa beli 1x, buyer bisa resell. Standard: Bisa dijual ke banyak buyer, tidak bisa resell.
                        </p>
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.price') }} (Rp) *
                        </label>
                        <input type="number" name="price" id="price" min="0" step="0.01" required
                               value="{{ old('price', $workspace->price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label for="discount_price" class="block text-sm font-medium text-gray-700 mb-2">
                            Harga Diskon (Rp) <span class="text-gray-500 text-xs">(opsional)</span>
                        </label>
                        <input type="number" name="discount_price" id="discount_price" min="0" step="0.01"
                               value="{{ old('discount_price', $workspace->discount_price) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Jika diisi, harga diskon akan ditampilkan sebagai harga utama.
                        </p>
                    </div>
                    <div id="scarcity-options" style="display: {{ old('sale_mode', $workspace->sale_mode ?: 'scarcity') === 'scarcity' ? 'block' : 'none' }};">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="grace_period_days" class="block text-sm font-medium text-gray-700 mb-2">
                                    Grace Period (hari)
                                </label>
                                <input type="number" name="grace_period_days" id="grace_period_days" min="0" max="365" 
                                       value="{{ old('grace_period_days', $workspace->grace_period_days ?? 30) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                <p class="mt-1 text-xs text-gray-500">
                                    Periode untuk repurchase dengan harga original (default: 30 hari)
                                </p>
                            </div>
                            <div>
                                <label for="relist_price_multiplier" class="block text-sm font-medium text-gray-700 mb-2">
                                    Repurchase Multiplier
                                </label>
                                <input type="number" name="relist_price_multiplier" id="relist_price_multiplier" min="1" max="10" step="0.1"
                                       value="{{ old('relist_price_multiplier', $workspace->relist_price_multiplier ?? 1.5) }}"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                <p class="mt-1 text-xs text-gray-500">
                                    Harga repurchase setelah grace period (default: 1.5x)
                                </p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="marketplace_description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.marketplace_description') }}
                        </label>
                        <textarea name="marketplace_description" id="marketplace_description" rows="4"
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500"
                                  placeholder="{{ __('messages.describe_workspace_for_sale') }}">{{ old('marketplace_description', $workspace->marketplace_description) }}</textarea>
                    </div>

                    <!-- File Attachments (Bundle Workspace) -->
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                            File Attachments (Bundle Workspace)
                            <span class="text-xs text-gray-500 font-normal">(Optional - untuk bundle workspace)</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-purple-400 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload files</span>
                                        <input id="attachments" name="attachments[]" type="file" multiple
                                               accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx"
                                               class="sr-only">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, PNG, GIF, XLS, XLSX, PPT, PPTX (Max 50MB per file)</p>
                            </div>
                        </div>
                        @if($workspace->hasAttachments())
                            <div class="mt-3 space-y-2">
                                <p class="text-xs font-medium text-gray-700 mb-2">Current Attachments ({{ $workspace->file_count }}):</p>
                                @foreach($workspace->attachments as $index => $attachment)
                                    @php
                                        $filename = is_array($attachment) ? ($attachment['filename'] ?? 'Unknown') : basename($attachment);
                                    @endphp
                                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded border border-gray-200">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <svg class="w-4 h-4 text-purple-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-xs font-medium text-gray-900 truncate">{{ $filename }}</p>
                                        </div>
                                        <label class="ml-2 flex items-center cursor-pointer">
                                            <input type="checkbox" name="removed_attachments[]" value="{{ $filename }}" class="h-3 w-3 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <span class="ml-1 text-xs text-red-600">Remove</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Thumbnails -->
                    <div>
                        <label for="thumbnails" class="block text-sm font-medium text-gray-700 mb-2">
                            Thumbnails (Gambar Preview)
                            <span class="text-xs text-gray-500 font-normal">(Optional - maks 5 gambar)</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-purple-400 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="thumbnails" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500">
                                        <span>Upload gambar</span>
                                        <input type="file" name="thumbnails[]" id="thumbnails" multiple accept="image/*" class="sr-only">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 5MB per gambar (maks 5 gambar)</p>
                            </div>
                        </div>
                        @if($workspace->hasThumbnails())
                            <div class="mt-3 grid grid-cols-5 gap-2">
                                @foreach($workspace->thumbnails as $index => $thumbnail)
                                    @php
                                        $thumbnailPath = is_array($thumbnail) ? ($thumbnail['path'] ?? $thumbnail) : $thumbnail;
                                    @endphp
                                    <div class="relative group">
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($thumbnailPath) }}" alt="Thumbnail {{ $index + 1 }}" class="w-full h-20 object-cover rounded border border-gray-200">
                                        <label class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 rounded flex items-center justify-center cursor-pointer transition-all">
                                            <input type="checkbox" name="removed_thumbnails[]" value="{{ basename($thumbnailPath) }}" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <span class="ml-2 text-xs text-white hidden group-hover:block">Remove</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('sell-workspace-modal').classList.add('hidden')" 
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            {{ __('messages.cancel') }}
                        </button>
                        <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            {{ __('messages.list_for_sale') }}
                        </button>
                    </div>
                </div>
            </form>
            <script>
                document.getElementById('sale_mode').addEventListener('change', function() {
                    const scarcityOptions = document.getElementById('scarcity-options');
                    if (this.value === 'scarcity') {
                        scarcityOptions.style.display = 'block';
                    } else {
                        scarcityOptions.style.display = 'none';
                    }
                });
            </script>
        </div>
    </div>
</div>

<!-- Plugins Section (Coming Soon) -->
<div class="fixed bottom-4 right-4 z-40" x-data="{ open: false }">
    <button @click="open = !open" 
            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-full shadow-lg flex items-center gap-2 transition-all">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <span class="text-sm font-medium">{{ __('messages.plugins') }}</span>
    </button>
    
    <div x-show="open" @click.away="open = false" x-transition
         class="absolute bottom-full right-0 mb-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 p-4">
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-purple-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ __('messages.plugins') }}</h4>
            <p class="text-sm text-gray-600 mb-4">{{ __('messages.plugins_coming_soon') }}</p>
            <div class="bg-purple-50 rounded-lg p-3">
                <p class="text-xs text-purple-800 font-medium">{{ __('messages.plugins_description') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

