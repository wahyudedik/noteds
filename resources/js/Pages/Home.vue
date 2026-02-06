<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostFeed from '@/Components/PostFeed.vue';
import SidebarWidget from '@/Components/SidebarWidget.vue';
import PostComposer from '@/Components/PostComposer.vue';
import TrendingTopics from '@/Components/Widgets/TrendingTopics.vue';
import SuggestedUsers from '@/Components/Widgets/SuggestedUsers.vue';
import { Head, usePage } from '@inertiajs/vue3';
import YouMightLike from '@/Components/Recommendations/YouMightLike.vue';
 

const page = usePage();

defineProps({
    posts: Object,
    filters: Object,
    trending: Array,
    suggestedUsers: Array,
    userVotes: Object,
    userBookmarks: Object,
    auth: Object,
    shareDraft: Object,
});
</script>

<template>
    <Head title="Home - Noteds" />

    <AuthenticatedLayout>
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <!-- Hybrid Layout: Feed + Sidebar -->
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
                    <!-- Main Feed Column -->
                    <div class="lg:col-span-8 order-2 lg:order-1">
                        <!-- Post Composer -->
                        <div class="mb-4 sm:mb-6">
                            <PostComposer v-if="page.props.auth?.user" :share-draft="shareDraft" />
                        </div>

                        <!-- Post Feed -->
                        <PostFeed 
                            :posts="posts" 
                            :filters="filters"
                            :user-votes="userVotes || {}"
                            :user-bookmarks="userBookmarks || {}"
                        />
                    </div>

                    <!-- Right Sidebar -->
                    <div class="lg:col-span-4 hidden lg:block order-1 lg:order-2">
                        <div class="sticky top-4 space-y-6">
                            <!-- Trending Topics Widget -->
                            <SidebarWidget title="Trending Topics" v-if="trending && trending.length > 0">
                                <TrendingTopics :topics="trending" />
                            </SidebarWidget>

                            <!-- Suggested Users Widget -->
                            <SidebarWidget title="Suggested Users" v-if="suggestedUsers && suggestedUsers.length > 0">
                                <SuggestedUsers :users="suggestedUsers" />
                            </SidebarWidget>

                            <SidebarWidget title="You Might Like">
                                <YouMightLike />
                            </SidebarWidget>

 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

