<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostFeed from '@/Components/PostFeed.vue';
import SidebarWidget from '@/Components/SidebarWidget.vue';
import PostComposer from '@/Components/PostComposer.vue';
import { Head, router } from '@inertiajs/vue3';

defineProps({
    posts: Object,
    filters: Object,
    trending: Array,
    suggestedUsers: Array,
    userVotes: Object,
    auth: Object,
});
</script>

<template>
    <Head title="Home - Noteds" />

    <AuthenticatedLayout>
        <div class="px-4 py-6 lg:px-6">
            <!-- Hybrid Layout: Feed + Sidebar -->
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Main Feed Column -->
                    <div class="lg:col-span-8 order-2 lg:order-1">
                        <!-- Post Composer -->
                        <div class="mb-6">
                            <PostComposer v-if="$page.props.auth?.user" />
                        </div>

                        <!-- Post Feed -->
                        <PostFeed 
                            :posts="posts" 
                            :filters="filters"
                            :user-votes="userVotes || {}"
                        />
                    </div>

                    <!-- Right Sidebar -->
                    <div class="lg:col-span-4 hidden lg:block order-1 lg:order-2">
                        <div class="sticky top-4 space-y-6">
                            <!-- Trending Topics Widget -->
                            <SidebarWidget title="Trending Topics" v-if="trending && trending.length > 0">
                                <div class="space-y-2">
                                    <button
                                        v-for="topic in trending"
                                        :key="topic.id"
                                        @click="router.get(route('home'), { purpose_type: topic.id === 'all' ? null : topic.id }, { preserveState: true, preserveScroll: true })"
                                        class="w-full text-left block px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                    >
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ topic.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ topic.count }} posts
                                        </div>
                                    </button>
                                </div>
                            </SidebarWidget>

                            <!-- Suggested Users Widget -->
                            <SidebarWidget title="Suggested Users" v-if="suggestedUsers && suggestedUsers.length > 0">
                                <div class="space-y-3">
                                    <a
                                        v-for="user in suggestedUsers"
                                        :key="user.id"
                                        :href="route('profile.show', user.id)"
                                        class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                                    >
                                        <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                            {{ (user.business_name || user.name).charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ user.business_name || user.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ user.business_field || 'Business' }}
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </SidebarWidget>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

