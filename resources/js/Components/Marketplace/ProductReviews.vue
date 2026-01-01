<script setup>
import { computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    reviews: {
        type: Object,
        required: true,
    },
    averageRating: {
        type: Number,
        default: 0,
    },
    reviewsCount: {
        type: Number,
        default: 0,
    },
    currentUserId: {
        type: Number,
        default: null,
    },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const deleteReview = (reviewId) => {
    if (confirm('Are you sure you want to delete this review?')) {
        router.delete(route('marketplace.reviews.destroy', reviewId), {
            preserveScroll: true,
        });
    }
};

const renderStars = (rating) => {
    const stars = [];
    for (let i = 1; i <= 5; i++) {
        stars.push(i <= rating);
    }
    return stars;
};

const ratingDistribution = computed(() => {
    const distribution = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    props.reviews.data?.forEach(review => {
        distribution[review.rating]++;
    });
    return distribution;
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Reviews</h2>
            
            <!-- Average Rating Summary -->
            <div class="flex items-center space-x-4 mb-6">
                <div class="text-center">
                    <div class="text-4xl font-bold text-gray-900 dark:text-white">
                        {{ averageRating.toFixed(1) }}
                    </div>
                    <div class="flex items-center justify-center mt-1">
                        <div class="flex">
                            <svg
                                v-for="(filled, index) in renderStars(Math.round(averageRating))"
                                :key="index"
                                :class="filled ? 'text-yellow-400' : 'text-gray-300'"
                                class="w-5 h-5"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ reviewsCount }} {{ reviewsCount === 1 ? 'review' : 'reviews' }}
                    </div>
                </div>
                
                <!-- Rating Distribution -->
                <div class="flex-1">
                    <div v-for="rating in [5, 4, 3, 2, 1]" :key="rating" class="flex items-center space-x-2 mb-1">
                        <span class="text-sm text-gray-600 dark:text-gray-400 w-8">{{ rating }}</span>
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div
                                class="bg-yellow-400 h-2 rounded-full"
                                :style="{ width: reviewsCount > 0 ? (ratingDistribution[rating] / reviewsCount * 100) + '%' : '0%' }"
                            ></div>
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400 w-8 text-right">
                            {{ ratingDistribution[rating] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div v-if="reviews.data && reviews.data.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
            <div
                v-for="review in reviews.data"
                :key="review.id"
                class="p-6"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start space-x-4 flex-1">
                        <!-- User Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                <span class="text-gray-600 dark:text-gray-300 font-semibold">
                                    {{ review.user?.name?.charAt(0).toUpperCase() || 'U' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <!-- User Name and Rating -->
                            <div class="flex items-center space-x-2 mb-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ review.user?.name || 'Anonymous' }}
                                </h3>
                                <div class="flex">
                                    <svg
                                        v-for="(filled, index) in renderStars(review.rating)"
                                        :key="index"
                                        :class="filled ? 'text-yellow-400' : 'text-gray-300'"
                                        class="w-4 h-4"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </div>
                            </div>
                            
                            <!-- Review Date -->
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                {{ formatDate(review.created_at) }}
                            </p>
                            
                            <!-- Review Comment -->
                            <p v-if="review.comment" class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ review.comment }}
                            </p>
                            <p v-else class="text-gray-500 dark:text-gray-400 italic">
                                No comment provided
                            </p>
                        </div>
                    </div>
                    
                    <!-- Edit/Delete Buttons (only for review owner) -->
                    <div v-if="currentUserId && review.user_id === currentUserId" class="flex space-x-2">
                        <button
                            @click="$emit('edit-review', review)"
                            class="p-2 text-gray-600 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors"
                            title="Edit review"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </button>
                        <button
                            @click="deleteReview(review.id)"
                            class="p-2 text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors"
                            title="Delete review"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No reviews yet</h3>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Be the first to review this product</p>
        </div>

        <!-- Pagination -->
        <div v-if="reviews.links && reviews.links.length > 3" class="p-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-center">
                <div class="flex space-x-2">
                    <Link
                        v-for="(link, index) in reviews.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="[
                            'px-4 py-2 rounded-lg border transition-colors',
                            link.active
                                ? 'bg-blue-600 text-white border-blue-600'
                                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                            !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                        ]"
                        v-html="link.label"
                    ></Link>
                </div>
            </div>
        </div>
    </div>
</template>

