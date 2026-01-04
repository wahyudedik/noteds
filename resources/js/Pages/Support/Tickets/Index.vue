<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tickets: Object,
    filters: Object,
    categories: Array,
});

const filterForm = useForm({
    status: props.filters?.status || 'all',
    category: props.filters?.category || '',
    search: props.filters?.search || '',
});

const applyFilters = () => {
    filterForm.get(route('support.tickets.index'), {
        preserveState: true,
    });
};

const clearFilters = () => {
    filterForm.status = 'all';
    filterForm.category = '';
    filterForm.search = '';
    applyFilters();
};

const getStatusBadgeClass = (status) => {
    const classes = {
        open: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        in_progress: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        resolved: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        closed: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
    };
    return classes[status] || classes.open;
};

const getPriorityBadgeClass = (priority) => {
    const classes = {
        low: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        medium: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        high: 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
        urgent: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    };
    return classes[priority] || classes.medium;
};
</script>

<template>
    <Head title="My Support Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    My Support Tickets
                </h2>
                <Link
                    :href="route('support.tickets.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Create Ticket
                </Link>
            </div>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select
                                v-model="filterForm.status"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="all">All Status</option>
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                            <select
                                v-model="filterForm.category"
                                @change="applyFilters"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                            <div class="flex gap-2">
                                <input
                                    v-model="filterForm.search"
                                    type="text"
                                    placeholder="Search tickets..."
                                    @keyup.enter="applyFilters"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                                <button
                                    @click="applyFilters"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                                >
                                    Search
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button
                            @click="clearFilters"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Tickets List -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div v-if="tickets.data.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No tickets found. <Link :href="route('support.tickets.create')" class="text-blue-600 hover:underline">Create one</Link>
                    </div>

                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <Link
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            :href="route('support.tickets.show', ticket.id)"
                            class="block p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ ticket.ticket_number }}
                                        </span>
                                        <span
                                            :class="['px-2 py-1 text-xs rounded-full', getStatusBadgeClass(ticket.status)]"
                                        >
                                            {{ ticket.status.replace('_', ' ') }}
                                        </span>
                                        <span
                                            :class="['px-2 py-1 text-xs rounded-full', getPriorityBadgeClass(ticket.priority)]"
                                        >
                                            {{ ticket.priority }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">
                                        {{ ticket.subject }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-2">
                                        {{ ticket.message }}
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                        <span>Category: {{ ticket.category || 'General' }}</span>
                                        <span>{{ new Date(ticket.created_at).toLocaleDateString() }}</span>
                                        <span v-if="ticket.responses && ticket.responses.length > 0">
                                            {{ ticket.responses.length }} response(s)
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tickets.links && tickets.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <nav class="flex justify-center">
                            <div class="flex space-x-2">
                                <Link
                                    v-for="link in tickets.links"
                                    :key="link.label"
                                    :href="link.url ?? '#'"
                                    :class="[
                                        'px-4 py-2 rounded-lg',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

