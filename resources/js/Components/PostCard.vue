<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import VoteButton from '@/Components/VoteButton.vue';
import BookmarkButton from '@/Components/Bookmark/BookmarkButton.vue';
import RepostButton from '@/Components/Repost/RepostButton.vue';
import QuoteRepostDisplay from '@/Components/Repost/QuoteRepostDisplay.vue';
import ReportButton from '@/Components/Report/ReportButton.vue';
import Dropdown from '@/Components/Dropdown.vue';
import LinkPreview from '@/Components/LinkPreview.vue';
import ImageGallery from '@/Components/ImageGallery.vue';
import HashtagList from '@/Components/HashtagList.vue';
import PollDisplay from '@/Components/Poll/PollDisplay.vue';
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
    isReposted: {
        type: Boolean,
        default: false,
    },
    userPollVote: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const showImageGallery = ref(false);
const selectedImageIndex = ref(0);

// Get the actual post ID to use for actions (original_post_id for reposts, otherwise post.id)
const actualPostId = computed(() => {
    // For reposts, use original_post_id; for regular posts, use post.id
    if (props.post.is_repost) {
        // Handle both object and array formats
        if (props.post.original_post_id) {
            return props.post.original_post_id;
        }
        if (props.post.original_post?.id) {
            return props.post.original_post.id;
        }
    }
    return props.post.id;
});

const openImageGallery = (index) => {
    selectedImageIndex.value = index;
    showImageGallery.value = true;
};

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

// Get image URL with fallback
const getImageUrl = (media) => {
    if (media.url) {
        return media.url;
    }
    // Fallback: construct URL from file_path
    if (media.file_path) {
        return `/storage/${media.file_path}`;
    }
    return '';
};

// Handle image error
const handleImageError = (event) => {
    console.warn('Image failed to load:', event.target.src);
    event.target.style.display = 'none';
};

// Get masonry grid style based on image count (Pinterest-style)
const getMasonryStyle = (imageCount) => {
    if (imageCount === 1) {
        return {
            gridTemplateColumns: '1fr',
            gridAutoRows: 'minmax(200px, auto)',
        };
    } else if (imageCount === 2) {
        return {
            gridTemplateColumns: 'repeat(2, 1fr)',
            gridAutoRows: 'minmax(200px, auto)',
        };
    } else if (imageCount === 3) {
        return {
            gridTemplateColumns: 'repeat(2, 1fr)',
            gridAutoRows: 'minmax(180px, auto)',
        };
    } else if (imageCount === 4) {
        return {
            gridTemplateColumns: 'repeat(2, 1fr)',
            gridAutoRows: 'minmax(180px, auto)',
        };
    } else {
        // 5+ images: use 3 columns masonry for compact layout
        return {
            gridTemplateColumns: 'repeat(3, 1fr)',
            gridAutoRows: 'minmax(150px, auto)',
        };
    }
};

// Get image class based on position and count (Pinterest-style sizing)
const getImageClass = (imageCount, index) => {
    if (imageCount === 1) {
        return 'aspect-video max-h-[400px]';
    } else if (imageCount === 2) {
        return 'aspect-square';
    } else if (imageCount === 3) {
        if (index === 0) {
            return 'row-span-2 aspect-auto max-h-[360px]';
        }
        return 'aspect-square';
    } else if (imageCount === 4) {
        return 'aspect-square';
    } else {
        // 5+ images: varied heights for masonry effect (more compact)
        const heightPatterns = [
            { h: 'h-40', max: 'max-h-[240px]' },
            { h: 'h-52', max: 'max-h-[280px]' },
            { h: 'h-44', max: 'max-h-[260px]' },
            { h: 'h-48', max: 'max-h-[270px]' },
            { h: 'h-50', max: 'max-h-[275px]' },
            { h: 'h-46', max: 'max-h-[265px]' },
        ];
        const pattern = heightPatterns[index % heightPatterns.length];
        return `${pattern.h} ${pattern.max} min-h-[180px]`;
    }
};
</script>

