<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';
import ProductShare from './ProductShare.vue';

defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const showShare = ref(false);
let shareMenuTimeout = null;

const handleMouseLeave = (event) => {
    // Check if mouse is moving to share menu dropdown or wrapper
    const relatedTarget = event.relatedTarget;
    if (relatedTarget && (
        relatedTarget.closest('.share-menu-container') || 
        relatedTarget.closest('.share-menu-wrapper')
    )) {
        // Mouse is moving to share menu - don't hide yet
        return;
    }
    
    // Clear any pending timeout
    if (shareMenuTimeout) {
        clearTimeout(shareMenuTimeout);
        shareMenuTimeout = null;
    }
    
    // Add small delay to allow mouse to enter share menu
    shareMenuTimeout = setTimeout(() => {
        showShare.value = false;
        shareMenuTimeout = null;
    }, 200);
};

const handleShareMenuLeave = (event) => {
    // Check if mouse is moving back to card
    const relatedTarget = event.relatedTarget;
    if (relatedTarget && relatedTarget.closest('.group')) {
        // Mouse is moving back to card - keep menu visible
        return;
    }
    
    // Clear any pending timeout
    if (shareMenuTimeout) {
        clearTimeout(shareMenuTimeout);
        shareMenuTimeout = null;
    }
    
    // Hide menu after small delay
    shareMenuTimeout = setTimeout(() => {
        showShare.value = false;
        shareMenuTimeout = null;
    }, 200);
};

// Cleanup timeout on unmount
onUnmounted(() => {
    if (shareMenuTimeout) {
        clearTimeout(shareMenuTimeout);
        shareMenuTimeout = null;
    }
});
</script>

<template>
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-visible hover:shadow-md transition-shadow relative group"
        @mouseenter="showShare = true"
        @mouseleave="handleMouseLeave"
    >
        <!-- Share Button (appears on hover) -->
        <div
            v-if="showShare"
            class="absolute top-2 right-2 z-50 transition-opacity share-menu-wrapper"
            @mouseenter="showShare = true"
            @mouseleave="handleShareMenuLeave"
        >
            <ProductShare :product="product" :compact="true" />
        </div>

        <Link
            :href="route('marketplace.products.show', product.id)"
            class="block"
        >
            <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                <img
                    v-if="product.image_url || product.image"
                    :src="product.image_url || product.image"
                    :alt="product.name"
                    class="w-full h-40 sm:h-48 object-cover"
                    @error="$event.target.src = '/images/placeholder.png'"
                />
                <div v-else class="w-full h-40 sm:h-48 flex items-center justify-center text-gray-400 dark:text-gray-500 text-sm">
                    No Image
                </div>
            </div>
            <div class="p-3 sm:p-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 sm:mb-2 line-clamp-2">
                    {{ product.name }}
                </h3>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-2 sm:mb-3 line-clamp-2">
                    {{ product.description }}
                </p>
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1 sm:gap-0">
                    <span class="text-lg sm:text-xl font-bold text-blue-600 dark:text-blue-400">
                        Rp {{ new Intl.NumberFormat('id-ID').format(product.price) }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ product.sales_count || 0 }} sales
                    </span>
                </div>
                <div v-if="product.category" class="mt-2">
                    <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                        {{ product.category }}
                    </span>
                </div>
            </div>
        </Link>
    </div>
</template>

