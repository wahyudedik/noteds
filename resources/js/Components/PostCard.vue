<script setup>
import { Link } from '@inertiajs/vue3';
import VoteButton from '@/Components/VoteButton.vue';
import BookmarkButton from '@/Components/Bookmark/BookmarkButton.vue';
import ReportButton from '@/Components/Report/ReportButton.vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    userVote: {
        type: String,
        default: null,
    },
    isBookmarked: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

// Format date
const formatDate = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
};
</script>

<template>
    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="p-4 sm:p-6">
            <!-- Header -->
            <div class="flex items-start justify-between mb-3 sm:mb-4 gap-2">
                <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                    <Link
                        :href="route('profile.show', post.user.id)"
                        class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold hover:ring-2 hover:ring-indigo-300 transition overflow-hidden flex-shrink-0"
                    >
                        <img
                            v-if="post.user.avatar_url"
                            :src="post.user.avatar_url"
                            :alt="post.user.business_name || post.user.name"
                            class="w-full h-full object-cover"
                        />
                        <span v-else class="text-xs sm:text-sm">
                            {{ (post.user.business_name || post.user.name).charAt(0).toUpperCase() }}
                        </span>
                    </Link>
                    <div class="flex-1 min-w-0">
                        <Link
                            :href="route('profile.show', post.user.id)"
                            class="block font-semibold text-sm sm:text-base text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 transition truncate"
                        >
                            {{ post.user.business_name || post.user.name }}
                        </Link>
                        <div class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                            <span>{{ formatDate(post.created_at) }}</span>
                            <span class="hidden sm:inline">•</span>
                            <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                {{ PURPOSE_TYPE_LABELS[post.purpose_type] }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-if="page.props.auth?.user && page.props.auth.user.id !== post.user_id">
                    <ReportButton
                        reportable-type="post"
                        :reportable-id="post.id"
                        variant="icon"
                        size="sm"
                    />
                </div>
            </div>

            <!-- Content -->
            <Link :href="route('posts.show', post.id)" class="block group">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                    {{ post.title }}
                </h3>
                <p class="text-gray-700 dark:text-gray-300 line-clamp-3">
                    {{ post.content }}
                </p>
            </Link>

            <!-- Actions -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <VoteButton
                        :post-id="post.id"
                        :upvotes="post.upvotes_count"
                        :downvotes="post.downvotes_count"
                        :user-vote="userVote"
                        :can-vote="page.props.auth?.user && page.props.auth.user.id !== post.user_id"
                    />
                    <div class="flex items-center gap-3">
                        <BookmarkButton
                            v-if="page.props.auth?.user"
                            :post-id="post.id"
                            :is-bookmarked="isBookmarked"
                            :can-bookmark="!!page.props.auth?.user"
                        />
                        <Link
                            :href="route('posts.show', post.id)"
                            class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            {{ post.comments_count || 0 }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </article>
</template>

