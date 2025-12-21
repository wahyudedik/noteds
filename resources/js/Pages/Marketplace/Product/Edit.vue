<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ProductForm from '@/Components/Marketplace/ProductForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    product: Object,
});
</script>

<template>
    <Head title="Edit Product" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Edit Product
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <ProductForm :product="product" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

