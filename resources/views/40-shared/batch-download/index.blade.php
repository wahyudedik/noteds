@extends('40-shared.layouts.app')

@section('title', __('messages.batch_download'))

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.batch_download') }}</h1>
                <p class="text-gray-600">Select multiple purchased notes to download in one zip.</p>
            </div>

            <div x-data="batchDownload()" x-init="init()" class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">Purchased Notes</h2>
                        <span class="text-xs text-gray-500">Select files to include</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="toggleSelectAll()" type="button"
                            class="px-3 py-1.5 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                            <span x-show="!allSelected">Select All</span>
                            <span x-show="allSelected">Unselect All</span>
                        </button>
                        <span class="text-xs text-gray-500" x-text="`${selectedIds.length} selected`"></span>
                    </div>
                </div>

                <div class="border rounded-lg divide-y max-h-80 overflow-y-auto">
                    @forelse ($purchasedNotes ?? [] as $note)
                        @php
                            $attachments = $note->attachments ?? [];
                            $attachmentCount = is_array($attachments) ? count($attachments) : 0;
                            $totalSize = 0;
                            if ($attachmentCount > 0) {
                                foreach ($attachments as $attachment) {
                                    $path = is_array($attachment) ? $attachment['path'] ?? null : $attachment;
                                    if ($path && Storage::disk('private')->exists($path)) {
                                        $totalSize += Storage::disk('private')->size($path);
                                    }
                                }
                            }
                            $sizeReadable = $totalSize > 0 ? number_format($totalSize / 1024 / 1024, 1) . ' MB' : 'N/A';
                        @endphp
                        <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" class="rounded" value="{{ $note->id }}"
                                @change="toggleItem($event)" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $note->title }}</p>
                                <p class="text-xs text-gray-500">{{ $attachmentCount }} file(s) · {{ $sizeReadable }}</p>
                            </div>
                            <span class="text-xs text-gray-400">{{ $note->ecosystem_category ?? 'N/A' }}</span>
                        </label>
                    @empty
                        <div class="px-4 py-8 text-center text-gray-500">
                            <p class="text-sm">Belum ada catatan yang dibeli dengan lampiran.</p>
                            <a href="{{ route('ecosystem.index') }}"
                                class="text-blue-600 hover:underline text-xs mt-2 inline-block">Jelajahi Catatan</a>
                        </div>
                    @endforelse
                </div>

                <form action="{{ route('batch-download.download') }}" method="POST" class="mt-6"
                    @submit.prevent="prepareDownload($event)">
                    @csrf
                    <template x-for="(id, index) in selectedIds" :key="index">
                        <input type="hidden" name="note_ids[]" :value="id" />
                    </template>
                    @php
                        $routeExists = \Illuminate\Support\Facades\Route::has('batch-download.download');
                    @endphp
                    <div class="flex items-center gap-3">
                        <button :disabled="selectedIds.length === 0 || !{{ $routeExists ? 'true' : 'false' }}"
                            class="px-4 py-2 rounded text-white"
                            :class="selectedIds.length === 0 || !{{ $routeExists ? 'true' : 'false' }} ?
                                'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'">
                            Prepare Download
                        </button>
                        @unless ($routeExists)
                            <span class="text-xs text-yellow-700 bg-yellow-100 px-2 py-1 rounded">Route not ready</span>
                        @endunless
                    </div>

                    <!-- Progress mock -->
                    <div x-show="progress.visible" class="mt-4">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-700">Preparing zip...</span>
                            <span class="text-xs text-gray-500" x-text="`${progress.value}%`"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded" :style="`width: ${progress.value}%`"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function batchDownload() {
            return {
                selectedIds: [],
                allSelected: false,
                progress: {
                    visible: false,
                    value: 0
                },
                init() {
                    this.selectedIds = [];
                    this.allSelected = false;
                },
                toggleItem(e) {
                    const id = e.target.value;
                    if (e.target.checked) {
                        if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
                    } else {
                        this.selectedIds = this.selectedIds.filter(v => v !== id);
                        this.allSelected = false;
                    }
                },
                toggleSelectAll() {
                    const boxes = Array.from(document.querySelectorAll('input[type="checkbox"]'));
                    this.allSelected = !this.allSelected;
                    this.selectedIds = [];
                    boxes.forEach(b => {
                        b.checked = this.allSelected;
                        if (this.allSelected && b.value) this.selectedIds.push(b.value);
                    });
                },
                async prepareDownload(ev) {
                    // Front-end progress mock; replace with real request later
                    if (this.selectedIds.length === 0) return;
                    this.progress.visible = true;
                    this.progress.value = 0;
                    for (let i = 0; i <= 100; i += 10) {
                        await new Promise(r => setTimeout(r, 150));
                        this.progress.value = i;
                    }
                    // Submit if route exists and enabled
                    const canSubmit =
                        {{ \Illuminate\Support\Facades\Route::has('batch-download.download') ? 'true' : 'false' }};
                    if (canSubmit) ev.target.submit();
                }
            }
        }
    </script>
@endpush
