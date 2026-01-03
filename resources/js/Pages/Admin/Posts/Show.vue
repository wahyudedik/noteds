<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import InputError from '@/Components/InputError.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    post: Object,
    moderation_history: Array,
    reports: Array,
});

const showModerateForm = ref(false);
const showRestoreForm = ref(false);
const moderateAction = ref('warn');

const moderateForm = useForm({
    action: 'warn',
    reason: '',
});

const restoreForm = useForm({
    reason: '',
});

const moderate = () => {
    moderateForm.action = moderateAction.value;
    moderateForm.post(route('admin.posts.moderate', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            showModerateForm.value = false;
            moderateForm.reset();
        },
    });
};

const restore = () => {
    restoreForm.post(route('admin.posts.restore', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRestoreForm.value = false;
            restoreForm.reset();
        },
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

const getActionBadgeClass = (action) => {
    const classes = {
        warn: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        hide: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        delete: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[action] || classes.warn;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="`Post #${post.id.slice(0, 8)}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Post Details
                </h2>
                <Link
                    :href="route('admin.posts.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Posts
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Post Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ post.title }}
                        </h1>
                        <span
                            :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(post.status)]"
                        >
                            {{ post.status }}
                        </span>
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ PURPOSE_TYPE_LABELS[post.purpose_type] || post.purpose_type }}
                        </span>
                    </div>

                    <div class="prose dark:prose-invert max-w-none mb-6">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ post.content }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Author</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ post.user?.name || 'Unknown' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Created</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ formatDate(post.created_at) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Comments</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ post.comments_count || 0 }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Votes</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ (post.upvotes_count || 0) - (post.downvotes_count || 0) }}
                            </div>
                        </div>
                    </div>

                    <!-- Moderation Actions -->
                    <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button
                            v-if="post.status !== 'active'"
                            @click="showRestoreForm = !showRestoreForm"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                        >
                            Restore Post
                        </button>
                        <button
                            v-if="post.status === 'active'"
                            @click="showModerateForm = !showModerateForm; moderateAction = 'warn'"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm"
                        >
                            Warn
                        </button>
                        <button
                            v-if="post.status === 'active'"
                            @click="showModerateForm = !showModerateForm; moderateAction = 'hide'"
                            class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors text-sm"
                        >
                            Hide
                        </button>
                        <button
                            v-if="post.status === 'active'"
                            @click="showModerateForm = !showModerateForm; moderateAction = 'delete'"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                        >
                            Delete
                        </button>
                    </div>

                    <!-- Moderate Form -->
                    <div v-if="showModerateForm" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="moderate" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Action: <span class="font-semibold capitalize">{{ moderateAction }}</span>
                                </label>
                                <div class="flex gap-2 mb-4">
                                    <button
                                        type="button"
                                        @click="moderateAction = 'warn'"
                                        :class="[
                                            'px-3 py-1 rounded text-sm',
                                            moderateAction === 'warn'
                                                ? 'bg-yellow-600 text-white'
                                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                        ]"
                                    >
                                        Warn
                                    </button>
                                    <button
                                        type="button"
                                        @click="moderateAction = 'hide'"
                                        :class="[
                                            'px-3 py-1 rounded text-sm',
                                            moderateAction === 'hide'
                                                ? 'bg-orange-600 text-white'
                                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                        ]"
                                    >
                                        Hide
                                    </button>
                                    <button
                                        type="button"
                                        @click="moderateAction = 'delete'"
                                        :class="[
                                            'px-3 py-1 rounded text-sm',
                                            moderateAction === 'delete'
                                                ? 'bg-red-600 text-white'
                                                : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                                        ]"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason *
                                </label>
                                <Textarea
                                    v-model="moderateForm.reason"
                                    class="w-full"
                                    rows="4"
                                    placeholder="Enter moderation reason..."
                                    required
                                />
                                <InputError :message="moderateForm.errors.reason" />
                            </div>
                            <div class="flex gap-2">
                                <PrimaryButton :disabled="moderateForm.processing">
                                    Submit
                                </PrimaryButton>
                                <button
                                    type="button"
                                    @click="showModerateForm = false; moderateForm.reset()"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Restore Form -->
                    <div v-if="showRestoreForm" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="restore" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Reason (Optional)
                                </label>
                                <Textarea
                                    v-model="restoreForm.reason"
                                    class="w-full"
                                    rows="3"
                                    placeholder="Enter restore reason (optional)..."
                                />
                                <InputError :message="restoreForm.errors.reason" />
                            </div>
                            <div class="flex gap-2">
                                <PrimaryButton :disabled="restoreForm.processing">
                                    Restore Post
                                </PrimaryButton>
                                <button
                                    type="button"
                                    @click="showRestoreForm = false; restoreForm.reset()"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reports -->
                <div v-if="reports && reports.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        Reports for this Post
                    </h3>
                    <div class="space-y-4">
                        <div
                            v-for="report in reports"
                            :key="report.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    Report #{{ report.id.slice(0, 8) }}
                                </div>
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        report.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                        report.status === 'resolved' ? 'bg-green-100 text-green-800' :
                                        'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ report.status }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <span class="font-medium">Reason:</span> {{ report.reason }}
                            </div>
                            <div v-if="report.notes" class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <span class="font-medium">Notes:</span> {{ report.notes }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-500">
                                Reported by {{ report.user?.name || 'Unknown' }} on {{ formatDate(report.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Moderation History -->
                <div v-if="moderation_history && moderation_history.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        Moderation History
                    </h3>
                    <div class="space-y-4">
                        <div
                            v-for="(entry, index) in moderation_history"
                            :key="index"
                            class="border-l-4 border-gray-300 dark:border-gray-600 pl-4"
                        >
                            <div class="flex items-center gap-2 mb-2">
                                <span
                                    :class="['px-2 py-1 text-xs font-medium rounded-full', getActionBadgeClass(entry.action)]"
                                >
                                    {{ entry.action }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    by {{ entry.moderator?.name || 'Unknown' }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ formatDate(entry.created_at) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                {{ entry.reason }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

