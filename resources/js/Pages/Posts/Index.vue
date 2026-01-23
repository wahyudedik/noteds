<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostFeed from '@/Components/PostFeed.vue';
import PostComposer from '@/Components/PostComposer.vue';
import { Head, usePage } from '@inertiajs/vue3';
import SidebarWidget from '@/Components/SidebarWidget.vue';
import YouMightLike from '@/Components/Recommendations/YouMightLike.vue';
import TrendingContent from '@/Components/Recommendations/TrendingContent.vue';
import SimilarUsers from '@/Components/Recommendations/SimilarUsers.vue';

const page = usePage();

defineProps({
    posts: Object,
    filters: Object,
    userVotes: {
        type: Object,
        default: () => ({}),
    },
    userBookmarks: {
        type: Object,
        default: () => ({}),
    },
    auth: Object,
});
</script>

<template>
    <Head title="Posts - Noteds" />

    <AuthenticatedLayout>
        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <!-- Same layout as Home -->
            <div class="mx-auto max-w-7xl">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
                    <!-- Main Feed Column -->
                    <div class="lg:col-span-8 order-2 lg:order-1">
                        <!-- Post Composer -->
                        <div class="mb-4 sm:mb-6">
                            <PostComposer v-if="page.props.auth?.user" />
                        </div>

                        <!-- Post Feed with infinite scroll -->
                        <PostFeed 
                            :posts="posts" 
                            :filters="filters"
                            :user-votes="userVotes || {}"
                            :user-bookmarks="userBookmarks || {}"
                        />
                    </div>

                    <!-- Right Sidebar (optional, can be empty or add widgets) -->
                    <div class="lg:col-span-4 hidden lg:block order-1 lg:order-2">
                        <div class="sticky top-4 space-y-6">
                            <SidebarWidget title="You Might Like">
                                <YouMightLike />
                            </SidebarWidget>
                            <SidebarWidget title="Trending">
                                <TrendingContent />
                            </SidebarWidget>
                            <SidebarWidget title="Similar Users">
                                <SimilarUsers />
                            </SidebarWidget>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

