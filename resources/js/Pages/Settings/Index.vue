<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Account from './Account.vue';
import Privacy from './Privacy.vue';
import Notifications from './Notifications.vue';
import Security from './Security.vue';

const activeTab = ref('account');

const tabs = [
    { id: 'account', label: 'Account', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { id: 'privacy', label: 'Privacy', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
    { id: 'notifications', label: 'Notifications', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { id: 'security', label: 'Security', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
];

const setActiveTab = (tabId) => {
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
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="-mb-px flex space-x-8 px-6">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="setActiveTab(tab.id)"
                                :class="[
                                    'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition',
                                    activeTab === tab.id
                                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                                ]"
                            >
                                <svg
                                    class="-ml-0.5 mr-2 h-5 w-5"
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
                        <Notifications v-else-if="activeTab === 'notifications'" />
                        <Security v-else-if="activeTab === 'security'" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

