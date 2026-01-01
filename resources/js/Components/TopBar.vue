<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationBell from '@/Components/Notifications/NotificationBell.vue';
import SearchBar from '@/Components/Search/SearchBar.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const showingProfileDropdown = ref(false);

const notifications = computed(() => {
    const notificationsData = page.props.notifications || [];
    // Handle pagination object - extract data array if it's a pagination object
    if (notificationsData && typeof notificationsData === 'object' && 'data' in notificationsData) {
        return notificationsData.data || [];
    }
    // Return as-is if it's already an array
    return Array.isArray(notificationsData) ? notificationsData : [];
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
                <SearchBar placeholder="Search posts, users..." />
            </div>

            <!-- Search Bar (Mobile) -->
            <div class="flex-1 max-w-xl lg:hidden ml-4">
                <SearchBar placeholder="Search..." />
            </div>

            <!-- Right Side Actions -->
            <div v-if="page.props.auth?.user" class="flex items-center gap-4">
                <!-- Notification Bell -->
                <NotificationBell :notifications="notifications" />
                
                <!-- Desktop Profile Dropdown -->
                <div class="hidden lg:block">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                <div class="h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm overflow-hidden flex-shrink-0">
                                    <img
                                        v-if="page.props.auth?.user?.avatar_url"
                                        :src="page.props.auth.user.avatar_url"
                                        :alt="page.props.auth.user.business_name || page.props.auth.user.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else>
                                        {{ (page.props.auth?.user?.business_name || page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                                    </span>
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
                            <DropdownLink 
                                v-if="page.props.auth?.user?.clipper_role === 'brand' || page.props.auth?.user?.clipper_role === 'clipper' || page.props.auth?.user?.role === 'brand' || page.props.auth?.user?.role === 'clipper'"
                                :href="route('clipper.campaigns.index')"
                            >
                                Clipper
                            </DropdownLink>
                            <DropdownLink :href="route('marketplace.index')">
                                Marketplace
                            </DropdownLink>
                            <DropdownLink :href="route('settings.index')">
                                Settings
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
                    class="lg:hidden h-9 w-9 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-sm overflow-hidden flex-shrink-0"
                >
                    <img
                        v-if="page.props.auth?.user?.avatar_url"
                        :src="page.props.auth.user.avatar_url"
                        :alt="page.props.auth.user.business_name || page.props.auth.user.name"
                        class="w-full h-full object-cover"
                    />
                    <span v-else>
                        {{ (page.props.auth?.user?.business_name || page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                    </span>
                </Link>
            </div>
        </div>
    </header>
</template>

