<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    rating: {
        type: [Number, String],
        default: 0,
    },
    reviewCount: {
        type: Number,
        default: 0,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    showBreakdown: {
        type: Boolean,
        default: false,
    },
    ratingBreakdown: {
        type: Object,
        default: null,
    },
    sellerId: {
        type: String,
        default: null,
    },
    linkToDetails: {
        type: Boolean,
        default: false,
    },
});

const sizeClasses = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base',
};

const starSizes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5',
};

const numericRating = computed(() => {
    const rating = parseFloat(props.rating);
    return isNaN(rating) ? 0 : rating;
});

const renderStars = (rating) => {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < 5; i++) {
        if (i < fullStars) {
            stars.push('full');
        } else if (i === fullStars && hasHalfStar) {
            stars.push('half');
        } else {
            stars.push('empty');
        }
    }
    return stars;
};

const tooltipText = computed(() => {
    if (!props.showBreakdown || !props.ratingBreakdown) return '';
    const breakdown = props.ratingBreakdown;
    return `Review: ${breakdown.review_rating?.toFixed(1) || 'N/A'}, Fulfillment: ${breakdown.fulfillment_rating?.toFixed(1) || 'N/A'}, Response: ${breakdown.response_rating?.toFixed(1) || 'N/A'}`;
});
</script>

<template>
    <div :class="['inline-flex items-center gap-1', sizeClasses[size]]">
        <div class="flex items-center">
            <svg
                v-for="(starType, index) in renderStars(numericRating)"
                :key="index"
                :class="[starSizes[size], starType === 'full' ? 'text-yellow-400' : starType === 'half' ? 'text-yellow-300' : 'text-gray-300']"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path
                    v-if="starType === 'full'"
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                />
                <path
                    v-else-if="starType === 'half'"
                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545L10 15z"
                    clip-path="inset(0 50% 0 0)"
                />
                <path
                    v-else
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1"
                />
            </svg>
        </div>
        <span class="font-medium text-gray-900 dark:text-white">
            {{ numericRating.toFixed(1) }}
        </span>
        <span v-if="reviewCount > 0" class="text-gray-500 dark:text-gray-400">
            ({{ reviewCount }})
        </span>
        <Link
            v-if="linkToDetails && sellerId"
            :href="route('marketplace.sellers.rating', sellerId)"
            class="ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            :title="tooltipText"
        >
            Details
        </Link>
    </div>
</template>

