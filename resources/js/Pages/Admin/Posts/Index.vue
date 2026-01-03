<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';

const props = defineProps({
    posts: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || 'all');
const selectedPurposeType = ref(props.filters?.purpose_type || 'all');

const filterPosts = () => {
    router.get(route('admin.posts.index'), {
        status: selectedStatus.value !== 'all' ? selectedStatus.value : null,
        purpose_type: selectedPurposeType.value !== 'all' ? selectedPurposeType.value : null,
        search: searchQuery.value || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        moderated: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        archived: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.active;
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const truncate = (text, length = 100) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
    <Head title="Post Moderation" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Post Moderation
                </h2>
                <Link
                    :href="route('admin.dashboard')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Dashboard
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="space-y-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Search
                            </label>
                            <div class="flex gap-2">
                                <input
                                    v-model="searchQuery"
                                    @keyup.enter="filterPosts"
                                    type="text"
                                    placeholder="Search by title or content..."
                                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <button
                                    @click="filterPosts"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                                >
                                    Search
                                </button>
                            </div>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    @click="selectedStatus = 'all'; filterPosts()"
                                    :class="[
                                        'px-4 py-2 rounded-lg transition-colors text-sm',
                                        selectedStatus === 'all'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    All
                                </button>
                                <button
                                    @click="selectedStatus = 'active'; filterPosts()"
                                    :class="[
                                        'px-4 py-2 rounded-lg transition-colors text-sm',
                                        selectedStatus === 'active'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    Active
                                </button>
                                <button
                                    @click="selectedStatus = 'moderated'; filterPosts()"
                                    :class="[
                                        'px-4 py-2 rounded-lg transition-colors text-sm',
                                        selectedStatus === 'moderated'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    Moderated
                                </button>
                                <button
                                    @click="selectedStatus = 'archived'; filterPosts()"
                                    :class="[
                                        'px-4 py-2 rounded-lg transition-colors text-sm',
                                        selectedStatus === 'archived'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    Archived
                                </button>
                            </div>
                        </div>

                        <!-- Purpose Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Purpose Type
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    @click="selectedPurposeType = 'all'; filterPosts()"
                                    :class="[
                                        'px-4 py-2 rounded-lg transition-colors text-sm',
                                        selectedPurposeType === 'all'
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    All
                                </button>
                                <button
                                    v-for="(label, type) in PURPOSE_TYPE_LABELS"
                                    :key="type"
                                    @click="selectedPurposeType = type; filterPosts()"
                                    :class="[
                                        'px-4 py-2 rounded-lg transition-colors text-sm',
                                        selectedPurposeType === type
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    {{ label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Posts List -->
                <div v-if="posts?.data && posts.data.length > 0" class="space-y-4">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <Link
                                        :href="route('admin.posts.show', post.id)"
                                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400"
                                    >
                                        {{ post.title }}
                                    </Link>
                                    <span
                                        :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(post.status)]"
                                    >
                                        {{ post.status }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ PURPOSE_TYPE_LABELS[post.purpose_type] || post.purpose_type }}
                                    </span>
                                </div>

                                <div class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                    {{ truncate(post.content, 200) }}
                                </div>

                                <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <div>
                                        <span class="font-medium">Author:</span>
                                        {{ post.user?.name || 'Unknown' }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Created:</span>
                                        {{ formatDate(post.created_at) }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Comments:</span>
                                        {{ post.comments_count || 0 }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Votes:</span>
                                        {{ (post.upvotes_count || 0) - (post.downvotes_count || 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <Link
                                :href="route('admin.posts.show', post.id)"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                            >
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <p class="text-gray-500 dark:text-gray-400">No posts found.</p>
                </div>

                <!-- Pagination -->
                <div v-if="posts?.links && posts.links.length > 3" class="flex justify-center">
                    <div class="flex gap-2">
                        <Link
                            v-for="(link, index) in posts.links"
                            :key="index"
                            :href="link.url || '#'"
                            :class="[
                                'px-4 py-2 rounded-lg transition-colors',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600',
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

