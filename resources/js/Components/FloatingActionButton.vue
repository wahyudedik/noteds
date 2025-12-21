<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CreatePostModal from '@/Components/CreatePostModal.vue';
import CreateProductModal from '@/Components/CreateProductModal.vue';

const page = usePage();
const isMenuOpen = ref(false);
const showPostModal = ref(false);
const showProductModal = ref(false);
const menuRef = ref(null);

const toggleMenu = () => {
    isMenuOpen.value = !isMenuOpen.value;
};

const openPostModal = () => {
    showPostModal.value = true;
    isMenuOpen.value = false;
};

const openProductModal = () => {
    showProductModal.value = true;
    isMenuOpen.value = false;
};

const closePostModal = () => {
    showPostModal.value = false;
};

const closeProductModal = () => {
    showProductModal.value = false;
};

// Close menu when clicking outside
const handleClickOutside = (event) => {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div v-if="page.props.auth?.user" ref="menuRef" class="fixed bottom-6 z-40 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-8 md:bottom-8">
        <!-- Action Menu (expanded) -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div v-if="isMenuOpen" class="absolute bottom-16 right-0 mb-2 flex flex-col gap-2">
                <!-- Create Post Button -->
                <button
                    @click="openPostModal"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border border-gray-200 dark:border-gray-700 min-w-[160px]"
                    aria-label="Create Post"
                >
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="font-medium">Create Post</span>
                </button>

                <!-- Add Product Button -->
                <button
                    @click="openProductModal"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border border-gray-200 dark:border-gray-700 min-w-[160px]"
                    aria-label="Add Product"
                >
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="font-medium">Add Product</span>
                </button>
            </div>
        </Transition>

        <!-- Main Floating Action Button -->
        <button
            @click="toggleMenu"
            :class="[
                'h-14 w-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg hover:shadow-xl transform transition-all duration-200 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                isMenuOpen ? 'rotate-45 scale-110' : 'hover:scale-110'
            ]"
            :aria-label="isMenuOpen ? 'Close menu' : 'Open menu'"
        >
            <svg class="w-6 h-6 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

        <!-- Create Post Modal -->
        <CreatePostModal :show="showPostModal" @close="closePostModal" />

        <!-- Create Product Modal -->
        <CreateProductModal :show="showProductModal" @close="closeProductModal" />
    </div>
</template>