<template>
    <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="p-4 sm:p-6">
            <!-- Repost Indicator -->
            <div v-if="post.is_repost && post.repost_user" class="mb-2 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <Link
                    :href="route('profile.show', post.repost_user.id)"
                    class="font-medium hover:text-indigo-600 dark:hover:text-indigo-400"
                >
                    {{ post.repost_user.business_name || post.repost_user.name }}
                </Link>
                <span>{{ post.is_quote_repost ? 'quote reposted' : 'reposted' }}</span>
                <span v-if="post.repost_comment" class="text-gray-400">• {{ post.repost_comment }}</span>
            </div>
            
            <!-- Quote Repost Display -->
            <div v-if="post.is_quote_repost && post.quote_content" class="mb-4">
                <QuoteRepostDisplay
                    :repost="{
                        user: post.repost_user,
                        quote_content: post.quote_content,
                        is_quote_repost: true
                    }"
                    :original-post="post"
                    :quote-post="post.quote_post"
                    :display-mode="post.quote_display_mode || 'embedded'"
                />
            </div>

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
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                class="inline-flex items-center justify-center h-8 w-8 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600"
                                aria-label="Post actions"
                                title="Actions"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="6" r="1.5" />
                                    <circle cx="12" cy="12" r="1.5" />
                                    <circle cx="12" cy="18" r="1.5" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="py-1 bg-white dark:bg-gray-700 rounded-md">
                                <div class="px-1">
                                    <ReportButton
                                        reportable-type="post"
                                        :reportable-id="actualPostId"
                                        variant="text"
                                        size="sm"
                                    />
                                </div>
                                <button
                                    v-if="!(page.props.blocked_user_ids || []).includes(post.user_id)"
                                    @click.prevent="router.post(route('user.block', post.user.id))"
                                    class="flex w-full items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md dark:text-gray-200 dark:hover:bg-gray-600"
                                >
                                    Block user
                                </button>
                                <button
                                    v-else
                                    @click.prevent="router.delete(route('user.unblock', post.user.id))"
                                    class="flex w-full items-center px-3 py-2 text-sm text-red-700 hover:bg-red-100 rounded-md dark:text-red-200 dark:hover:bg-red-800/40"
                                >
                                    Unblock user
                                </button>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>

            <!-- Content -->
            <Link :href="route('posts.show', actualPostId)" class="block group">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                    {{ post.title }}
                </h3>
                <p class="text-gray-700 dark:text-gray-300 line-clamp-3">
                    {{ post.content }}
                </p>
            </Link>

            <!-- Images Grid -->
            <div v-if="post.media && post.media.length > 0" class="mt-4">
                <div
                    :class="[
                        'grid gap-2 rounded-lg overflow-hidden',
                        post.media.length === 1 ? 'grid-cols-1' : '',
                        post.media.length === 2 ? 'grid-cols-2' : '',
                        post.media.length >= 3 ? 'grid-cols-2' : '',
                    ]"
                >
                    <div
                        v-for="(media, index) in post.media.slice(0, 4)"
                        :key="media.id"
                        :class="[
                            'relative overflow-hidden bg-gray-100 dark:bg-gray-700 cursor-pointer hover:opacity-90 transition-opacity',
                            post.media.length === 1 ? 'aspect-video' : 'aspect-square',
                            index === 0 && post.media.length === 3 ? 'row-span-2' : '',
                        ]"
                        @click="openImageGallery(index)"
                    >
                        <img
                            :src="getImageUrl(media)"
                            :alt="media.file_name"
                            class="w-full h-full object-cover"
                            loading="lazy"
                            @error="handleImageError($event)"
                        />
                    </div>
                    <div
                        v-if="post.media.length > 4"
                        class="relative aspect-square bg-gray-900 bg-opacity-50 flex items-center justify-center cursor-pointer hover:opacity-90 transition-opacity"
                        @click="openImageGallery(4)"
                    >
                        <span class="text-white font-semibold text-lg">
                            +{{ post.media.length - 4 }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Image Gallery Modal -->
            <ImageGallery
                v-if="post.media && post.media.length > 0"
                :images="post.media"
                :initial-index="selectedImageIndex"
                :show="showImageGallery"
                @close="showImageGallery = false"
            />

            <!-- Link Preview -->
            <div v-if="post.link_url" class="mt-4">
                <LinkPreview
                    :preview="{
                        url: post.link_url,
                        title: post.link_preview_title,
                        description: post.link_preview_description,
                        image: post.link_preview_image,
                        site_name: post.link_preview_site_name,
                    }"
                />
            </div>

            <!-- Hashtags -->
            <HashtagList v-if="post.hashtags && post.hashtags.length > 0" :hashtags="post.hashtags" />

            <!-- Poll -->
            <PollDisplay
                v-if="post.poll"
                :poll="post.poll"
                :user-vote="userPollVote"
                :post-id="actualPostId"
            />


            <!-- Actions -->
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <VoteButton
                        :post-id="actualPostId"
                        :upvotes="post.upvotes_count"
                        :downvotes="post.downvotes_count"
                        :user-vote="userVote"
                        :can-vote="page.props.auth?.user && page.props.auth.user.id !== post.user_id"
                    />
                    <div class="flex items-center gap-3">
                        <RepostButton
                            v-if="page.props.auth?.user"
                            :post-id="actualPostId"
                            :reposts-count="post.reposts_count || 0"
                            :is-reposted="isReposted"
                            :can-repost="!!page.props.auth?.user"
                        />
                        <BookmarkButton
                            v-if="page.props.auth?.user"
                            :post-id="actualPostId"
                            :is-bookmarked="isBookmarked"
                            :can-bookmark="!!page.props.auth?.user"
                        />
                        <Link
                            :href="route('posts.show', actualPostId)"
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

