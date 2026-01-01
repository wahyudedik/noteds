<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    clips: Object,
    filters: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        paid: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[status] || classes.pending;
};

const filterByStatus = (status) => {
    router.get(route('admin.clips.index'), { 
        status: status || null,
        fraud_detected: filters?.fraud_detected || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filterByFraud = (fraudDetected) => {
    router.get(route('admin.clips.index'), { 
        status: filters?.status || null,
        fraud_detected: fraudDetected || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Manage Clips" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Manage Clips
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="mb-4 flex flex-wrap gap-2">
                    <div class="flex space-x-2">
                        <button
                            @click="filterByStatus(null)"
                            :class="['px-4 py-2 rounded-lg', !filters?.status ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            All
                        </button>
                        <button
                            @click="filterByStatus('pending')"
                            :class="['px-4 py-2 rounded-lg', filters?.status === 'pending' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            Pending
                        </button>
                        <button
                            @click="filterByStatus('approved')"
                            :class="['px-4 py-2 rounded-lg', filters?.status === 'approved' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            Approved
                        </button>
                        <button
                            @click="filterByStatus('rejected')"
                            :class="['px-4 py-2 rounded-lg', filters?.status === 'rejected' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            Rejected
                        </button>
                        <button
                            @click="filterByStatus('paid')"
                            :class="['px-4 py-2 rounded-lg', filters?.status === 'paid' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            Paid
                        </button>
                    </div>
                    <div class="flex space-x-2">
                        <button
                            @click="filterByFraud(null)"
                            :class="['px-4 py-2 rounded-lg', !filters?.fraud_detected ? 'bg-gray-200 dark:bg-gray-700' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            All Clips
                        </button>
                        <button
                            @click="filterByFraud('1')"
                            :class="['px-4 py-2 rounded-lg', filters?.fraud_detected === '1' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700']"
                        >
                            ⚠️ Fraud Detected
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div v-if="clips?.data && clips.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clip ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Campaign</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Clipper</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Platform</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Views</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reward</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="clip in clips.data" :key="clip.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500 dark:text-gray-400">
                                        {{ clip.id.substring(0, 8) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ clip.campaign?.title || 'Unknown' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ clip.clipper?.name || 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 capitalize">
                                        {{ clip.platform }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(clip.valid_views) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(clip.approved_reward || clip.pending_reward) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span
                                                :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(clip.status)]"
                                            >
                                                {{ clip.status }}
                                            </span>
                                            <span
                                                v-if="clip.fraud_detected"
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                                title="Fraud detected - requires review"
                                            >
                                                ⚠️ Fraud
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <Link
                                            :href="route('admin.clips.show', clip.id)"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                                        >
                                            Review
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                        No clips found.
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="clips?.links && clips.links.length > 3" class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ clips.from }} to {{ clips.to }} of {{ clips.total }} results
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-for="link in clips.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 text-sm rounded-md',
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
    </AuthenticatedLayout>
</template>

