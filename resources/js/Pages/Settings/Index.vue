<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Account from './Account.vue';
import Privacy from './Privacy.vue';
import Notifications from './Notifications.vue';
import Security from './Security.vue';
import Playback from './Playback.vue';
import Theme from './Theme.vue';
import Accessibility from './Accessibility.vue';
import PrivacyDashboard from './PrivacyDashboard.vue';
import BlockedUsers from './BlockedUsers.vue';

const activeTab = ref('account');

const tabs = [
    { id: 'account', label: 'Account', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { id: 'privacy', label: 'Privacy', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
    { id: 'privacy-dashboard', label: 'Privacy Dashboard', icon: 'M3 3h18v4H3V3zm0 6h18v12H3V9z' },
    { id: 'notifications', label: 'Notifications', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { id: 'security', label: 'Security', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { id: 'activity-log', label: 'Activity Log', icon: 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4' },
    { id: 'playback', label: 'Playback', icon: 'M14.752 11.168l-5.197-3.027A1 1 0 008 9.027v5.946a1 1 0 001.555.832l5.197-3.027a1 1 0 000-1.64z' },
    { id: 'theme', label: 'Appearance', icon: 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M12 7a5 5 0 100 10 5 5 0 000-10z' },
    { id: 'accessibility', label: 'Accessibility', icon: 'M13 16h-1v-4H8l4-8v6h3l-2 6z' },
    { id: 'blocked-users', label: 'Safety', icon: 'M18.364 5.636a9 9 0 11-12.728 12.728 9 9 0 0112.728-12.728zM6 6l12 12' },
];
const page = usePage();
const user = page.props.auth?.user || {};
const filteredTabs = computed(() => {
    const allowed = new Set([
        'account',
        'privacy',
        'notifications',
        'security',
        'playback',
        'theme',
        'accessibility',
        'blocked-users',
    ]);
    if (user.role === 'admin') {
        allowed.add('privacy-dashboard');
        allowed.add('activity-log');
    }
    return tabs.filter(t => allowed.has(t.id));
});

const setActiveTab = (tabId) => {
    if (tabId === 'activity-log') {
        // Navigate to activity log page
        router.visit(route('settings.activity-log'));
        return;
    }
    activeTab.value = tabId;
};
</script>

<template>
    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Settings
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <!-- Tabs -->
                    <div class="border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                        <nav class="-mb-px flex space-x-4 sm:space-x-6 md:space-x-8 px-4 sm:px-6 min-w-max">
                            <button
                                v-for="tab in filteredTabs"
                                :key="tab.id"
                                @click="setActiveTab(tab.id)"
                                :class="[
                                    'group inline-flex items-center py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition whitespace-nowrap flex-shrink-0',
                                    activeTab === tab.id
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                <svg
                                    class="-ml-0.5 mr-1.5 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0"
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

                    <!-- Tab Content -->
                    <div class="p-6">
                        <Account v-if="activeTab === 'account'" />
                        <Privacy v-else-if="activeTab === 'privacy'" />
                        <PrivacyDashboard v-else-if="activeTab === 'privacy-dashboard'" />
                        <Notifications v-else-if="activeTab === 'notifications'" />
                        <Security v-else-if="activeTab === 'security'" />
                        <Playback v-else-if="activeTab === 'playback'" />
                        <Theme v-else-if="activeTab === 'theme'" />
                        <Accessibility v-else-if="activeTab === 'accessibility'" />
                        <BlockedUsers v-else-if="activeTab === 'blocked-users'" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

