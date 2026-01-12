<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import RatingDisplay from '@/Components/Marketplace/Seller/RatingDisplay.vue';
import VerifiedBadge from '@/Components/Marketplace/Seller/VerifiedBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    seller: Object,
    ratingBreakdown: Object,
    reviews: Object,
    performanceMetrics: Object,
});
</script>

<template>
    <Head :title="`Seller Rating: ${seller?.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Seller Rating: {{ seller?.name }}
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Seller Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div v-if="seller?.avatar_url || seller?.avatar" class="h-16 w-16 rounded-full overflow-hidden">
                            <img
                                :src="seller?.avatar_url || seller?.avatar"
                                :alt="seller?.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ seller?.name }}
                                </h3>
                                <VerifiedBadge :seller="seller" />
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Member since {{ new Date(seller?.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Overall Rating -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Overall Rating
                        </h3>
                    </div>
                    <div class="flex items-center gap-8">
                        <div class="text-center">
                            <RatingDisplay
                                :rating="ratingBreakdown?.overall_rating || 0"
                                :review-count="ratingBreakdown?.total_reviews || 0"
                                size="lg"
                                :show-breakdown="true"
                                :rating-breakdown="ratingBreakdown"
                            />
                        </div>
                        <div class="flex-1 grid grid-cols-3 gap-6">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Review Rating</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ ratingBreakdown?.review_rating ? ratingBreakdown.review_rating.toFixed(1) : 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">40% weight</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Fulfillment Rating</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ ratingBreakdown?.fulfillment_rating ? ratingBreakdown.fulfillment_rating.toFixed(1) : 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">35% weight</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Response Rating</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                    {{ ratingBreakdown?.response_rating ? ratingBreakdown.response_rating.toFixed(1) : 'N/A' }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">25% weight</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div v-if="performanceMetrics" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Performance Metrics
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ performanceMetrics.total_orders || 0 }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fulfillment Rate</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ performanceMetrics.fulfillment_rate ? performanceMetrics.fulfillment_rate.toFixed(1) : '0.0' }}%
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                Rp {{ new Intl.NumberFormat('id-ID').format(performanceMetrics.total_revenue || 0) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Avg Response Time</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ performanceMetrics.average_response_time_hours ? performanceMetrics.average_response_time_hours.toFixed(1) : 'N/A' }}h
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Seller Reviews
                    </h3>
                    <div v-if="reviews?.data && reviews.data.length > 0" class="space-y-4">
                        <div
                            v-for="review in reviews.data"
                            :key="review.id"
                            class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div v-if="review.buyer?.avatar_url || review.buyer?.avatar" class="h-10 w-10 rounded-full overflow-hidden">
                                        <img
                                            :src="review.buyer?.avatar_url || review.buyer?.avatar"
                                            :alt="review.buyer?.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ review.buyer?.name || 'Anonymous' }}
                                        </p>
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center">
                                                <span
                                                    v-for="i in 5"
                                                    :key="i"
                                                    class="text-yellow-400"
                                                    :class="i <= review.rating ? 'fill-current' : ''"
                                                >
                                                    ★
                                                </span>
                                            </div>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ new Date(review.created_at).toLocaleDateString() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="review.comment" class="text-gray-700 dark:text-gray-300 mt-2">
                                {{ review.comment }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No reviews yet
                    </div>

                    <!-- Pagination -->
                    <div v-if="reviews?.links" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center gap-2">
                            <Link
                                v-for="link in reviews.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-lg',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

