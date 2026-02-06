<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
    recentPosts: Array,
    recentComments: Array,
    reportsReceived: Array,
    reportsMade: Array,
});

const banForm = useForm({
    ban_reason: '',
});

const showBanModal = ref(false);

const banUser = () => {
    if (!banForm.ban_reason.trim()) {
        banForm.setError('ban_reason', 'Ban reason is required.');
        return;
    }

    banForm.post(route('admin.users.ban', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            showBanModal.value = false;
            banForm.reset();
        },
    });
};

const unbanUser = () => {
    if (confirm('Are you sure you want to unban this user?')) {
        router.post(route('admin.users.unban', props.user.id), {}, {
            preserveScroll: true,
        });
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="'User: ' + (user.business_name || user.name)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    User Details
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('admin.users.index')"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm"
                    >
                        Back to Users
                    </Link>
                    <Link
                        :href="route('admin.users.edit', user.id)"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm"
                    >
                        Edit User
                    </Link>
                </div>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- User Info Card -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div class="flex items-center gap-4">
                                    <div class="h-20 w-20 rounded-full bg-indigo-500 flex items-center justify-center text-white text-3xl font-bold">
                                        {{ (user.business_name || user.name || 'U').charAt(0).toUpperCase() }}
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ user.business_name || user.name }}
                                        </h3>
                                        <p class="text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <span
                                                :class="[
                                                    'px-2 py-1 text-xs font-medium rounded-full',
                                                    user.role === 'admin'
                                                        ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                                ]"
                                            >
                                                {{ user.role }}
                                            </span>
                                            <span
                                                v-if="user.is_banned"
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                            >
                                                Banned
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                            >
                                                Active
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Posts</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.posts_count || 0 }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Comments</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.comments_count || 0 }}</div>
                                </div>
                            </div>

                            <div v-if="user.business_field" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Business Field</div>
                                <div class="text-base text-gray-900 dark:text-white">{{ user.business_field }}</div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Joined</div>
                                <div class="text-base text-gray-900 dark:text-white">{{ formatDate(user.created_at) }}</div>
                            </div>

                            <div v-if="user.is_banned" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Banned At</div>
                                <div class="text-base text-gray-900 dark:text-white">{{ formatDate(user.banned_at) }}</div>
                                <div v-if="user.ban_reason" class="mt-2">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Ban Reason</div>
                                    <div class="text-base text-gray-900 dark:text-white">{{ user.ban_reason }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Posts -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Posts</h4>
                            <div v-if="recentPosts.length > 0" class="space-y-3">
                                <Link
                                    v-for="post in recentPosts"
                                    :key="post.id"
                                    :href="route('posts.show', post.id)"
                                    class="block p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white">{{ post.title }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ formatDate(post.created_at) }}
                                    </div>
                                </Link>
                            </div>
                            <p v-else class="text-gray-500 dark:text-gray-400">No posts yet.</p>
                        </div>

                        <!-- Recent Comments -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Comments</h4>
                            <div v-if="recentComments.length > 0" class="space-y-3">
                                <div
                                    v-for="comment in recentComments"
                                    :key="comment.id"
                                    class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg"
                                >
                                    <div class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                        {{ comment.content }}
                                    </div>
                                    <Link
                                        v-if="comment.post"
                                        :href="route('posts.show', comment.post.id)"
                                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline mt-1 block"
                                    >
                                        View Post →
                                    </Link>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ formatDate(comment.created_at) }}
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 dark:text-gray-400">No comments yet.</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Actions -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h4>
                            <div class="space-y-3">
                                <button
                                    v-if="!user.is_banned && !user.isAdmin()"
                                    @click="showBanModal = true"
                                    class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                                >
                                    Ban User
                                </button>
                                <button
                                    v-if="user.is_banned"
                                    @click="unbanUser"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                                >
                                    Unban User
                                </button>
                                <Link
                                    :href="route('profile.show', user.id)"
                                    target="_blank"
                                    class="block w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm text-center"
                                >
                                    View Profile
                                </Link>
                            </div>
                        </div>

                        <!-- Reports Received -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reports Received</h4>
                            <div v-if="reportsReceived.length > 0" class="space-y-2">
                                <div
                                    v-for="report in reportsReceived.slice(0, 5)"
                                    :key="report.id"
                                    class="p-2 border border-gray-200 dark:border-gray-700 rounded text-sm"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ report.reason }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ formatDate(report.created_at) }}
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 dark:text-gray-400 text-sm">No reports received.</p>
                        </div>

                        <!-- Reports Made -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reports Made</h4>
                            <div v-if="reportsMade.length > 0" class="space-y-2">
                                <div
                                    v-for="report in reportsMade.slice(0, 5)"
                                    :key="report.id"
                                    class="p-2 border border-gray-200 dark:border-gray-700 rounded text-sm"
                                >
                                    <div class="font-medium text-gray-900 dark:text-white capitalize">{{ report.reason }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ formatDate(report.created_at) }}
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 dark:text-gray-400 text-sm">No reports made.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ban Modal -->
        <div
            v-if="showBanModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            @click.self="showBanModal = false"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showBanModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ban User</h3>
                    <form @submit.prevent="banUser">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Ban Reason <span class="text-red-500">*</span>
                            </label>
                            <Textarea
                                v-model="banForm.ban_reason"
                                rows="4"
                                placeholder="Enter the reason for banning this user..."
                                class="w-full"
                            />
                            <InputError :message="banForm.errors.ban_reason" />
                        </div>
                        <div class="flex gap-2">
                            <PrimaryButton type="submit" :disabled="banForm.processing">
                                Ban User
                            </PrimaryButton>
                            <button
                                type="button"
                                @click="showBanModal = false"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

