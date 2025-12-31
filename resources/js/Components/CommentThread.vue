<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ReportButton from '@/Components/Report/ReportButton.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    comments: Array,
    postId: String,
    postAuthorId: String,
    auth: Object,
});

const replyingTo = ref(null);
const replyForms = ref({});

const startReply = (commentId) => {
    replyingTo.value = commentId;
    if (!replyForms.value[commentId]) {
        replyForms.value[commentId] = useForm({
            content: '',
            parent_id: commentId,
        });
    }
};

const submitReply = (commentId) => {
    const form = replyForms.value[commentId];
    form.post(route('comments.store', props.postId), {
        preserveScroll: true,
        onSuccess: () => {
            replyingTo.value = null;
            form.reset();
        },
    });
};

const cancelReply = (commentId) => {
    replyingTo.value = null;
    if (replyForms.value[commentId]) {
        replyForms.value[commentId].reset();
    }
};

const markBestAnswer = (commentId) => {
    router.post(route('comments.best-answer', commentId), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="comment in comments"
            :key="comment.id"
            :class="[
                'border rounded-lg p-4',
                comment.is_best_answer
                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                    : 'border-gray-200 dark:border-gray-700'
            ]"
        >
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('profile.show', comment.user.id)"
                        class="font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        {{ comment.user.business_name || comment.user.name }}
                    </Link>
                    <span
                        v-if="comment.is_best_answer"
                        class="text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900 px-2 py-1 rounded"
                    >
                        Best Answer
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ new Date(comment.created_at).toLocaleDateString('id-ID') }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        v-if="auth?.user && auth.user.id === postAuthorId && !comment.is_best_answer"
                        @click="markBestAnswer(comment.id)"
                        class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                        Mark as Best Answer
                    </button>
                    <ReportButton
                        v-if="auth?.user && auth.user.id !== comment.user_id"
                        reportable-type="comment"
                        :reportable-id="comment.id"
                        variant="icon"
                    />
                </div>
            </div>

            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap mb-3">
                {{ comment.content }}
            </p>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button
                        @click="$event => router.post(route('votes.comment', comment.id), { vote_type: 'upvote' }, { preserveScroll: true })"
                        :disabled="!auth?.user || auth.user.id === comment.user_id"
                        :class="[
                            'flex items-center gap-1 px-2 py-1 rounded text-sm transition',
                            'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                            (!auth?.user || auth.user.id === comment.user_id) && 'opacity-50 cursor-not-allowed'
                        ]"
                    >
                        👍 {{ comment.upvotes_count }}
                    </button>
                    <button
                        @click="$event => router.post(route('votes.comment', comment.id), { vote_type: 'downvote' }, { preserveScroll: true })"
                        :disabled="!auth?.user || auth.user.id === comment.user_id"
                        :class="[
                            'flex items-center gap-1 px-2 py-1 rounded text-sm transition',
                            'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                            (!auth?.user || auth.user.id === comment.user_id) && 'opacity-50 cursor-not-allowed'
                        ]"
                    >
                        👎 {{ comment.downvotes_count }}
                    </button>
                </div>

                <button
                    v-if="auth?.user"
                    @click="startReply(comment.id)"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    Reply
                </button>
            </div>

            <!-- Reply Form -->
            <div v-if="replyingTo === comment.id" class="mt-4 border-t pt-4">
                <form @submit.prevent="submitReply(comment.id)">
                    <Textarea
                        v-model="replyForms[comment.id].content"
                        class="w-full mb-2"
                        rows="3"
                        placeholder="Write a reply..."
                    />
                    <InputError :message="replyForms[comment.id].errors.content" />
                    <div class="flex gap-2">
                        <PrimaryButton type="submit" :disabled="replyForms[comment.id].processing">
                            Post Reply
                        </PrimaryButton>
                        <button
                            type="button"
                            @click="cancelReply(comment.id)"
                            class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Nested Replies -->
            <div v-if="comment.replies && comment.replies.length > 0" class="mt-4 ml-6 space-y-3 border-l-2 border-gray-200 dark:border-gray-700 pl-4">
                <div
                    v-for="reply in comment.replies"
                    :key="reply.id"
                    class="border rounded p-3 bg-gray-50 dark:bg-gray-800"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <Link
                            :href="route('profile.show', reply.user.id)"
                            class="font-semibold text-sm text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400"
                        >
                            {{ reply.user.business_name || reply.user.name }}
                        </Link>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            {{ new Date(reply.created_at).toLocaleDateString('id-ID') }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {{ reply.content }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

