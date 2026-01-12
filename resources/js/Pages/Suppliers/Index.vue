<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import SupplierCard from '@/Components/SupplierCard.vue';
import { ref } from 'vue';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const filterForm = useForm({
    category: props.filters.category || '',
    location: props.filters.location || '',
    min_rating: props.filters.min_rating || '',
    verified_only: props.filters.verified_only || false,
    sort_by: props.filters.sort_by || 'rating',
    sort_order: props.filters.sort_order || 'desc',
});

const applyFilters = () => {
    filterForm.get(route('suppliers.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    filterForm.reset();
    filterForm.get(route('suppliers.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Daftar Supplier" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Daftar Supplier
                </h2>
                <Link
                    :href="route('suppliers.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                >
                    Daftar sebagai Supplier
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filter Supplier</h3>
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Kategori
                            </label>
                            <input
                                v-model="filterForm.category"
                                type="text"
                                placeholder="Cari kategori..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Lokasi
                            </label>
                            <input
                                v-model="filterForm.location"
                                type="text"
                                placeholder="Kota/Kabupaten..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Rating Min
                            </label>
                            <input
                                v-model="filterForm.min_rating"
                                type="number"
                                min="0"
                                max="5"
                                step="0.1"
                                placeholder="0.0"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Urutkan
                            </label>
                            <select
                                v-model="filterForm.sort_by"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="rating">Rating</option>
                                <option value="reviews">Jumlah Review</option>
                                <option value="orders">Jumlah Order</option>
                                <option value="created_at">Terbaru</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                            >
                                Terapkan
                            </button>
                            <button
                                type="button"
                                @click="clearFilters"
                                class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-lg text-sm font-medium"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                    <div class="mt-4">
                        <label class="flex items-center">
                            <input
                                v-model="filterForm.verified_only"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Hanya Supplier Terverifikasi</span>
                        </label>
                    </div>
                </div>

                <!-- Suppliers List -->
                <div v-if="suppliers.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <SupplierCard
                        v-for="supplier in suppliers"
                        :key="supplier.id"
                        :supplier="supplier"
                    />
                </div>
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        Tidak ada supplier yang ditemukan dengan filter yang dipilih.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


