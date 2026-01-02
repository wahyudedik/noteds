<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isBrand = computed(() => user.value?.clipper_role === 'brand' || user.value?.role === 'brand');
const isClipper = computed(() => user.value?.clipper_role === 'clipper' || user.value?.role === 'clipper');

// Brand navigation tabs
const brandTabs = [
    {
        name: 'Dashboard',
        route: 'clipper.dashboard',
        icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
    },
    {
        name: 'Campaigns',
        route: 'clipper.campaigns.index',
        icon: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    },
    {
        name: 'Analytics',
        route: 'clipper.campaigns.analytics',
        icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
    },
    {
        name: 'Top Ups',
        route: 'clipper.top-ups.index',
        icon: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    },
    {
        name: 'Wallet',
        route: 'clipper.wallet.creator',
        icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
];

// Clipper navigation tabs
const clipperTabs = [
    {
        name: 'Dashboard',
        route: 'clipper.dashboard',
        icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
    },
    {
        name: 'Available Campaigns',
        route: 'clipper.campaigns.available',
        icon: 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    },
    {
        name: 'My Clips',
        route: 'clipper.clips.index',
        icon: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    },
    {
        name: 'Wallet',
        route: 'clipper.wallet.clipper',
        icon: 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
];

const tabs = computed(() => {
    if (isBrand.value) {
        return brandTabs;
    } else if (isClipper.value) {
        return clipperTabs;
    }
    return [];
});

const isActiveTab = (routeName) => {
    const currentUrl = page.url.split('?')[0].replace(/\/$/, ''); // Remove query params and trailing slash
    
    // Direct URL path mappings for each route
    switch (routeName) {
        case 'clipper.dashboard':
            return currentUrl === '/clipper/dashboard' || currentUrl === '/clipper';
            
        case 'clipper.campaigns.index':
            // Match /clipper/campaigns exactly or /clipper/campaigns/{id}
            // But exclude analytics, available, create, edit
            if (currentUrl === '/clipper/campaigns') {
                return true;
            }
            if (currentUrl.startsWith('/clipper/campaigns/')) {
                return !currentUrl.startsWith('/clipper/campaigns/analytics') &&
                       !currentUrl.startsWith('/clipper/campaigns/available') &&
                       !currentUrl.startsWith('/clipper/campaigns/create') &&
                       !currentUrl.startsWith('/clipper/campaigns/edit');
            }
            return false;
            
        case 'clipper.campaigns.analytics':
            return currentUrl.startsWith('/clipper/campaigns/analytics');
            
        case 'clipper.campaigns.available':
            return currentUrl.startsWith('/clipper/campaigns/available');
            
        case 'clipper.top-ups.index':
            // Match /clipper/top-ups exactly or /clipper/top-ups/{id}
            // But exclude create and payment
            if (currentUrl === '/clipper/top-ups') {
                return true;
            }
            if (currentUrl.startsWith('/clipper/top-ups/')) {
                return !currentUrl.startsWith('/clipper/top-ups/create') &&
                       !currentUrl.startsWith('/clipper/top-ups/payment');
            }
            return false;
            
        case 'clipper.wallet.creator':
            return currentUrl === '/clipper/wallet/creator' || 
                   currentUrl.startsWith('/clipper/wallet/creator/');
            
        case 'clipper.wallet.clipper':
            return currentUrl === '/clipper/wallet/clipper' || 
                   currentUrl.startsWith('/clipper/wallet/clipper/');
            
        case 'clipper.clips.index':
            // Match /clipper/clips exactly or /clipper/clips/{id}
            // But exclude create and edit
            if (currentUrl === '/clipper/clips') {
                return true;
            }
            if (currentUrl.startsWith('/clipper/clips/')) {
                return !currentUrl.startsWith('/clipper/clips/create') &&
                       !currentUrl.startsWith('/clipper/clips/edit');
            }
            return false;
            
        default:
            // Fallback: try to get route URL and match
            try {
                const routeUrl = route(routeName).split('?')[0].replace(/\/$/, '');
                return currentUrl === routeUrl || currentUrl.startsWith(routeUrl + '/');
            } catch (error) {
                return false;
            }
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <slot name="header" />
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <!-- Navigation Tabs -->
            <div v-if="tabs.length > 0" class="mb-4 sm:mb-6 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <nav class="-mb-px flex space-x-2 sm:space-x-4 lg:space-x-8 min-w-max sm:min-w-0">
                    <Link
                        v-for="tab in tabs"
                        :key="tab.name"
                        :href="route(tab.route)"
                        :class="[
                            'group inline-flex items-center py-3 sm:py-4 px-2 sm:px-1 border-b-2 font-medium text-xs sm:text-sm transition whitespace-nowrap',
                            isActiveTab(tab.route)
                                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                        ]"
                    >
                        <svg
                            class="-ml-0.5 mr-1 sm:mr-2 h-4 w-4 sm:h-5 sm:w-5 flex-shrink-0"
                            :class="isActiveTab(tab.route) ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 group-hover:text-gray-500'"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                :d="tab.icon"
                            />
                        </svg>
                        <span>{{ tab.name }}</span>
                    </Link>
                </nav>
            </div>

            <!-- Page Content -->
            <slot />
        </div>
    </AuthenticatedLayout>
</template>

