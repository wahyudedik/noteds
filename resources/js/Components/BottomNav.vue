<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const navItems = [
    {
        name: 'Home',
        route: 'home',
        icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        active: () => page.url === '/' || page.url.startsWith('/home') || page.url.startsWith('/posts'),
    },
    {
        name: 'Explorer',
        route: 'explorer.index',
        icon: 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        active: () => page.url.startsWith('/explorer'),
    },
    {
        name: 'Profile',
        route: 'profile.show',
        routeParams: () => page.props.auth?.user?.id,
        icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
        active: () => page.url.startsWith('/profile'),
    },
];
const filteredNavItems = navItems.filter(i => {
  if (i.name === 'Profile') return !!page.props.auth?.user
  return true
})
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 z-40 border-t border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800 lg:hidden">
        <div class="grid h-16 grid-cols-3 gap-1">
            <Link
                v-for="item in filteredNavItems"
                :key="item.name"
                :href="item.routeParams ? route(item.route, item.routeParams()) : route(item.route)"
                :class="[
                    'flex flex-col items-center justify-center gap-1 text-xs font-medium transition',
                    item.active()
                        ? 'text-indigo-600 dark:text-indigo-400'
                        : 'text-gray-600 dark:text-gray-400',
                ]"
            >
                <svg
                    class="h-6 w-6"
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
</template>

