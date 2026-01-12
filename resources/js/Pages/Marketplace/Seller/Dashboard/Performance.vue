<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RatingDisplay from '@/Components/Marketplace/Seller/RatingDisplay.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    ratingBreakdown: {
        type: Object,
        default: () => ({}),
    },
    fulfillmentMetrics: {
        type: Object,
        default: () => ({}),
    },
    responseTimeMetrics: {
        type: Object,
        default: () => ({}),
    },
    ratingTrends: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Performance Metrics" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Performance Metrics
                </h2>
                <Link
                    :href="route('marketplace.seller.dashboard.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Overall Rating -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Overall Rating
                    </h3>
                    <div class="flex items-center gap-8">
                        <div class="text-center">
                            <RatingDisplay
                                :rating="ratingBreakdown.overall_rating || 0"
                                :review-count="ratingBreakdown.total_reviews || 0"
                                size="lg"
                            />
                        </div>
                        <div class="flex-1 grid grid-cols-3 gap-6">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Review Rating</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ ratingBreakdown.review_rating ? ratingBreakdown.review_rating.toFixed(1) : 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">40% weight</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Fulfillment Rating</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ ratingBreakdown.fulfillment_rating ? ratingBreakdown.fulfillment_rating.toFixed(1) : 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">35% weight</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Response Rating</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ ratingBreakdown.response_rating ? ratingBreakdown.response_rating.toFixed(1) : 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">25% weight</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fulfillment Metrics -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Fulfillment Rate
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Fulfillment Rate
                                </span>
                                <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ fulfillmentMetrics.fulfillment_rate ? fulfillmentMetrics.fulfillment_rate.toFixed(2) : '0.00' }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                                <div
                                    class="bg-indigo-600 h-4 rounded-full transition-all duration-500"
                                    :style="{ width: (fulfillmentMetrics.fulfillment_rate || 0) + '%' }"
                                ></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Completed Orders</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ fulfillmentMetrics.completed_orders || 0 }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Orders</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ fulfillmentMetrics.total_orders || 0 }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Cancelled Orders</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                                    {{ fulfillmentMetrics.cancelled_orders || 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Response Time Metrics -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Response Time
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Average Response Time</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                                {{ responseTimeMetrics.average_response_time_hours ? responseTimeMetrics.average_response_time_hours.toFixed(1) : 'N/A' }} hours
                            </p>
                        </div>
                        <div v-if="responseTimeMetrics.trend" class="h-64 flex items-center justify-center text-gray-500 dark:text-gray-400">
                            Response time trend chart placeholder
                        </div>
                    </div>
                </div>

                <!-- Performance Improvement Suggestions -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Performance Improvement Suggestions
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-if="(fulfillmentMetrics.fulfillment_rate || 0) < 90"
                            class="flex items-start gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg"
                        >
                            <svg class="w-5 h-5 text-yellow-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    Improve Fulfillment Rate
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Your fulfillment rate is below 90%. Focus on completing orders on time to improve your rating.
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="(responseTimeMetrics.average_response_time_hours || 999) > 48"
                            class="flex items-start gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg"
                        >
                            <svg class="w-5 h-5 text-yellow-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    Improve Response Time
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Try to respond to customer messages and reviews within 24 hours to improve your response rating.
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="(ratingBreakdown.overall_rating || 0) >= 4.5"
                            class="flex items-start gap-3 p-4 bg-white dark:bg-gray-800 rounded-lg"
                        >
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    Excellent Performance!
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Keep up the great work! Your high rating helps attract more customers.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

