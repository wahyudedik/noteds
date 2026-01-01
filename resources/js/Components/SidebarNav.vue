<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

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
        name: 'Marketplace',
        route: 'marketplace.index',
        icon: 'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z',
        active: () => page.url.startsWith('/marketplace'),
    },
    {
        name: 'Explorer',
        route: 'explorer.index',
        icon: 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        active: () => page.url.startsWith('/explorer'),
    },
    {
        name: 'Clipper',
        route: 'clipper.campaigns.index',
        icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        active: () => page.url.startsWith('/clipper'),
        requiresAuth: true,
        showIf: () => {
            const user = page.props.auth?.user;
            return user?.clipper_role === 'brand' || user?.clipper_role === 'clipper' || user?.role === 'brand' || user?.role === 'clipper';
        },
    },
    {
        name: 'Bookmarks',
        route: 'bookmarks.index',
        icon: 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
        active: () => page.url.startsWith('/bookmarks'),
        requiresAuth: true,
    },
    {
        name: 'Notifications',
        route: 'notifications.index',
        icon: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
        active: () => page.url.startsWith('/notifications'),
        requiresAuth: true,
    },
    {
        name: 'Settings',
        route: 'settings.index',
        icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z',
        active: () => page.url.startsWith('/settings'),
        requiresAuth: true,
    },
    {
        name: 'Profile',
        route: 'profile.show',
        routeParams: () => page.props.auth?.user?.id,
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        active: () => page.url.startsWith('/profile'),
    },
];

// Add admin menu items if user is admin
const adminNavItems = [
    {
        name: 'Admin Dashboard',
        route: 'admin.dashboard',
        icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
        active: () => page.url.startsWith('/admin'),
    },
];

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
                <Link
                    v-for="item in filteredNavItems"
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

