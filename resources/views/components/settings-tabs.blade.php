@props(['tabs' => []])

<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Back Button & Header -->
        <div class="mb-8">
            <a href="{{ route('admin.dashboard') }}"
                class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_dashboard') }}
            </a>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.system_settings') }}</h2>
            <p class="text-gray-600 mt-1">{{ __('messages.configure_system_wide_settings') }}</p>
        </div>

        <!-- Tab Navigation -->
        <div x-data="settingsTabs()" class="mb-8">
            <!-- Sticky Tab Bar -->
            <div class="bg-white border-b border-gray-200 rounded-t-lg sticky top-0 z-10 shadow-sm">
                <div class="flex overflow-x-auto">
                    @foreach ($tabs as $key => $tab)
                        <button
                            @click="activeTab = '{{ $key }}'; localStorage.setItem('settingsActiveTab', '{{ $key }}')"
                            :class="activeTab === '{{ $key }}' ? 'border-b-2 border-blue-600 text-blue-600' :
                                'text-gray-600 hover:text-gray-900'"
                            class="px-6 py-4 font-medium text-sm transition-colors flex items-center gap-2 whitespace-nowrap border-b-2 border-transparent"
                            role="tab" :aria-selected="activeTab === '{{ $key }}'"
                            :tabindex="activeTab === '{{ $key }}' ? '0' : '-1'">
                            @if (isset($tab['icon']))
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $tab['icon'] !!}
                                </svg>
                            @endif
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Tab Content Panels -->
            <div class="bg-white rounded-b-lg shadow-sm">
                @foreach ($tabs as $key => $tab)
                    <div x-show="activeTab === '{{ $key }}'"
                        x-transition:enter="transition ease-in duration-200" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" id="panel-{{ $key }}" role="tabpanel"
                        :aria-labelledby="'tab-{{ $key }}'" class="p-6">
                        {!! $tab['content'] !!}
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function settingsTabs() {
            return {
                activeTab: localStorage.getItem('settingsActiveTab') || Object.keys(@json($tabs))[0],
                init() {
                    // Restore active tab from localStorage or set first tab
                    const savedTab = localStorage.getItem('settingsActiveTab');
                    if (savedTab && Object.keys(@json($tabs)).includes(savedTab)) {
                        this.activeTab = savedTab;
                    }
                }
            }
        }
    </script>
@endpush
