<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

// Posts/Show page - displays individual post with comments and validation
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';
import VoteButton from '@/Components/VoteButton.vue';
import CommentThread from '@/Components/CommentThread.vue';
import Textarea from '@/Components/Textarea.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import IdeaValidationForm from '@/Components/IdeaValidationForm.vue';
import ValidationResults from '@/Components/ValidationResults.vue';
import { PURPOSE_TYPES } from '@/Utils/constants';

const props = defineProps({
    post: Object,
    auth: Object,
    userVote: String,
    validationStats: Object,
    userValidation: Object,
});

const commentForm = useForm({
    content: '',
});

const submitComment = () => {
    commentForm.post(route('comments.store', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
        },
    });
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
                        <div class="flex items-center gap-2 mb-4">
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
                                {{ new Date(post.created_at).toLocaleDateString('id-ID', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                }) }}
                            </span>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                            {{ post.title }}
                        </h1>

                        <div class="prose dark:prose-invert max-w-none mb-6">
                            <p class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">
                                {{ post.content }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                            <VoteButton
                                :post-id="post.id"
                                :upvotes="post.upvotes_count"
                                :downvotes="post.downvotes_count"
                                :user-vote="userVote"
                                :can-vote="auth?.user && auth.user.id !== post.user_id"
                            />
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

