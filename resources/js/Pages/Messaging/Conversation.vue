<template>
    <Head :title="conversation.display_name || 'Conversation'" />
    
    <MessagingLayout>
        <div class="flex h-full">
            <!-- Conversation List -->
            <div class="w-1/3 border-r border-gray-200 flex flex-col">
                <div class="p-4 border-b border-gray-200">
                    <h1 class="text-xl font-bold">Messages</h1>
                </div>
                <ConversationList :conversations="conversations" :active-conversation-id="conversation.id" />
            </div>

            <!-- Conversation View -->
            <div class="flex-1 flex flex-col">
                <ConversationHeader :conversation="conversation" />
                <div class="flex-1 overflow-y-auto">
                    <MessageList :messages="realTimeMessages" :conversation="conversation" />
                    <TypingIndicator :typing-users="typingUsers" />
                </div>
                <MessageInput :conversation="conversation" />
            </div>
        </div>
    </MessagingLayout>
</template>

<script setup>
import { computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import ConversationList from '@/Components/Messaging/ConversationList.vue';
import ConversationHeader from '@/Components/Messaging/ConversationHeader.vue';
import MessageList from '@/Components/Messaging/MessageList.vue';
import MessageInput from '@/Components/Messaging/MessageInput.vue';
import TypingIndicator from '@/Components/Messaging/TypingIndicator.vue';
import { useMessaging } from '@/Composables/useMessaging';
import { useTypingIndicator } from '@/Composables/useTypingIndicator';

const props = defineProps({
    conversation: Object,
    messages: Object,
    conversations: Object,
});

// Initialize real-time messaging
const { messages: realTimeMessages } = useMessaging(
    props.conversation.id,
    props.messages?.data || []
);

// Initialize typing indicators
const { typingUsers } = useTypingIndicator(props.conversation.id);

// Update messages when prop changes
watch(() => props.messages, (newMessages) => {
    if (newMessages?.data) {
        realTimeMessages.value = [...newMessages.data];
    }
}, { deep: true });
</script>

