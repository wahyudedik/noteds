<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

// Posts/Show page - displays individual post with comments and validation
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import { ref, computed } from 'vue';
import VoteButton from '@/Components/VoteButton.vue';
import BookmarkButton from '@/Components/Bookmark/BookmarkButton.vue';
import RepostButton from '@/Components/Repost/RepostButton.vue';
import ReportButton from '@/Components/Report/ReportButton.vue';
import CommentThread from '@/Components/CommentThread.vue';
import CommentRichTextEditor from '@/Components/CommentRichTextEditor.vue';
import FileUploader from '@/Components/FileUploader.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import IdeaValidationForm from '@/Components/IdeaValidationForm.vue';
import ValidationResults from '@/Components/ValidationResults.vue';
import LinkPreview from '@/Components/LinkPreview.vue';
import ImageGallery from '@/Components/ImageGallery.vue';
import WeightedScoreToggle from '@/Components/WeightedScoreToggle.vue';
import SupplierRecommendations from '@/Components/SupplierRecommendations.vue';
import { PURPOSE_TYPES } from '@/Utils/constants';
import RelatedPosts from '@/Components/Recommendations/RelatedPosts.vue';
import YouMightLike from '@/Components/Recommendations/YouMightLike.vue';
import SocialShareButtons from '@/Components/Social/SocialShareButtons.vue';

const props = defineProps({
    post: Object,
    auth: Object,
    userVote: String,
    isBookmarked: {
        type: Boolean,
        default: false,
    },
    isReposted: {
        type: Boolean,
        default: false,
    },
    validationStats: Object,
    userValidation: Object,
    supplierRecommendations: Array,
    businessType: String,
});

const commentForm = useForm({
    content: '',
    attachments: [],
});

const commentAttachments = ref([]);

const showImageGallery = ref(false);
const selectedImageIndex = ref(0);

// Weighted score toggle
const useWeighted = ref(false);

// Check if current user is post author
const isPostAuthor = computed(() => {
    return props.auth?.user?.id === props.post?.user_id;
});

// Navigate to vote analytics
const viewPostAnalytics = () => {
    router.visit(route('votes.post.analytics', props.post.id));
};

const viewCommentAnalytics = (commentId) => {
    router.visit(route('votes.comment.analytics', commentId));
};

const openImageGallery = (index) => {
    selectedImageIndex.value = index;
    showImageGallery.value = true;
};

