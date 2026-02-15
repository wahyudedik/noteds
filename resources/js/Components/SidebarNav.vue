<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const supportMenuExpanded = ref(false);
const accountMenuExpanded = ref(false);

// Logout handled via Inertia Link with POST to include CSRF automatically

const normalizePath = (url) => {
    const raw = String(url ?? '');
    const noQuery = raw.split('?')[0];
    return new URL(noQuery, 'http://noteds.local').pathname;
};

const navItems = [
    {
        name: 'Home',
        route: 'home',
        icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        active: () => {
            const url = page.url;
            // Only match /home or / (root), not /posts/{uuid}
            // Match exactly /home or /, or /home with query params like /home?page=2
            return url === '/' || url === '/home' || (url.startsWith('/home') && !url.match(/^\/posts\/[0-9a-f-]+/i));
        },
    },
    // {
    //     name: 'Search',
    //     route: 'search.index',
    //     icon: 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
    //     active: () => page.url.startsWith('/search'),
    //     requiresAuth: true,
    // },
    {
        name: 'Dashboard',
        route: 'dashboard',
        icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
        active: () => page.url.startsWith('/dashboard'),
        requiresAuth: true,
    },
    {
        name: 'Explorer',
        route: 'explorer.index',
        icon: 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        active: () => page.url.startsWith('/explorer'),
    },
    {
        name: 'Plugins',
        route: 'plugins.index',
        icon: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25',
        active: () => page.url.startsWith('/plugins'),
        requiresAuth: true,
    },
    {
        name: 'Support',
        route: 'support.help-center',
        icon: 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
        active: () => page.url.startsWith('/support'),
        requiresAuth: true,
        hasSubmenu: true,
    },
    {
        name: 'Account',
        route: 'profile.show',
        routeParams: () => page.props.auth?.user?.id,
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        active: () => page.url.startsWith('/profile') || page.url.startsWith('/settings') || page.url.startsWith('/bookmarks') || page.url.startsWith('/notifications') || page.url.startsWith('/gamification'),
        requiresAuth: true,
        hasSubmenu: true,
    },
];

// Add admin menu items if user is admin
const adminNavItems = [
    {
        name: 'Admin Dashboard',
        route: 'admin.dashboard',
        icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        active: () => page.url.startsWith('/admin/dashboard'),
    },
    {
        name: 'Marketplace Orders',
        route: 'admin.marketplace.orders.index',
        icon: 'M3 3h18v4H3V3zm0 6h18v4H3V9zm0 6h18v4H3v-4z',
        active: () => page.url.startsWith('/admin/marketplace/orders'),
    },
    {
        name: 'Marketplace Settings',
        route: 'admin.marketplace.settings',
        icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        active: () => page.url.startsWith('/admin/marketplace/settings'),
    },
];

