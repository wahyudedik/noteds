<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    activeTab: {
        type: String,
        default: 'posts',
    },
    isOwnProfile: {
        type: Boolean,
        default: false,
    },
    profileUser: {
        type: Object,
        default: null,
    },
    hasBrandProfile: {
        type: Boolean,
        default: false,
    },
    hasClipperProfile: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:activeTab']);

const baseTabs = [
    { id: 'posts', label: 'Posts', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { id: 'analytics', label: 'Analytics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { id: 'about', label: 'About', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

const brandTab = { id: 'brand', label: 'Brand', icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z' };
const clipperTab = { id: 'clipper', label: 'Clipper', icon: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' };

const visibleTabs = computed(() => {
    let tabs = [...baseTabs];
    
    // Add Brand tab if user has brand profile
    if (props.hasBrandProfile) {
        tabs.push(brandTab);
    }
    
    // Add Clipper tab if user has clipper profile
    if (props.hasClipperProfile) {
        tabs.push(clipperTab);
    }
    
    // Filter analytics for non-own profile
    if (!props.isOwnProfile) {
        tabs = tabs.filter(tab => tab.id !== 'analytics');
    }
    
    return tabs;
});

const setActiveTab = (tabId) => {
    emit('update:activeTab', tabId);
};
</script>

<template>
    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
        <nav class="-mb-px flex justify-center space-x-2 sm:space-x-4 lg:space-x-8 min-w-max sm:min-w-0">
            <button
                v-for="tab in visibleTabs"
                :key="tab.id"
                @click="setActiveTab(tab.id)"
                :class="[
                    'group inline-flex items-center py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition whitespace-nowrap',
                    activeTab === tab.id
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                ]"
            >
                <svg
                    class="-ml-0.5 mr-1 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0"
                    :class="activeTab === tab.id ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500'"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icon" />
                </svg>
                <span>{{ tab.label }}</span>
            </button>
        </nav>
    </div>
</template>

