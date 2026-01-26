<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import CreatePostModal from '@/Components/CreatePostModal.vue';
import CreateProductModal from '@/Components/CreateProductModal.vue';
import { useFeatureGate } from '@/Composables/useFeatureGate';

const page = usePage();
const isMenuOpen = ref(false);
const showPostModal = ref(false);
const showProductModal = ref(false);
const menuRef = ref(null);
const { can } = useFeatureGate();

const isBrand = computed(() => {
    const user = page.props.auth?.user;
    return user?.clipper_role === 'brand' || user?.role === 'brand';
});

const isClipper = computed(() => {
    const user = page.props.auth?.user;
    return user?.clipper_role === 'clipper' || user?.role === 'clipper';
});

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
    <div v-if="page.props.auth?.user" ref="menuRef" class="fixed bottom-20 z-50 left-1/2 -translate-x-1/2 md:left-auto md:translate-x-0 md:right-8 md:bottom-8">
        <!-- Action Menu (expanded) -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95 translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-2"
        >
            <div 
                v-if="isMenuOpen" 
                class="absolute bottom-16 mb-2 flex flex-col gap-2 min-w-max left-1/2 -translate-x-1/2 items-center md:left-auto md:translate-x-0 md:right-0 md:items-end"
                role="menu"
                aria-orientation="vertical"
            >
                <!-- Create Campaign Button (Brand) -->
                <Link
                    v-if="isBrand"
                    :href="route('clipper.campaigns.create')"
                    @click="isMenuOpen = false"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border border-gray-200 dark:border-gray-700 w-full md:w-auto"
                    role="menuitem"
                    aria-label="Create Campaign"
                >
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <span class="font-medium whitespace-nowrap">Create Campaign</span>
                </Link>

                <!-- Submit Clip Button (Clipper) -->
                <Link
                    v-if="isClipper"
                    :href="route('clipper.campaigns.available')"
                    @click="isMenuOpen = false"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border border-gray-200 dark:border-gray-700 w-full md:w-auto"
                    role="menuitem"
                    aria-label="Submit Clip"
                >
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium whitespace-nowrap">Submit Clip</span>
                </Link>

                <!-- Create Post Button -->
                <button
                    @click.stop="openPostModal"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border border-gray-200 dark:border-gray-700 w-full md:w-auto"
                    role="menuitem"
                    aria-label="Create Post"
                >
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span class="font-medium whitespace-nowrap">Create Post</span>
                </button>

                <!-- Add Product Button -->
                <button
                    v-if="can('marketplace.seller')"
                    @click.stop="openProductModal"
                    class="flex items-center gap-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 border border-gray-200 dark:border-gray-700 w-full md:w-auto"
                    role="menuitem"
                    aria-label="Add Product"
                >
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="font-medium whitespace-nowrap">Add Product</span>
                </button>
            </div>
        </Transition>

        <!-- Main Floating Action Button -->
        <button
            @click.stop="toggleMenu"
            :class="[
                'h-14 w-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full shadow-lg hover:shadow-xl transform transition-all duration-200 flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                isMenuOpen ? 'rotate-45 scale-110' : 'hover:scale-110'
            ]"
            :aria-label="isMenuOpen ? 'Close menu' : 'Open menu'"
            aria-haspopup="true"
            :aria-expanded="isMenuOpen"
        >
            <svg class="w-6 h-6 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>

    </div>

    <!-- Create Post Modal - Teleported to body for fullscreen -->
    <Teleport to="body">
        <CreatePostModal :show="showPostModal" @close="closePostModal" />
    </Teleport>

    <!-- Create Product Modal - Teleported to body for fullscreen -->
    <Teleport to="body">
        <CreateProductModal :show="showProductModal" @close="closeProductModal" />
    </Teleport>
    
    <!-- Backdrop for mobile/desktop when menu is open -->
    <Teleport to="body">
         <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="isMenuOpen" 
                class="fixed inset-0 bg-black/30 z-40 backdrop-blur-sm"
                @click="isMenuOpen = false"
                aria-hidden="true"
            ></div>
        </Transition>
    </Teleport>
</template>
