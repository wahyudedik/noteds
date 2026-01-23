<script setup>
import { ref } from 'vue';
import SidebarNav from '@/Components/SidebarNav.vue';
import TopBar from '@/Components/TopBar.vue';
import BottomNav from '@/Components/BottomNav.vue';
import FloatingActionButton from '@/Components/FloatingActionButton.vue';
import { announce } from '@/Utils/accessibility';
import CookieConsent from '@/Components/Common/CookieConsent.vue';

const sidebarOpen = ref(false);

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 bg-indigo-600 text-white px-3 py-2 rounded">
            Skip to content
        </a>
        <!-- Sidebar (Desktop) / Drawer (Mobile) -->
        <SidebarNav :show="sidebarOpen" />

        <!-- Overlay for mobile sidebar -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
        ></div>

        <!-- Main Content Area -->
        <div class="lg:pl-64">
            <!-- Top Bar -->
            <TopBar @toggle-sidebar="toggleSidebar" />

            <!-- Page Heading (if needed) -->
            <header
                v-if="$slots.header"
                class="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="px-4 py-4 lg:px-6">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" class="pb-16 lg:pb-0" tabindex="-1">
                <slot />
            </main>
        </div>

        <!-- Bottom Navigation (Mobile only) -->
        <BottomNav />

        <!-- ARIA live region -->
        <div id="aria-live" aria-live="polite" class="sr-only"></div>
        <CookieConsent />
        <!-- Floating Action Button -->
        <FloatingActionButton />
    </div>
</template>
