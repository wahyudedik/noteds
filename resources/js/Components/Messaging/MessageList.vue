<template>
    <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messageListRef">
        <div v-if="displayMessages && displayMessages.length > 0">
            <MessageItem
                v-for="message in displayMessages"
                :key="message.id"
                :message="message"
                :conversation="conversation"
                :autoPlayEnabled="autoPlayEnabled"
            />
        </div>
        <div v-else class="text-center text-gray-500 py-8">
            <p>No messages yet. Start the conversation!</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import MessageItem from './MessageItem.vue';

const props = defineProps({
    messages: {
        type: [Object, Array],
        default: () => [],
    },
    conversation: Object,
    autoPlayEnabled: {
        type: Boolean,
        default: false,
    },
});

const messageListRef = ref(null);

const displayMessages = computed(() => {
    if (Array.isArray(props.messages)) {
        return props.messages;
    }
    return props.messages?.data || [];
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messageListRef.value) {
            messageListRef.value.scrollTop = messageListRef.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

watch(() => displayMessages.value, () => {
    scrollToBottom();
}, { deep: true });
</script>

