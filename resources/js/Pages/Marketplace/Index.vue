<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductCard from '@/Components/Marketplace/ProductCard.vue';
import SearchBar from '@/Components/Marketplace/SearchBar.vue';
import ProductFilter from '@/Components/Marketplace/ProductFilter.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const page = usePage();

const props = defineProps({
    products: Object,
    filters: Object,
});

const searchQuery = ref('');
const productsList = ref([]);
const currentPage = ref(1);
const hasMorePages = ref(true);
const isLoading = ref(false);
const sentinelRef = ref(null);
const observerInstance = ref(null);

// Initialize products list from props
const initializeProducts = () => {
    if (props.products?.data) {
        productsList.value = [...props.products.data];
        currentPage.value = props.products.current_page || 1;
        hasMorePages.value = props.products.next_page_url !== null;
    }
};

// Initialize on mount
onMounted(() => {
    initializeProducts();
    
    // Setup Intersection Observer after next tick to ensure sentinelRef is mounted
    nextTick(() => {
        if (sentinelRef.value) {
            observerInstance.value = new IntersectionObserver(
                (entries) => {
                    if (entries[0].isIntersecting && hasMorePages.value && !isLoading.value) {
                        loadMore();
                    }
                },
                {
                    rootMargin: '100px',
                }
            );
            
            observerInstance.value.observe(sentinelRef.value);
        }
    });
});

onUnmounted(() => {
    if (observerInstance.value) {
        observerInstance.value.disconnect();
    }
});

// Watch for filter/search changes and reset
watch(() => [props.filters, props.products], () => {
    initializeProducts();
}, { deep: true });

const loadMore = () => {
    if (isLoading.value || !hasMorePages.value) return;
    
    isLoading.value = true;
    
    const nextPage = currentPage.value + 1;
    const queryParams = {
        page: nextPage,
        ...props.filters,
    };
    
    router.get(
        route('marketplace.index'),
        queryParams,
        {
            preserveState: true,
            preserveScroll: true,
            only: ['products'],
            onSuccess: (page) => {
                const newProducts = page.props.products?.data || [];
                productsList.value.push(...newProducts);
                currentPage.value = page.props.products.current_page || nextPage;
                hasMorePages.value = page.props.products.next_page_url !== null;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            },
        }
    );
};
</script>

<template>
    <Head title="Marketplace" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Marketplace
            </h2>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Search and Filter Bar -->
                <div class="mb-4 sm:mb-6">
                    <SearchBar :filters="filters" />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
                    <!-- Sidebar Filters -->
                    <aside class="lg:col-span-1">
                        <ProductFilter :filters="filters" />
                    </aside>

                    <!-- Products Grid -->
                    <div class="lg:col-span-3">
                        <div class="mb-3 sm:mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Products</h3>
                            <Link
                                v-if="page.props.auth?.user"
                                :href="route('marketplace.products.create')"
                                class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm sm:text-base font-medium transition-colors text-center"
                            >
                                Add Product
                            </Link>
                        </div>

                        <div v-if="productsList.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <ProductCard
                                v-for="product in productsList"
                                :key="product.id"
                                :product="product"
                            />
                        </div>

                        <div v-else class="text-center py-12 text-gray-500">
                            No products found.
                        </div>

                        <!-- Loading indicator -->
                        <div v-if="isLoading" class="mt-6 text-center py-8">
                            <div class="inline-flex items-center space-x-2 text-gray-500">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Loading more products...</span>
                            </div>
                        </div>

                        <!-- End of results message -->
                        <div v-if="!hasMorePages && productsList.length > 0" class="mt-6 text-center py-4 text-gray-500 text-sm">
                            No more products to load.
                        </div>

                        <!-- Sentinel element for infinite scroll -->
                        <div ref="sentinelRef" class="h-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

