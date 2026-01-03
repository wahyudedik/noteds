<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Posts/Show page - displays individual post with comments and validation
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import { ref } from 'vue';
import VoteButton from '@/Components/VoteButton.vue';
import BookmarkButton from '@/Components/Bookmark/BookmarkButton.vue';
import ReportButton from '@/Components/Report/ReportButton.vue';
import CommentThread from '@/Components/CommentThread.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import IdeaValidationForm from '@/Components/IdeaValidationForm.vue';
import ValidationResults from '@/Components/ValidationResults.vue';
import LinkPreview from '@/Components/LinkPreview.vue';
import ImageGallery from '@/Components/ImageGallery.vue';
import { PURPOSE_TYPES } from '@/Utils/constants';

const props = defineProps({
    post: Object,
    auth: Object,
    userVote: String,
    isBookmarked: {
        type: Boolean,
        default: false,
    },
    validationStats: Object,
    userValidation: Object,
});

const commentForm = useForm({
    content: '',
});

const showImageGallery = ref(false);
const selectedImageIndex = ref(0);

const openImageGallery = (index) => {
    selectedImageIndex.value = index;
    showImageGallery.value = true;
};

const submitComment = () => {
    commentForm.post(route('comments.store', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
        },
    });
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
    <Head :title="post.title" />

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
                                    :user-vote="userVote"
                                    :can-vote="auth?.user && auth.user.id !== post.user_id"
                                />
                                <BookmarkButton
                                    v-if="auth?.user"
                                    :post-id="post.id"
                                    :is-bookmarked="isBookmarked"
                                    :can-bookmark="!!auth?.user"
                                />
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                💬 {{ post.comments_count }} komentar
                            </span>
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

                        <!-- Comments section -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                Komentar ({{ post.comments_count }})
                            </h3>

                            <!-- Comment Form -->
                            <div v-if="auth?.user" class="mb-6">
                                <form @submit.prevent="submitComment">
                                    <Textarea
                                        v-model="commentForm.content"
                                        class="w-full mb-2"
                                        rows="4"
                                        placeholder="Tulis komentar kamu di sini..."
                                        required
                                    />
                                    <InputError :message="commentForm.errors.content" />
                                    <PrimaryButton :disabled="commentForm.processing">
                                        Post Komentar
                                    </PrimaryButton>
                                </form>
                            </div>

                            <!-- Comments Thread -->
                            <div v-if="post.comments && post.comments.length > 0">
                                <CommentThread
                                    :comments="post.comments"
                                    :post-id="post.id"
                                    :post-author-id="post.user_id"
                                    :auth="auth"
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

