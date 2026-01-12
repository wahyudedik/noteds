<script setup>
import { computed } from 'vue';

const props = defineProps({
    seller: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    showDate: {
        type: Boolean,
        default: false,
    },
    verifiedAt: {
        type: String,
        default: null,
    },
});

const sizeClasses = {
    sm: 'text-xs px-2 py-0.5',
    md: 'text-sm px-2.5 py-1',
    lg: 'text-base px-3 py-1.5',
};

const iconSizes = {
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-5 h-5',
};

const isVerified = computed(() => props.seller?.is_verified_seller || false);

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const tooltipText = computed(() => {
    if (!isVerified.value) return '';
    const date = props.verifiedAt || props.seller?.seller_verification?.verified_at;
    return date ? `Verified on ${formatDate(date)}` : 'Verified Seller';
});
</script>

<template>
    <span
        v-if="isVerified"
        :class="[
            'inline-flex items-center gap-1 rounded-full font-medium bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            sizeClasses[size],
        ]"
        :title="tooltipText"
    >
        <svg
            :class="iconSizes[size]"
            fill="currentColor"
            viewBox="0 0 20 20"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill-rule="evenodd"
                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
            />
        </svg>
        <span>Verified</span>
        <span v-if="showDate && verifiedAt" class="text-xs opacity-75">
            ({{ formatDate(verifiedAt) }})
        </span>
    </span>
</template>

