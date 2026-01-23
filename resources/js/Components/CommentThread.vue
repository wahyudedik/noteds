<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ReportButton from '@/Components/Report/ReportButton.vue';
import CommentRichTextEditor from '@/Components/CommentRichTextEditor.vue';
import CommentReactions from '@/Components/CommentReactions.vue';
import FileUploader from '@/Components/FileUploader.vue';
import VoteReasonSelector from '@/Components/VoteReasonSelector.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    comments: Array,
    postId: String,
    postAuthorId: String,
    auth: Object,
    useWeighted: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['viewCommentAnalytics']);

const replyingTo = ref(null);
const editingComment = ref(null);
const replyForms = ref({});
const editForms = ref({});
const showEditHistory = ref({});
const attachments = ref({});

// Vote reason selector state
const showVoteReasonSelector = ref(false);
const pendingVoteCommentId = ref(null);
const pendingVoteType = ref(null);
const selectedVoteReason = ref(null);

const startVote = (commentId, voteType) => {
    if (!props.auth?.user) return;

    const comment = props.comments.find(c => c.id === commentId);
    if (comment && comment.user_id === props.auth.user.id) return;

    pendingVoteCommentId.value = commentId;
    pendingVoteType.value = voteType;
    showVoteReasonSelector.value = true;
};

const confirmVote = (reason) => {
    router.post(route('votes.comment', pendingVoteCommentId.value), {
        vote_type: pendingVoteType.value,
        reason: reason,
    }, {
        preserveScroll: true,
    });

    showVoteReasonSelector.value = false;
    pendingVoteCommentId.value = null;
    pendingVoteType.value = null;
    selectedVoteReason.value = null;
};

const cancelVote = () => {
    showVoteReasonSelector.value = false;
    pendingVoteCommentId.value = null;
    pendingVoteType.value = null;
    selectedVoteReason.value = null;
};

const getDisplayUpvotes = (comment) => {
    if (props.useWeighted && comment.weighted_upvotes_score !== undefined && comment.weighted_upvotes_score !== null) {
        // Convert string to number if needed
        const value = typeof comment.weighted_upvotes_score === 'string' 
            ? parseFloat(comment.weighted_upvotes_score) 
            : comment.weighted_upvotes_score;
        return isNaN(value) ? 0 : value;
    }
    return comment.upvotes_count || 0;
};

const getDisplayDownvotes = (comment) => {
    if (props.useWeighted && comment.weighted_downvotes_score !== undefined && comment.weighted_downvotes_score !== null) {
        // Convert string to number if needed
        const value = typeof comment.weighted_downvotes_score === 'string' 
            ? parseFloat(comment.weighted_downvotes_score) 
            : comment.weighted_downvotes_score;
        return isNaN(value) ? 0 : value;
    }
    return comment.downvotes_count || 0;
};

const formatVoteCount = (num) => {
    // Handle null, undefined, or invalid values
    if (num === null || num === undefined || num === '') {
        return '0';
    }
    
    // Convert string to number if needed
    const number = typeof num === 'string' ? parseFloat(num) : num;
    
    // Check if conversion was successful
    if (isNaN(number)) {
        return '0';
    }
    
    if (number >= 1000) {
        return (number / 1000).toFixed(1) + 'k';
    }
    
    // Check if it's an integer or decimal
    return Number.isInteger(number) ? number.toString() : number.toFixed(1);
};

const viewCommentAnalytics = (commentId) => {
    emit('viewCommentAnalytics', commentId);
};

const startReply = (commentId) => {
    replyingTo.value = commentId;
    if (!replyForms.value[commentId]) {
        replyForms.value[commentId] = useForm({
            content: '',
            parent_id: commentId,
            attachments: [],
        });
        attachments.value[commentId] = [];
    }
};

const submitReply = (commentId) => {
    const form = replyForms.value[commentId];
    form.attachments = attachments.value[commentId]?.map(f => f.file) || [];
    form.post(route('comments.store', props.postId), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            replyingTo.value = null;
            form.reset();
            attachments.value[commentId] = [];
        },
    });
};

