<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import { computed } from 'vue';

const props = defineProps({
    posts: Object,
    filters: Object,
    auth: Object,
});

const Layout = computed(() => props.auth?.user ? AuthenticatedLayout : GuestLayout);

const filterByPurpose = (purposeType) => {
    router.get(route('posts.index'), { purpose_type: purposeType || 'all' }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Posts" />

    <component :is="Layout">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Posts
                </h2>
                <Link
                    v-if="auth?.user"
                    :href="route('posts.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Buat Post
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="mb-6 flex flex-wrap gap-2">
                    <button
                        @click="filterByPurpose('all')"
                        :class="[
                            'px-4 py-2 rounded-md text-sm font-medium',
                            (!filters?.purpose_type || filters.purpose_type === 'all')
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        Semua
                    </button>
                    <button
                        v-for="(label, type) in PURPOSE_TYPE_LABELS"
                        :key="type"
                        @click="filterByPurpose(type)"
                        :class="[
                            'px-4 py-2 rounded-md text-sm font-medium',
                            filters?.purpose_type === type
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
                        ]"
                    >
                        {{ label }}
                    </button>
                </div>

                <!-- Posts List -->
                <div class="space-y-4">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ PURPOSE_TYPE_LABELS[post.purpose_type] }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">•</span>
                                        <Link
                                            :href="route('profile.show', post.user.id)"
                                            class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                                        >
                                            {{ post.user.business_name || post.user.name }}
                                        </Link>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">•</span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ new Date(post.created_at).toLocaleDateString('id-ID') }}
                                        </span>
                                    </div>
                                    <Link
                                        :href="route('posts.show', post.id)"
                                        class="block"
                                    >
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            {{ post.title }}
                                        </h3>
                                        <p class="mt-2 text-gray-600 dark:text-gray-400 line-clamp-3">
                                            {{ post.content }}
                                        </p>
                                    </Link>
                                    <div class="mt-4 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>👍 {{ post.upvotes_count }}</span>
                                        <span>👎 {{ post.downvotes_count }}</span>
                                        <span>💬 {{ post.comments_count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="posts.links" class="mt-6">
                    <div class="flex justify-center gap-2">
                        <Link
                            v-for="(link, index) in posts.links"
                            :key="index"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 rounded-md text-sm',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>

