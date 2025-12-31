<script setup>
import { ref } from 'vue';
import ReportModal from './ReportModal.vue';

const props = defineProps({
    reportableType: {
        type: String,
        required: true,
        validator: (value) => ['post', 'comment', 'user'].includes(value),
    },
    reportableId: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'icon', // 'icon' or 'text'
    },
    size: {
        type: String,
        default: 'sm', // sm, md, lg
    },
});

const showModal = ref(false);

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const handleReported = () => {
    closeModal();
};
</script>

<template>
    <div>
        <button
            @click="openModal"
            :class="[
                'inline-flex items-center text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors',
                variant === 'icon' ? 'p-1' : 'px-2 py-1 rounded-md text-sm',
                size === 'sm' && variant === 'text' ? 'text-xs' : '',
                size === 'lg' && variant === 'text' ? 'text-base' : '',
            ]"
            :title="'Report ' + reportableType"
        >
            <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span v-if="variant === 'text'" class="ml-1">Report</span>
        </button>

        <ReportModal
            :show="showModal"
            :reportable-type="reportableType"
            :reportable-id="reportableId"
            @close="closeModal"
            @reported="handleReported"
        />
    </div>
</template>