const cancelReply = (commentId) => {
    replyingTo.value = null;
    if (replyForms.value[commentId]) {
        replyForms.value[commentId].reset();
    }
    attachments.value[commentId] = [];
};

const startEdit = (comment) => {
    editingComment.value = comment.id;
    if (!editForms.value[comment.id]) {
        editForms.value[comment.id] = useForm({
            content: comment.content,
        });
    }
};

const submitEdit = (commentId) => {
    const form = editForms.value[commentId];
    form.put(route('comments.update', commentId), {
        preserveScroll: true,
        onSuccess: () => {
            editingComment.value = null;
        },
    });
};

const cancelEdit = (commentId) => {
    editingComment.value = null;
    if (editForms.value[commentId]) {
        editForms.value[commentId].reset();
    }
};

const markBestAnswer = (commentId) => {
    router.post(route('comments.best-answer', commentId), {}, {
        preserveScroll: true,
    });
};

const pinComment = (commentId) => {
    router.post(route('comments.pin', commentId), {}, {
        preserveScroll: true,
    });
};

const unpinComment = (commentId) => {
    router.post(route('comments.unpin', commentId), {}, {
        preserveScroll: true,
    });
};

const toggleEditHistory = async (commentId) => {
    if (showEditHistory.value[commentId]) {
        showEditHistory.value[commentId] = false;
    } else {
        showEditHistory.value[commentId] = true;
        // Load history if not loaded
        const comment = props.comments.find(c => c.id === commentId);
        if (comment && !comment.edit_history) {
            try {
                const response = await fetch(route('comments.history', commentId), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const history = await response.json();
                comment.edit_history = history;
            } catch (error) {
                console.error('Error loading edit history:', error);
            }
        }
    }
};

const isPostAuthor = computed(() => {
    return props.auth?.user?.id === props.postAuthorId;
});
const page = usePage();
const blockedIds = computed(() => {
    const v = page.props.blocked_user_ids || [];
    return Array.isArray(v) ? v : Object.values(v);
});
const filteredComments = computed(() => {
    const base = Array.isArray(props.comments) ? props.comments : [];
    return base
        .filter(c => !blockedIds.value.includes(c.user_id))
        .map(c => ({
            ...c,
            replies: (c.replies || []).filter(r => !blockedIds.value.includes(r.user_id)),
        }));
});

const iconFor = (mime, name) => {
    const ext = (name || '').toLowerCase();
    if ((mime || '').startsWith('image/')) return 'image';
    if (ext.endsWith('.pdf')) return 'pdf';
    if (ext.endsWith('.doc') || ext.endsWith('.docx')) return 'word';
    if (ext.endsWith('.xls') || ext.endsWith('.xlsx') || ext.endsWith('.csv')) return 'excel';
    if (ext.endsWith('.ppt') || ext.endsWith('.pptx')) return 'ppt';
    if (ext.endsWith('.txt')) return 'txt';
    if (ext.endsWith('.zip') || ext.endsWith('.rar')) return 'archive';
    return 'file';
};
const iconSvg = (type) => {
    switch (type) {
        case 'pdf': return 'M7 7v10a2 2 0 002 2h6a2 2 0 002-2V7m-8 0V5a2 2 0 012-2h2a2 2 0 012 2v2';
        case 'word': return 'M4 4h16v16H4z M7 7h10v10H7z';
        case 'excel': return 'M4 4h16v16H4z M8 8l8 8M16 8l-8 8';
        case 'ppt': return 'M12 7a5 5 0 110 10 5 5 0 010-10M7 7h10v10H7z';
        case 'txt': return 'M6 4h12v16H6z M8 8h8M8 12h8M8 16h8';
        case 'archive': return 'M6 4h12v16H6z M12 4v16M9 7h6M9 11h6M9 15h6';
        default: return 'M7 7v10a2 2 0 002 2h6a2 2 0 002-2V7m-8 0V5a2 2 0 012-2h2a2 2 0 012 2v2';
    }
};
const sizeText = (n) => {
    if (!n && n !== 0) return '';
    const kb = n / 1024;
    if (kb < 1024) return `${Math.round(kb)} KB`;
    const mb = kb / 1024;
    return `${mb.toFixed(1)} MB`;
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="comment in filteredComments"
            :key="comment.id"
            :class="[
                'border rounded-lg p-4',
                comment.is_pinned
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : comment.is_best_answer
                    ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                    : 'border-gray-200 dark:border-gray-700'
            ]"
        >
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2 flex-wrap">
                    <Link
                        :href="route('profile.show', comment.user.id)"
                        class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-semibold hover:ring-2 hover:ring-indigo-300 transition overflow-hidden flex-shrink-0"
                    >
                        <img
                            v-if="comment.user.avatar_url"
                            :src="comment.user.avatar_url"
                            :alt="comment.user.business_name || comment.user.name"
                            class="w-full h-full object-cover"
                        />
                        <span v-else>
                            {{ (comment.user.business_name || comment.user.name).charAt(0).toUpperCase() }}
                        </span>
                    </Link>
                    <Link
                        :href="route('profile.show', comment.user.id)"
                        class="font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        {{ comment.user.business_name || comment.user.name }}
                    </Link>
                    <span
                        v-if="comment.is_pinned"
                        class="text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded flex items-center gap-1"
                    >
                        📌 Pinned
                    </span>
                    <span
                        v-if="comment.is_best_answer"
                        class="text-xs font-medium text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900 px-2 py-1 rounded"
                    >
                        Best Answer
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ formatDate(comment.created_at) }}
                    </span>
                    <span
                        v-if="comment.edited_at"
                        class="text-xs text-gray-400 dark:text-gray-500 italic"
                        title="Edited"
                    >
                        (edited)
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        v-if="isPostAuthor && !comment.is_pinned"
                        @click="pinComment(comment.id)"
                        class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        title="Pin comment"
                    >
                        📌
                    </button>
                    <button
                        v-if="isPostAuthor && comment.is_pinned"
                        @click="unpinComment(comment.id)"
                        class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        title="Unpin comment"
                    >
                        📌
                    </button>
                    <button
                        v-if="auth?.user && auth.user.id === comment.user_id && !editingComment"
                        @click="startEdit(comment)"
                        class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        Edit
                    </button>
                    <button
                        v-if="comment.edit_count > 0"
                        @click="toggleEditHistory(comment.id)"
                        class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                        title="View edit history"
                    >
                        History
                    </button>
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

            <!-- Edit Form -->
            <div v-if="editingComment === comment.id" class="mb-3">
                <CommentRichTextEditor
                    v-model="editForms[comment.id].content"
                    placeholder="Edit your comment..."
                />
                <InputError :message="editForms[comment.id].errors.content" />
                <div class="flex gap-2 mt-2">
                    <PrimaryButton @click="submitEdit(comment.id)" :disabled="editForms[comment.id].processing">
                        Save
                    </PrimaryButton>
                    <button
                        @click="cancelEdit(comment.id)"
                        class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Comment Content -->
            <div v-else>
                <div
                    class="text-gray-700 dark:text-gray-300 mb-3 prose prose-sm max-w-none"
                    v-html="comment.content"
                ></div>

                <!-- Comment Media -->
                <div v-if="comment.media && comment.media.length > 0" class="mb-3">
                    <div class="grid grid-cols-2 gap-2">
                        <template v-for="media in comment.media" :key="media.id">
                            <img
                                v-if="media.mime_type?.startsWith('image/')"
                                :src="media.url"
                                :alt="media.file_name"
                                class="rounded-lg max-w-full h-auto object-cover cursor-pointer hover:opacity-90"
                                @click="window.open(media.url, '_blank')"
                            />
                            <a
                                v-else
                                :href="media.url"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center gap-2 rounded-md border border-gray-200 dark:border-gray-700 p-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                <img
                                    v-if="media.thumbnail_url"
                                    :src="media.thumbnail_url"
                                    :alt="media.file_name"
                                    class="w-10 h-10 object-cover rounded"
                                />
                                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconSvg(iconFor(media.mime_type, media.file_name))" />
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ media.file_name }}</span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 ml-auto">{{ sizeText(media.file_size) }}</span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Edit History -->
            <div v-if="showEditHistory[comment.id] && comment.edit_history" class="mb-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <h4 class="text-sm font-semibold mb-2 text-gray-900 dark:text-gray-100">Edit History</h4>
                <div class="space-y-2 text-xs">
                    <div
                        v-for="(history, index) in comment.edit_history"
                        :key="history.id"
                        class="border-l-2 border-gray-300 dark:border-gray-600 pl-2"
                    >
                        <p class="text-gray-600 dark:text-gray-400">{{ formatDate(history.edited_at) }}</p>
                        <p class="text-gray-700 dark:text-gray-300 line-clamp-2">{{ history.content }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        @click="startVote(comment.id, 'upvote')"
                        :disabled="!auth?.user || auth.user.id === comment.user_id"
                        :class="[
                            'flex items-center gap-1 px-2 py-1 rounded text-sm transition',
                            'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                            (!auth?.user || auth.user.id === comment.user_id) && 'opacity-50 cursor-not-allowed'
                        ]"
                    >
                        👍 {{ formatVoteCount(getDisplayUpvotes(comment)) }}
                    </button>
                    <button
                        @click="startVote(comment.id, 'downvote')"
                        :disabled="!auth?.user || auth.user.id === comment.user_id"
                        :class="[
                            'flex items-center gap-1 px-2 py-1 rounded text-sm transition',
                            'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                            (!auth?.user || auth.user.id === comment.user_id) && 'opacity-50 cursor-not-allowed'
                        ]"
                    >
                        👎 {{ formatVoteCount(getDisplayDownvotes(comment)) }}
                    </button>
                    <!-- View Analytics button (author only) -->
                    <button
                        v-if="auth?.user && auth.user.id === comment.user_id"
                        @click="viewCommentAnalytics(comment.id)"
                        class="flex items-center gap-1 px-2 py-1 rounded text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                        title="View vote analytics"
                    >
                        📊
                    </button>
                    <CommentReactions
                        v-if="comment.reactions"
                        :comment="comment"
                        :current-user="auth?.user"
                        @reaction-updated="(reactions) => { comment.reactions = reactions; }"
                    />
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
                    <CommentRichTextEditor
                        v-model="replyForms[comment.id].content"
                        placeholder="Write a reply..."
                    />
                    <InputError :message="replyForms[comment.id].errors.content" />
                    <div class="mt-2">
                        <FileUploader
                            v-model="attachments[comment.id]"
                            :max-files="5"
                            :max-size-kb="10240"
                        />
                    </div>
                    <InputError :message="replyForms[comment.id].errors.attachments" />
                    <div class="flex gap-2 mt-2">
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
                            {{ formatDate(reply.created_at) }}
                        </span>
                    </div>
                    <div
                        class="text-sm text-gray-700 dark:text-gray-300 prose prose-sm max-w-none"
                        v-html="reply.content"
                    ></div>
                    <div v-if="reply.media && reply.media.length > 0" class="mt-2 grid grid-cols-2 gap-2">
                        <template v-for="media in reply.media" :key="media.id">
                            <img
                                v-if="media.mime_type?.startsWith('image/')"
                                :src="media.url"
                                :alt="media.file_name"
                                class="rounded-lg max-w-xs h-auto object-cover cursor-pointer hover:opacity-90"
                                @click="window.open(media.url, '_blank')"
                            />
                            <a
                                v-else
                                :href="media.url"
                                target="_blank"
                                rel="noopener"
                                class="flex items-center gap-2 rounded-md border border-gray-200 dark:border-gray-700 p-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                            >
                                <img
                                    v-if="media.thumbnail_url"
                                    :src="media.thumbnail_url"
                                    :alt="media.file_name"
                                    class="w-10 h-10 object-cover rounded"
                                />
                                <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconSvg(iconFor(media.mime_type, media.file_name))" />
                                </svg>
                                <span class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ media.file_name }}</span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 ml-auto">{{ sizeText(media.file_size) }}</span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vote Reason Selector Modal -->
    <VoteReasonSelector
        v-if="pendingVoteType"
        v-model="selectedVoteReason"
        v-model:show="showVoteReasonSelector"
        :vote-type="pendingVoteType"
        @confirm="confirmVote"
        @cancel="cancelVote"
    />
</template>
