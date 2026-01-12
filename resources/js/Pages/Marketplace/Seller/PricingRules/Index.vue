<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PricingRuleCard from '@/Components/Marketplace/Seller/PricingRuleCard.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rules: Object,
    filters: Object,
});

const filterForm = useForm({
    product_id: props.filters?.product_id || '',
    rule_type: props.filters?.rule_type || '',
    status: props.filters?.status || 'all',
});

const applyFilters = () => {
    filterForm.get(route('marketplace.seller.pricing-rules.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleRule = (rule) => {
    router.put(route('marketplace.seller.pricing-rules.toggle', rule.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pricing Rules" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Pricing Rules
                </h2>
                <Link
                    :href="route('marketplace.seller.pricing-rules.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                >
                    Create Rule
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Rule Type
                            </label>
                            <select
                                v-model="filterForm.rule_type"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">All Types</option>
                                <option value="time_based">Time-Based</option>
                                <option value="stock_based">Stock-Based</option>
                                <option value="demand_based">Demand-Based</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <select
                                v-model="filterForm.status"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-end">
                            <button
                                @click="applyFilters"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rules Grid -->
                <div v-if="rules?.data && rules.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <PricingRuleCard
                        v-for="rule in rules.data"
                        :key="rule.id"
                        :rule="rule"
                        :product="rule.product"
                        @toggle="toggleRule(rule)"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No pricing rules found.</p>
                    <Link
                        :href="route('marketplace.seller.pricing-rules.create')"
                        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Create Your First Rule
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="rules?.links" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ rules?.from || 0 }} to {{ rules?.to || 0 }} of {{ rules?.total || 0 }} results
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in rules.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-lg',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
                                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

