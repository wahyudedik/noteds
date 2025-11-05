@php
    $isActive = request()->has('folder') && request()->folder == $folder->id;
    $hasChildren = $folder->relationLoaded('children') ? $folder->children->count() > 0 : false;
@endphp

<div class="folder-tree-item" x-data="{ expanded: {{ $isActive ? 'true' : 'false' }} }">
    <div class="flex items-center gap-1">
        @if($hasChildren)
            <button @click="expanded = !expanded" class="p-1 hover:bg-gray-100 rounded">
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <div class="w-6"></div>
        @endif
        
        <a href="{{ route('workspaces.show', ['workspace' => $workspace->id, 'folder' => $folder->id]) }}" 
           class="flex items-center gap-2 flex-1 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors {{ $isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}"
           style="padding-left: {{ ($level * 16) + 12 }}px;">
            @if($folder->color)
                <div class="w-4 h-4 rounded flex-shrink-0" style="background-color: {{ $folder->color }};"></div>
            @else
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            @endif
            <span class="text-sm font-medium truncate flex-1">{{ $folder->name }}</span>
            <span class="text-xs text-gray-500 ml-2 flex-shrink-0">{{ $folder->notes()->count() }}</span>
        </a>
    </div>
    
    @if($hasChildren)
        <div x-show="expanded" x-transition class="mt-1 space-y-1 ml-6">
            @foreach($folder->children as $child)
                @include('workspaces.partials.folder-tree-item', ['folder' => $child, 'workspace' => $workspace, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>

