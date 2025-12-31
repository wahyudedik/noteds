<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recent_withdrawals: Array,
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
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Pending Withdrawals</h3>
                        <p class="text-3xl font-bold text-yellow-600">
                            {{ stats?.pending_withdrawals || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Users</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ stats?.total_users || 0 }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Sales</h3>
                        <p class="text-3xl font-bold text-green-600">
                            Rp {{ new Intl.NumberFormat('id-ID').format(stats?.total_sales || 0) }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Total Products</h3>
                        <p class="text-3xl font-bold text-purple-600">
                            {{ stats?.total_products || 0 }}
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">FAQ Management</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Manage frequently asked questions and answers
                                </p>
                            </div>
                            <Link
                                :href="route('admin.faqs.index')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                            >
                                Manage FAQs
                            </Link>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold mb-2">Documentation Management</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Manage platform documentation and guides
                                </p>
                            </div>
                            <Link
                                :href="route('admin.documentations.index')"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                            >
                                Manage Docs
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Recent Withdrawals -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Recent Withdrawal Requests</h3>
                        <Link
                            :href="route('admin.withdrawals.index')"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            View All
                        </Link>
                    </div>
                    <div class="space-y-4">
                        <div
                            v-for="withdrawal in recent_withdrawals"
                            :key="withdrawal.id"
                            class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div>
                                <p class="font-semibold">
                                    {{ withdrawal.user?.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(withdrawal.amount) }}
                                </p>
                            </div>
                            <Link
                                :href="route('admin.withdrawals.show', withdrawal.id)"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Review
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

