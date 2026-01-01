<script setup>
import { ref, onErrorCaptured } from 'vue';
import { router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const error = ref(null);
const retryCount = ref(0);
const maxRetries = 3;

onErrorCaptured((err, instance, info) => {
    error.value = {
        message: err.message || 'An unexpected error occurred',
        stack: err.stack,
        info,
    };
    console.error('Error caught by boundary:', err, info);
    return false; // Prevent error from propagating
});

const retry = () => {
    if (retryCount.value < maxRetries) {
        retryCount.value++;
        error.value = null;
        router.reload({ only: [] });
    }
};

const goBack = () => {
    router.visit(route('clipper.campaigns.index'));
};
</script>

<template>
    <div v-if="error" class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/20 rounded-full mb-4">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white text-center mb-2">
                Something went wrong
            </h2>
            
            <p class="text-gray-600 dark:text-gray-400 text-center mb-6">
                {{ error.message }}
            </p>

            <div class="space-y-3">
                <PrimaryButton
                    v-if="retryCount < maxRetries"
                    @click="retry"
                    class="w-full"
                >
                    Try Again ({{ retryCount }}/{{ maxRetries }})
                </PrimaryButton>
                
                <button
                    @click="goBack"
                    class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                >
                    Go to Campaigns
                </button>
            </div>

            <details class="mt-6">
                <summary class="text-sm text-gray-500 dark:text-gray-400 cursor-pointer">
                    Technical Details
                </summary>
                <pre class="mt-2 text-xs bg-gray-100 dark:bg-gray-900 p-3 rounded overflow-auto max-h-40">{{ error.stack }}</pre>
            </details>
        </div>
    </div>
    
    <slot v-else />
</template>