// Account submenu items
const accountSubmenuItems = computed(() => {
    const user = page.props.auth?.user;
    if (!user) return [];
    
    return [
        { name: 'Profile', route: 'profile.show', routeParams: user.id, icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z' },
        { name: 'Settings', route: 'settings.index', icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z' },
        { name: 'Bookmarks', route: 'bookmarks.index', icon: 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z' },
        { name: 'Gamification', route: 'gamification.overview', icon: 'M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.545a60.05 60.05 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35' },
        { name: 'Notifications', route: 'notifications.index', icon: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0' },
        { name: 'Logout', route: 'logout', method: 'post', as: 'button', icon: 'M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9' },
    ];
});

// Support submenu items
const supportSubmenuItems = computed(() => {
    return [
        { name: 'Help Center', route: 'support.help-center', icon: 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z' },
        { name: 'My Tickets', route: 'support.tickets.index', icon: 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h5.25c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z' },
        { name: 'Create Ticket', route: 'support.tickets.create', icon: 'M12 4.5v15m7.5-7.5h-15' },
    ];
});

// Filter nav items based on authentication and custom conditions
const filteredNavItems = computed(() => {
    return navItems.filter(item => {
        // If item requires auth or has routeParams, only show if user is authenticated
        if (item.requiresAuth || item.routeParams) {
            if (!page.props.auth?.user) return false;
        }
        
        // Check custom showIf condition if exists
        if (item.showIf && typeof item.showIf === 'function') {
            return item.showIf();
        }
        
        return true;
    });
});

// Auto-expand menus if on relevant pages
if (page.url.startsWith('/support')) {
    supportMenuExpanded.value = true;
}
if (page.url.startsWith('/profile') || page.url.startsWith('/settings') || page.url.startsWith('/bookmarks') || page.url.startsWith('/notifications') || page.url.startsWith('/gamification')) {
    accountMenuExpanded.value = true;
}
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
            <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
                <template v-for="item in filteredNavItems" :key="item.name">
                    <!-- Support menu with submenu -->
                    <div v-if="item.hasSubmenu && item.name === 'Support'" class="space-y-1">
                        <button
                            @click="supportMenuExpanded = !supportMenuExpanded"
                            :class="[
                                'group w-full flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                item.active()
                                    ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                                    : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                            ]"
                        >
                            <div class="flex items-center gap-3">
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
                            </div>
                            <svg
                                class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': supportMenuExpanded }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="supportMenuExpanded" class="ml-4 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                            <Link
                                v-for="subItem in supportSubmenuItems"
                                :key="subItem.name"
                                :href="route(subItem.route)"
                                :class="[
                                    'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                    (() => {
                                        try {
                                            const routePath = normalizePath(route(subItem.route));
                                            return page.url === routePath || page.url.startsWith(`${routePath}/`);
                                        } catch {
                                            return page.url.includes(subItem.route.replace('.', '/'));
                                        }
                                    })()
                                        ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700',
                                ]"
                            >
                                <svg
                                    class="h-5 w-5 flex-shrink-0"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        :d="subItem.icon"
                                    />
                                </svg>
                                <span>{{ subItem.name }}</span>
                            </Link>
                        </div>
                    </div>
                    <!-- Account menu with submenu -->
                    <div v-else-if="item.hasSubmenu && item.name === 'Account'" class="space-y-1">
                        <button
                            @click="accountMenuExpanded = !accountMenuExpanded"
                            :class="[
                                'group w-full flex items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                                item.active()
                                    ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                                    : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700',
                            ]"
                        >
                            <div class="flex items-center gap-3">
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
                            </div>
                            <svg
                                class="h-4 w-4 transition-transform"
                                :class="{ 'rotate-180': accountMenuExpanded }"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="accountMenuExpanded" class="ml-4 space-y-1 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                            <template v-for="subItem in accountSubmenuItems" :key="subItem.name">
                                <button
                                    v-if="subItem.route === 'logout'"
                                    type="button"
                                    class="group w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="subItem.icon" />
                                    </svg>
                                    <Link :href="route('logout')" method="post" as="span" class="flex-1 text-left">
                                        {{ subItem.name }}
                                    </Link>
                                </button>
                                <Link
                                    v-else
                                    :href="subItem.routeParams ? route(subItem.route, typeof subItem.routeParams === 'function' ? subItem.routeParams() : subItem.routeParams) : route(subItem.route)"
                                    :class="[
                                        'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition',
                                        (() => {
                                            const params = subItem.routeParams
                                                ? (typeof subItem.routeParams === 'function' ? subItem.routeParams() : subItem.routeParams)
                                                : undefined;
                                            try {
                                                const routePath = normalizePath(params ? route(subItem.route, params) : route(subItem.route));
                                                return page.url === routePath || page.url.startsWith(`${routePath}/`);
                                            } catch {
                                                return page.url.includes(subItem.route.replace('.', '/'));
                                            }
                                        })()
                                            ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400'
                                            : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700',
                                    ]"
                                >
                                    <svg
                                        class="h-5 w-5 flex-shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            :d="subItem.icon"
                                        />
                                    </svg>
                                    <span>{{ subItem.name }}</span>
                                </Link>
                            </template>
                        </div>
                    </div>
                    <!-- Regular menu item -->
                    <Link
                        v-else
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
                </template>

                <!-- Admin Section -->
                <div v-if="page.props.auth?.user?.role === 'admin'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Admin</p>
                    <Link
                        v-for="item in adminNavItems"
                        :key="item.name"
                        :href="route(item.route)"
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
                </div>
            </nav>

            <!-- User Profile Card -->
            <div v-if="page.props.auth?.user" class="border-t border-gray-200 p-4 dark:border-gray-700">
                <Link
                    :href="route('profile.show', page.props.auth.user.id)"
                    class="flex items-center gap-3 rounded-lg p-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                >
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold overflow-hidden flex-shrink-0">
                        <img
                            v-if="page.props.auth.user.avatar_url"
                            :src="page.props.auth.user.avatar_url"
                            :alt="page.props.auth.user.business_name || page.props.auth.user.name"
                            class="w-full h-full object-cover"
                        />
                        <span v-else>
                            {{ (page.props.auth.user.business_name || page.props.auth.user.name || 'U').charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                            {{ page.props.auth.user.business_name || page.props.auth.user.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ page.props.auth.user.email }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>
    </aside>
</template>

