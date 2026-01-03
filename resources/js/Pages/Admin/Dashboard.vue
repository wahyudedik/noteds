<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ClipperSystemWidget from '@/Components/Admin/ClipperSystemWidget.vue';
import RecentActivitiesWidget from '@/Components/Admin/RecentActivitiesWidget.vue';
import QuickActionsPanel from '@/Components/Admin/QuickActionsPanel.vue';
import PendingItemsSummary from '@/Components/Admin/PendingItemsSummary.vue';
import AnalyticsCharts from '@/Components/Admin/AnalyticsCharts.vue';

const props = defineProps({
    stats: Object,
    recent_withdrawals: Array,
    recent_reports: Array,
    recent_users: Array,
    recent_activities: Array,
    analytics: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
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
                <!-- Pending Items Summary -->
                <PendingItemsSummary
                    :pending-withdrawals="{
                        clipper: stats?.pending_clipper_withdrawals || 0,
                        creator: stats?.pending_creator_withdrawals || 0,
                        marketplace: stats?.pending_marketplace_withdrawals || 0,
                        total: stats?.pending_withdrawals || 0,
                    }"
                    :pending-reports="stats?.pending_reports || 0"
                    :pending-posts="stats?.pending_posts_moderation || 0"
                    :pending-clips="stats?.pending_clips || 0"
                    :pending-campaigns="stats?.pending_campaigns || 0"
                    :pending-brand-approvals="stats?.pending_brand_approvals || 0"
                    :fraud-alerts="stats?.fraud_alerts_count || 0"
                />

                <!-- Quick Actions -->
                <QuickActionsPanel
                    :pending-withdrawals="stats?.pending_withdrawals || 0"
                    :pending-reports="stats?.pending_reports || 0"
                    :pending-posts="stats?.pending_posts_moderation || 0"
                    :fraud-alerts="stats?.fraud_alerts_count || 0"
                    :pending-brand-approvals="stats?.pending_brand_approvals || 0"
                />

                <!-- Enhanced Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Withdrawals -->
                    <Link
                        :href="route('admin.withdrawals.index')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Pending Withdrawals</h3>
                        <p class="text-3xl font-bold text-yellow-600">
                            {{ stats?.pending_withdrawals || 0 }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Clipper: {{ stats?.pending_clipper_withdrawals || 0 }} | 
                            Creator: {{ stats?.pending_creator_withdrawals || 0 }} | 
                            Market: {{ stats?.pending_marketplace_withdrawals || 0 }}
                        </div>
                    </Link>

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

                    <!-- Sales -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Sales</h3>
                        <p class="text-3xl font-bold text-green-600">
                            Rp {{ formatCurrency(stats?.total_sales || 0) }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Today: Rp {{ formatCurrency(stats?.sales_today || 0) }} | 
                            This month: Rp {{ formatCurrency(stats?.sales_this_month || 0) }}
                        </div>
                    </div>

                    <!-- Marketplace Commission -->
                    <Link
                        :href="route('admin.marketplace.settings')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Marketplace Commission</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            Rp {{ formatCurrency(stats?.marketplace_commission_total || 0) }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            This month: Rp {{ formatCurrency(stats?.marketplace_commission_this_month || 0) }} | 
                            Avg/Order: Rp {{ formatCurrency(stats?.average_commission_per_order || 0) }}
                        </div>
                    </Link>

                    <!-- Products -->
                    <Link
                        :href="route('admin.products.index')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Products</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            {{ stats?.total_products || 0 }}
                        </p>
                    </Link>

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

                    <!-- Clipper System -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Clipper System</h3>
                        <p class="text-3xl font-bold text-indigo-600">
                            {{ stats?.total_clips || 0 }}
                        </p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Pending: {{ stats?.pending_clips || 0 }} | 
                            Campaigns: {{ stats?.total_campaigns || 0 }} | 
                            Active: {{ stats?.active_campaigns || 0 }}
                        </div>
                    </div>

                    <!-- Fraud Alerts -->
                    <Link
                        v-if="stats?.fraud_alerts_count > 0"
                        :href="route('admin.clips.fraud-alerts')"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border-2 border-red-300 dark:border-red-700 p-6 hover:shadow-md transition-shadow cursor-pointer"
                    >
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Fraud Alerts</h3>
                        <p class="text-3xl font-bold text-red-600">
                            {{ stats?.fraud_alerts_count || 0 }}
                        </p>
                        <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                            Requires immediate attention
                        </div>
                    </Link>
                </div>

                <!-- Analytics Charts -->
                <AnalyticsCharts
                    v-if="analytics"
                    :user-growth-trends="analytics.user_growth_trends || { labels: [], data: [] }"
                    :sales-trends="analytics.sales_trends || { labels: [], data: [] }"
                    :post-trends="analytics.post_trends || { labels: [], data: [] }"
                    period="monthly"
                />

                <!-- Clipper System Widget -->
                <ClipperSystemWidget
                    :fraud-alerts-count="stats?.fraud_alerts_count || 0"
                    :pending-clips="stats?.pending_clips || 0"
                    :pending-campaigns="stats?.pending_campaigns || 0"
                    :pending-brand-approvals="stats?.pending_brand_approvals || 0"
                    :active-campaigns="stats?.active_campaigns || 0"
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

                <!-- Recent Withdrawals -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Withdrawal Requests</h3>
                        <Link
                            :href="route('admin.withdrawals.index')"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                        >
                            View All
                        </Link>
                    </div>
                    <div v-if="recent_withdrawals && recent_withdrawals.length > 0" class="space-y-4">
                        <div
                            v-for="withdrawal in recent_withdrawals"
                            :key="withdrawal.id"
                            class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ withdrawal.user?.name }}
                                    </p>
                                    <span
                                        v-if="withdrawal.user_type"
                                        :class="[
                                            'px-2 py-0.5 text-xs rounded-full',
                                            withdrawal.user_type === 'clipper' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                            withdrawal.user_type === 'creator' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' :
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        ]"
                                    >
                                        {{ withdrawal.user_type === 'clipper' ? 'Clipper' : withdrawal.user_type === 'creator' ? 'Creator' : 'Marketplace' }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Rp {{ formatCurrency(withdrawal.amount) }}
                                </p>
                            </div>
                            <Link
                                :href="route('admin.withdrawals.show', withdrawal.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                            >
                                Review
                            </Link>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400 text-sm">
                        No pending withdrawals
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
                                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Marketplace Settings</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Configure commission percentage and flat fee for marketplace transactions
                                </p>
                            </div>
                            <Link
                                :href="route('admin.marketplace.settings')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                Configure
                            </Link>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Clipper Settings</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Configure platform fee percentage for clipper rewards
                                </p>
                            </div>
                            <Link
                                :href="route('admin.clipper.settings')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                Configure
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

