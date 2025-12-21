<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostFeed from '@/Components/PostFeed.vue';
import SidebarWidget from '@/Components/SidebarWidget.vue';
import PostComposer from '@/Components/PostComposer.vue';
import TrendingTopics from '@/Components/Widgets/TrendingTopics.vue';
import SuggestedUsers from '@/Components/Widgets/SuggestedUsers.vue';
import QuickStats from '@/Components/Widgets/QuickStats.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();

defineProps({
    posts: Object,
    filters: Object,
    trending: Array,
    suggestedUsers: Array,
    quickStats: Object,
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
                            <PostComposer v-if="page.props.auth?.user" />
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
                            <!-- Quick Stats Widget -->
                            <SidebarWidget title="Quick Stats" v-if="quickStats">
                                <QuickStats :stats="quickStats" />
                            </SidebarWidget>

                            <!-- Trending Topics Widget -->
                            <SidebarWidget title="Trending Topics" v-if="trending && trending.length > 0">
                                <TrendingTopics :topics="trending" />
                            </SidebarWidget>

                            <!-- Suggested Users Widget -->
                            <SidebarWidget title="Suggested Users" v-if="suggestedUsers && suggestedUsers.length > 0">
                                <SuggestedUsers :users="suggestedUsers" />
                            </SidebarWidget>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

