<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import RecentActivitiesWidget from '@/Components/Admin/RecentActivitiesWidget.vue';
import AnalyticsCharts from '@/Components/Admin/AnalyticsCharts.vue';
import { ref, onMounted } from 'vue';

const a11y = ref({ score: 0, top_rules: [], reports: 0 });
const loadA11y = async () => {
    const res = await fetch(route('admin.a11y.summary'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
    if (res.ok) a11y.value = await res.json();
};
onMounted(loadA11y);

const props = defineProps({
    stats: Object,
    recent_reports: Array,
    recent_users: Array,
    recent_activities: Array,
    analytics: Object,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Admin Dashboard
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <div v-if="a11y.score && a11y.score < 80" class="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-yellow-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold">Accessibility Alert</p>
                            <p class="text-sm">Score: {{ a11y.score }}. Perlu perbaikan aksesibilitas. Lihat laporan untuk detail.</p>
                        </div>
                        <Link :href="route('admin.a11y.reports.page')" class="px-3 py-2 bg-yellow-600 text-white rounded">Lihat Laporan</Link>
                    </div>
                </div>
                <!-- Enhanced Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Users -->
                    <Link
                        :href="route('admin.users.index')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Users</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ stats?.total_users || 0 }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            New today: {{ stats?.new_users_today || 0 }} | 
                            Banned: {{ stats?.banned_users || 0 }}
                        </div>
                    </Link>

                    <!-- Accessibility Summary -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Accessibility Score</h3>
                        <p class="text-3xl font-bold" :class="a11y.score >= 90 ? 'text-green-600' : (a11y.score >= 75 ? 'text-yellow-600' : 'text-red-600')">
                            {{ a11y.score }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Reports analyzed: {{ a11y.reports }}
                        </div>
                        <div class="mt-3">
                            <div class="text-xs font-semibold">Top Rules</div>
                            <ul class="text-xs text-gray-600 dark:text-gray-300 list-disc ml-4">
                                <li v-for="r in a11y.top_rules" :key="r.rule">{{ r.rule }} ({{ r.count }})</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Reports -->
                    <Link
                        :href="route('admin.reports.index')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Pending Reports</h3>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats?.pending_reports || 0 }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Posts: {{ stats?.pending_post_reports || 0 }} | 
                            Comments: {{ stats?.pending_comment_reports || 0 }} | 
                            Users: {{ stats?.pending_user_reports || 0 }}
                        </div>
                    </Link>

                    <!-- Posts -->
                    <Link
                        :href="route('admin.posts.index')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Posts for Moderation</h3>
                        <p class="text-3xl font-bold text-orange-600">
                            {{ stats?.pending_posts_moderation || 0 }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Total: {{ stats?.total_posts || 0 }} | 
                            Active: {{ stats?.active_posts || 0 }}
                        </div>
                    </Link>
                </div>

                <!-- Analytics Charts -->
                <AnalyticsCharts
                    v-if="analytics"
                    :user-growth-trends="analytics.user_growth_trends || { labels: [], data: [] }"
                    :post-trends="analytics.post_trends || { labels: [], data: [] }"
                    period="monthly"
                />

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Reports -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Reports</h3>
                            <Link
                                :href="route('admin.reports.index')"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                            >
                                View All
                            </Link>
                        </div>
                        <div v-if="recent_reports && recent_reports.length > 0" class="space-y-3">
                            <div
                                v-for="report in recent_reports.slice(0, 5)"
                                :key="report.id"
                                class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div class="flex items-center justify-between mb-1">
                                    <Link
                                        :href="route('admin.reports.show', report.id)"
                                        class="text-sm font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        Report #{{ report.id.slice(0, 8) }}
                                    </Link>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 text-xs rounded-full',
                                            report.status === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                            report.status === 'resolved' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                            'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ report.status }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ report.reason }} • {{ report.user?.name || 'Unknown' }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                            No recent reports
                        </div>
                    </div>

                    <!-- Recent Users -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Users</h3>
                            <Link
                                :href="route('admin.users.index')"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                            >
                                View All
                            </Link>
                        </div>
                        <div v-if="recent_users && recent_users.length > 0" class="space-y-3">
                            <div
                                v-for="user in recent_users.slice(0, 5)"
                                :key="user.id"
                                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                            >
                                <div>
                                    <Link
                                        :href="route('admin.users.show', user.id)"
                                        class="font-medium text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                    >
                                        {{ user.name }}
                                    </Link>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ user.email }}
                                    </div>
                                </div>
                                <span
                                    v-if="user.is_banned"
                                    class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                >
                                    Banned
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                            No recent users
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <RecentActivitiesWidget
                    v-if="recent_activities"
                    :activities="recent_activities"
                />

                <!-- Management Links -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">FAQ Management</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Manage frequently asked questions and answers
                                </p>
                            </div>
                            <Link
                                :href="route('admin.faqs.index')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                Manage FAQs
                            </Link>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Documentation Management</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Manage platform documentation and guides
                                </p>
                            </div>
                            <Link
                                :href="route('admin.documentations.index')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                Manage Docs
                            </Link>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Moderation</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Review reports and posts that require admin attention
                                </p>
                            </div>
                            <Link
                                :href="route('admin.reports.index')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                Open Reports
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

