<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const navItems = [
    {
        name: 'Home',
        route: 'home',
        icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        active: () => page.url === '/' || page.url.startsWith('/home') || page.url.startsWith('/posts'),
    },
    {
        name: 'Dashboard',
        route: 'dashboard',
        icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
        active: () => page.url.startsWith('/dashboard'),
    },
    {
        name: 'Profile',
        route: 'profile.show',
        routeParams: () => page.props.auth?.user?.id,
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        active: () => page.url.startsWith('/profile'),
    },
];
</script>

<template>
    <aside class="fixed left-0 top-0 z-40 h-screen w-64 border-r border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 transition-transform lg:translate-x-0" :class="{ '-translate-x-full': !props.show, 'translate-x-0': props.show }">
        <div class="flex h-full flex-col">
            <!-- Logo -->
            <div class="flex h-16 items-center border-b border-gray-200 px-6 dark:border-gray-700">
                <Link :href="route('home')" class="flex items-center gap-2">
                    <ApplicationLogo class="h-8 w-auto" />
                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">Noteds</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 space-y-1 px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.routeParams ? route(item.route, item.routeParams()) : route(item.route)"
                    :class="[
                        'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                        item.active()
                            ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                            : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                >
                    <svg
                        class="h-6 w-6 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            :d="item.icon"
                        />
                    </svg>
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <!-- User Profile Card -->
            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                <Link
                    :href="route('profile.show', page.props.auth?.user?.id)"
                    class="flex items-center gap-3 rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                >
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                        {{ (page.props.auth?.user?.business_name || page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                            {{ page.props.auth?.user?.business_name || page.props.auth?.user?.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ page.props.auth?.user?.email }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>
    </aside>
</template>

