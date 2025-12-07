<!-- Admin Tabs Component -->
<div class="bg-white border-b border-gray-200 sticky top-0 z-40 mb-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="flex space-x-8 overflow-x-auto" role="tablist">
            @foreach($tabs as $key => $tab)
                <button
                    onclick="switchTab('{{ $key }}')"
                    role="tab"
                    aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
                    aria-controls="panel-{{ $key }}"
                    class="px-4 py-4 font-medium text-sm border-b-2 transition-colors whitespace-nowrap {{ $activeTab === $key ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300' }}">
                    @if(isset($tab['icon']))
                        <span class="inline-flex items-center gap-2">
                            {!! $tab['icon'] !!}
                            {{ $tab['label'] }}
                        </span>
                    @else
                        {{ $tab['label'] }}
                    @endif
                </button>
            @endforeach
        </div>
    </div>
</div>

<script>
    function switchTab(tabKey) {
        // Hide all panels
        @foreach($tabs as $key => $tab)
            document.getElementById('panel-{{ $key }}').style.display = 'none';
            document.querySelector('[aria-controls="panel-{{ $key }}"]').setAttribute('aria-selected', 'false');
            document.querySelector('[aria-controls="panel-{{ $key }}"]').classList.remove('border-blue-500', 'text-blue-600');
            document.querySelector('[aria-controls="panel-{{ $key }}"]').classList.add('border-transparent', 'text-gray-600');
        @endforeach

        // Show selected panel
        document.getElementById('panel-' + tabKey).style.display = 'block';
        document.querySelector('[aria-controls="panel-' + tabKey + '"]').setAttribute('aria-selected', 'true');
        document.querySelector('[aria-controls="panel-' + tabKey + '"]').classList.add('border-blue-500', 'text-blue-600');
        document.querySelector('[aria-controls="panel-' + tabKey + '"]').classList.remove('border-transparent', 'text-gray-600');

        // Store active tab in localStorage
        localStorage.setItem('settingsActiveTab', tabKey);
    }

    // Restore active tab from localStorage on page load
    window.addEventListener('DOMContentLoaded', function() {
        const savedTab = localStorage.getItem('settingsActiveTab');
        if (savedTab) {
            switchTab(savedTab);
        }
    });
</script>
