<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CreatePostModal from '@/Components/CreatePostModal.vue';

const page = usePage();
const showModal = ref(false);

const openModal = () => {
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};
</script>

<template>
    <div v-if="page.props.auth?.user">
        <!-- Floating Action Button -->
        <button
            @click="openModal"
            class="fixed bottom-6 z-40 h-14 w-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-110 transition-all duration-200 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-8 md:bottom-8"
            aria-label="Create Post"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

        <!-- Create Post Modal -->
        <CreatePostModal :show="showModal" @close="closeModal" />
    </div>
</template>

