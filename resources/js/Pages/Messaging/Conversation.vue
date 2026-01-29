<template>
    <Head :title="conversation.display_name || 'Conversation'" />
    
    <MessagingLayout>
        <div class="flex h-full">
            <!-- Conversation List -->
            <div :class="['border-r border-gray-200 flex flex-col', showSidebar ? 'w-full md:w-1/3' : 'hidden md:flex md:w-1/3']" aria-label="Sidebar conversations">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h1 class="text-xl font-bold">Messages</h1>
                        <button class="md:hidden px-2 py-1 text-sm bg-gray-100 rounded" @click="toggleSidebar" aria-label="Toggle sidebar">
                            {{ showSidebar ? 'Hide' : 'Show' }}
                        </button>
                    </div>
                </div>
                <ConversationList :conversations="conversations" :active-conversation-id="conversation.id" />
            </div>

            <!-- Conversation View -->
            <div class="flex-1 flex flex-col">
                <ConversationHeader :conversation="conversation" />
                <div class="flex-1 overflow-y-auto">
                    <div class="px-4 pt-2">
                      <div class="inline-flex rounded-md shadow-sm border" role="tablist" aria-label="View tabs">
                        <button :class="['px-3 py-1 text-sm', activeTab==='chat' ? 'bg-blue-600 text-white' : 'bg-white']" @click="activeTab='chat'" role="tab" :aria-selected="activeTab==='chat'">Chat</button>
                        <button :class="['px-3 py-1 text-sm', activeTab==='call' ? 'bg-blue-600 text-white' : 'bg-white']" @click="activeTab='call'" role="tab" :aria-selected="activeTab==='call'">Call</button>
                      </div>
                    </div>
                    <div v-if="activeTab==='chat'">
                      <MessageList :messages="realTimeMessages" :conversation="conversation" :autoPlayEnabled="autoPlayEnabled" :lastReadAt="lastReadAt" />
                      <TypingIndicator :typing-users="typingUsers" />
                    </div>
                    <TypingIndicator :typing-users="typingUsers" />
                    <div class="p-4 border-t border-gray-200">
                        <div v-if="activeTab==='call'">
                          <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold">Video Call</h3>
                          </div>
                          <div class="mt-2">
                            <ErrorBoundary>
                              <CallPanel :conversationId="conversation.id" :iceServers="iceServers" :currentUserId="currentUserId" />
                              <template #fallback>
                                <div class="p-4 text-red-600">Call panel failed to load.</div>
                              </template>
                            </ErrorBoundary>
                            <DevSimulator />
                            <MetricsDashboard :conversationId="conversation.id" :sessionId="sessionId" />
                          </div>
                        </div>
                    </div>
                </div>
                <MessageInput :conversation="conversation" />
                <nav class="md:hidden fixed bottom-0 inset-x-0 bg-white border-t p-2 flex items-center justify-around" aria-label="Bottom actions">
                  <button class="p-2" aria-label="Messages">💬</button>
                  <button class="p-2" aria-label="Call" @click="activeTab='call'">📞</button>
                  <button class="p-2" aria-label="Menu" @click="toggleSidebar">☰</button>
                </nav>
            </div>
        </div>
    </MessagingLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
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
const sessionId = typeof window !== 'undefined' ? (window.__currentCallSessionId || null) : null;
const showSidebar = ref(true);
const activeTab = ref('chat');
const toggleSidebar = () => { showSidebar.value = !showSidebar.value; };
const lastReadAt = usePage().props.lastReadAt || null;
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

