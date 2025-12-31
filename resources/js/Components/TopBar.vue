<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const showingProfileDropdown = ref(false);

const notifications = computed(() => {
    return page.props.notifications || [];
});
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="flex h-16 items-center justify-between px-4 lg:px-6">
            <!-- Mobile Menu Button -->
            <button
                @click="$emit('toggle-sidebar')"
                class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700 lg:hidden"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Search Bar (Desktop) -->
            <div class="hidden flex-1 max-w-xl lg:block lg:ml-4">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        placeholder="Search posts, users..."
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 py-2 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                    />
                </div>
            </div>

            <!-- Right Side Actions -->
            <div class="flex items-center gap-4">
                <!-- Notification Bell -->
                <NotificationBell :notifications="notifications" />
                
                <!-- Desktop Profile Dropdown -->
                <div class="hidden lg:block">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <div class="h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ (page.props.auth?.user?.business_name || page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <div class="hidden text-left xl:block">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ page.props.auth?.user?.business_name || page.props.auth?.user?.name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ page.props.auth?.user?.email }}
                                    </p>
                                </div>
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.show', page.props.auth?.user?.id)">
                                My Profile
                            </DropdownLink>
                            <DropdownLink :href="route('profile.edit')">
                                Edit Profile
                            </DropdownLink>
                            <DropdownLink :href="route('dashboard')">
                                Dashboard
                            </DropdownLink>
                            <div class="border-t border-gray-200 dark:border-gray-700"></div>
                            <DropdownLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <!-- Mobile Profile Button -->
                <Link
                    :href="route('profile.show', page.props.auth?.user?.id)"
                    class="lg:hidden h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm"
                >
                    {{ (page.props.auth?.user?.business_name || page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                </Link>
            </div>
        </div>
    </header>
</template>

