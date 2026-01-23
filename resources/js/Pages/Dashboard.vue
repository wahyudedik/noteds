<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatsOverview from '@/Components/Analytics/StatsOverview.vue';
import EngagementChart from '@/Components/Analytics/EngagementChart.vue';
import TopPosts from '@/Components/Analytics/TopPosts.vue';
import ActivityTimeline from '@/Components/Analytics/ActivityTimeline.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isClipper = computed(() => user.value?.clipper_role === 'clipper' || user.value?.role === 'clipper');
const isBrand = computed(() => user.value?.clipper_role === 'brand' || user.value?.role === 'brand');
const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.is_admin === true);
const hasPendingBrandRegistration = computed(() => page.props.has_pending_brand_registration ?? false);
const hasPendingClipperRegistration = computed(() => page.props.has_pending_clipper_registration ?? false);
const showOnboarding = computed(() => !isAdmin.value && !isClipper.value && !isBrand.value && !hasPendingBrandRegistration.value && !hasPendingClipperRegistration.value);

defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    engagement_data: {
        type: Object,
        default: () => ({}),
    },
    top_posts: {
        type: Array,
        default: () => [],
    },
    recent_activities: {
        type: Array,
        default: () => [],
    },
    purpose_type_stats: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <Head title="Dashboard - Analytics" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Analytics Dashboard
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Onboarding Card for New Users -->
                <div v-if="showOnboarding" class="rounded-lg border border-indigo-200 bg-gradient-to-r from-indigo-50 to-purple-50 p-6 dark:from-indigo-900/20 dark:to-purple-900/20 dark:border-indigo-800">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                🚀 Mulai Eksplorasi Fitur Clipper & Brand
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                                Daftarkan diri Anda sebagai <strong>Clipper</strong> untuk submit konten viral dan dapatkan penghasilan, atau sebagai <strong>Brand</strong> untuk membuat campaign dan distribusikan konten Anda.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <Link
                                    :href="route('clipper.profile.create')"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors shadow-sm"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Daftar Sebagai Clipper
                                </Link>
                                <Link
                                    :href="route('clipper.brand-registration.create')"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-700 transition-colors shadow-sm"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Daftar Sebagai Brand
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Overview -->
                <StatsOverview :stats="stats" />

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <EngagementChart :data="engagement_data" />
                    <TopPosts :posts="top_posts" />
                </div>

                <!-- Activity Timeline -->
                <ActivityTimeline :activities="recent_activities" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
