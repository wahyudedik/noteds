<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FaqForm from '@/Components/Admin/FaqForm.vue';

defineProps({
    faq: Object,
});

</script>

<template>
    <Head title="Edit FAQ" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit FAQ
                </h2>
                <Link
                    :href="route('admin.faqs.index')"
                    class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <FaqForm :faq="faq" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

