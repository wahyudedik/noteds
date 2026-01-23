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
                    <MessageList :messages="realTimeMessages" :conversation="conversation" :autoPlayEnabled="autoPlayEnabled" />
                    <TypingIndicator :typing-users="typingUsers" />
                    <div class="p-4 border-t border-gray-200">
                        <h3 class="text-lg font-semibold mb-2">Video Call</h3>
                        <ErrorBoundary>
                          <CallPanel :conversationId="conversation.id" :iceServers="iceServers" :currentUserId="currentUserId" />
                          <template #fallback>
                            <div class="p-4 text-red-600">Call panel failed to load.</div>
                          </template>
                        </ErrorBoundary>
                        <DevSimulator />
                        <MetricsDashboard :conversationId="conversation.id" :sessionId="window.__currentCallSessionId || null" />
                    </div>
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
import CallPanel from '@/Components/Calls/CallPanel.vue';
import ErrorBoundary from '@/Components/Common/ErrorBoundary.vue';
import DevSimulator from '@/Components/Calls/DevSimulator.vue';
import MetricsDashboard from '@/Components/Monitoring/MetricsDashboard.vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
    messages: Object,
    conversations: Object,
    autoPlayEnabled: Boolean,
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

// Fetch conversation key for E2E encryption if available
const fetchConversationKey = async () => {
    try {
        const res = await fetch(route('secure.conversations.key.fetch', props.conversation.id), {
            credentials: 'include',
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.key_b64) {
            window.__conversationKey = data.key_b64;
        }
    } catch (e) {}
};
fetchConversationKey();

const page = usePage();
const currentUserId = page.props.auth?.user?.id || null;
const iceServers = [];
const fetchIce = async () => {
    try {
        const res = await fetch(route('rtc.ice'), { credentials: 'include', headers: { 'Accept': 'application/json' } });
        if (!res.ok) return;
        const data = await res.json();
        if (data.iceServers) {
            data.iceServers.forEach(s => iceServers.push(s));
        }
    } catch (e) {}
};
fetchIce();
</script>

