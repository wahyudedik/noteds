<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    comment: {
        type: Object,
        required: true,
    },
    currentUser: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['reaction-updated']);

const allowedEmojis = ['👍', '❤️', '😂', '🎉', '🔥', '💡', '👏', '🙌'];
const reacting = ref({});

const getReactionCount = (emoji) => {
    if (!props.comment.reactions) return 0;
    const reaction = props.comment.reactions.find(r => r.emoji === emoji);
    return reaction ? reaction.count : 0;
};

const hasReaction = (emoji) => {
    return getReactionCount(emoji) > 0;
};

const toggleReaction = async (emoji) => {
    if (reacting.value[emoji]) return;
    
    reacting.value[emoji] = true;
    
    try {
        const response = await fetch(route('comments.reactions.react', props.comment.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ emoji }),
        });

        const data = await response.json();

        if (data.success) {
            emit('reaction-updated', data.reactions);
        }
    } catch (error) {
        console.error('Error toggling reaction:', error);
    } finally {
        reacting.value[emoji] = false;
    }
};
</script>

<template>
    <div class="flex items-center gap-2 flex-wrap">
        <button
            v-for="emoji in allowedEmojis"
            :key="emoji"
            @click="toggleReaction(emoji)"
            :disabled="reacting[emoji]"
            :class="[
                'px-2 py-1 text-sm rounded-md transition flex items-center gap-1',
                hasReaction(emoji)
                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-700'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600',
                reacting[emoji] ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]"
            :title="emoji"
        >
            <span>{{ emoji }}</span>
            <span v-if="getReactionCount(emoji) > 0" class="text-xs font-medium">
                {{ getReactionCount(emoji) }}
            </span>
        </button>
    </div>
</template>