const submitComment = () => {
    commentForm.attachments = commentAttachments.value.map(f => f.file);
    commentForm.post(route('comments.store', props.post.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            commentForm.reset();
            commentAttachments.value = [];
        },
    });
};
// UI reinforcement: permissions from server
const canComment = computed(() => {
    return (props.permissions?.can_comment ?? true) === true;
});

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
    <Head :title="post.title">
        <meta name="description" :content="post.excerpt || ''" />
        <meta property="og:title" :content="post.title" />
        <meta property="og:description" :content="post.excerpt || ''" />
        <meta property="og:url" :content="route('posts.show', post.id)" />
        <meta property="og:type" content="article" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="post.title" />
        <meta name="twitter:description" :content="post.excerpt || ''" />
    </Head>

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <Link
                    :href="route('posts.index')"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    ← Kembali ke Posts
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('profile.show', post.user.id)"
                                    class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold hover:ring-2 hover:ring-indigo-300 transition overflow-hidden flex-shrink-0"
                                >
                                    <img
                                        v-if="post.user.avatar_url"
                                        :src="post.user.avatar_url"
                                        :alt="post.user.business_name || post.user.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else>
                                        {{ (post.user.business_name || post.user.name).charAt(0).toUpperCase() }}
                                    </span>
                                </Link>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ PURPOSE_TYPE_LABELS[post.purpose_type] }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">•</span>
                                    <Link
                                        :href="route('profile.show', post.user.id)"
                                        class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 font-semibold"
                                    >
                                        {{ post.user.business_name || post.user.name }}
                                    </Link>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">•</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(post.created_at).toLocaleDateString('id-ID', { 
                                            year: 'numeric', 
                                            month: 'long', 
                                            day: 'numeric' 
                                        }) }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="auth?.user && auth.user.id !== post.user_id">
                                <ReportButton
                                    reportable-type="post"
                                    :reportable-id="post.id"
                                    variant="icon"
                                    size="sm"
                                />
                            </div>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                            {{ post.title }}
                        </h1>

                        <div class="prose dark:prose-invert max-w-none mb-6">
                            <p class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                                {{ post.content }}
                            </p>
                        </div>

                        <!-- Images Masonry Layout (Pinterest-style) -->
                        <div v-if="post.media && post.media.length > 0" class="mb-6">
                            <div
                                class="grid gap-3 rounded-lg overflow-hidden"
                                :style="getMasonryStyle(post.media.length)"
                            >
                                <div
                                    v-for="(media, index) in post.media"
                                    :key="media.id"
                                    :class="[
                                        'relative overflow-hidden bg-gray-100 dark:bg-gray-700 rounded-lg cursor-pointer hover:opacity-90 transition-opacity',
                                        getImageClass(post.media.length, index)
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
                        <div v-if="post.link_url" class="mb-6">
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


                        <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <VoteButton
                                    :post-id="post.id"
                                    :upvotes="post.upvotes_count"
                                    :downvotes="post.downvotes_count"
                                    :weighted-upvotes="post.weighted_upvotes_score"
                                    :weighted-downvotes="post.weighted_downvotes_score"
                                    :user-vote="userVote"
                                    :can-vote="props.permissions?.can_vote"
                                    :disabled-reason="props.restrictions?.vote || ''"
                                    :use-weighted="useWeighted"
                                    :is-author="isPostAuthor"
                                    @view-analytics="viewPostAnalytics"
                                />
                                <div class="flex items-center gap-2">
                                    <RepostButton
                                        v-if="auth?.user"
                                        :post-id="post.id"
                                        :reposts-count="post.reposts_count || 0"
                                        :is-reposted="isReposted"
                                        :can-repost="props.permissions?.can_repost"
                                        :disabled-reason="props.restrictions?.repost || ''"
                                    />
                                    <Link
                                        v-if="auth?.user && auth.user.id === post.user_id && post.reposts_count > 0"
                                        :href="route('reposts.analytics', post.id)"
                                        class="text-xs text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400"
                                        title="View Repost Analytics"
                                    >
                                        Analytics
                                    </Link>
                                </div>
                                <BookmarkButton
                                    v-if="auth?.user"
                                    :post-id="post.id"
                                    :is-bookmarked="isBookmarked"
                                        :can-bookmark="props.permissions?.can_bookmark"
                                        :disabled-reason="props.restrictions?.bookmark || ''"
                                />
                                <WeightedScoreToggle v-model="useWeighted" />
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                💬 {{ post.comments_count }} komentar
                            </span>
                        </div>

                        <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                            <SocialShareButtons
                                :url="route('posts.show', post.id)"
                                :title="post.title"
                                :description="post.excerpt || ''"
                                :hashtags="post.hashtags?.map(h => h.name) || []"
                                share-type="posts"
                                :share-id="post.id"
                            />
                        </div>

                        <!-- Idea Validation Section -->
                        <div v-if="post.purpose_type === PURPOSE_TYPES.VALIDATE_IDEA" class="mb-6 space-y-4">
                            <ValidationResults :stats="validationStats" />
                            
                            <div v-if="auth?.user && auth.user.id !== post.user_id">
                                <IdeaValidationForm
                                    :post-id="post.id"
                                    :user-validation="userValidation"
                                />
                            </div>
                        </div>

                        <!-- Supplier Recommendations -->
                        <SupplierRecommendations
                            v-if="supplierRecommendations"
                            :recommendations="supplierRecommendations"
                            :business-type="businessType"
                        />

                        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <RelatedPosts :post-id="post.id" />
                            </div>
                            <div>
                                <YouMightLike />
                            </div>
                        </div>

                        <!-- Comments section -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Komentar ({{ post.comments_count }})
                            </h3>

                            <!-- Comment Form -->
                            <div v-if="auth?.user" class="mb-6">
                                <div v-if="!canComment" class="mb-3 rounded-md border border-yellow-300 bg-yellow-50 p-3 text-yellow-800">
                                    Komentar dinonaktifkan atau hanya untuk followers sesuai pengaturan privasi penulis.
                                </div>
                                <form @submit.prevent="submitComment">
                                    <CommentRichTextEditor
                                        v-model="commentForm.content"
                                        placeholder="Tulis komentar kamu di sini..."
                                        :disabled="!canComment"
                                    />
                                    <InputError :message="commentForm.errors.content" />
                                    <div class="mt-2">
                                        <FileUploader
                                            v-model="commentAttachments"
                                            :max-files="5"
                                            :max-size-kb="10240"
                                        />
                                    </div>
                                    <InputError :message="commentForm.errors.attachments" />
                                    <div class="mt-2">
                                        <PrimaryButton :disabled="commentForm.processing || !canComment">
                                            Post Komentar
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>

                            <!-- Comments Thread -->
                            <div v-if="post.comments && post.comments.length > 0">
                                <CommentThread
                                    :comments="post.comments"
                                    :post-id="post.id"
                                    :post-author-id="post.user_id"
                                    :auth="auth"
                                    :use-weighted="useWeighted"
                                    @view-comment-analytics="viewCommentAnalytics"
                                />
                            </div>
                            <p v-else class="text-gray-500 dark:text-gray-400">
                                Belum ada komentar. Jadilah yang pertama berkomentar!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

