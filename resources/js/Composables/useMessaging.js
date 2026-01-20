import { ref, onMounted, onUnmounted } from 'vue';
import Echo from '@/Utils/echo';

export function useMessaging(conversationId, initialMessages = []) {
    const messages = ref([...initialMessages]);
    const isConnected = ref(false);
    let channel = null;

    const listenForMessages = () => {
        if (!conversationId || !window.Echo) return;

        channel = window.Echo.private(`conversation.${conversationId}`)
            .listen('.message.sent', (e) => {
                // Add new message to the end of the array (newest first in pagination)
                messages.value.unshift(e);
            })
            .listen('.message.edited', (e) => {
                const index = messages.value.findIndex(m => m.id === e.id);
                if (index !== -1) {
                    messages.value[index] = { ...messages.value[index], ...e, is_edited: true };
                }
            })
            .listen('.message.deleted', (e) => {
                const index = messages.value.findIndex(m => m.id === e.message_id);
                if (index !== -1) {
                    messages.value[index].is_deleted = true;
                }
            })
            .listen('.conversation.updated', (e) => {
                // Handle conversation updates (new participants, etc.)
                console.log('Conversation updated:', e);
            });

        isConnected.value = true;
    };

    const stopListening = () => {
        if (!conversationId || !window.Echo || !channel) return;
        
        window.Echo.leave(`conversation.${conversationId}`);
        channel = null;
        isConnected.value = false;
    };

    const addMessage = (message) => {
        messages.value.unshift(message);
    };

    const updateMessage = (messageId, updates) => {
        const index = messages.value.findIndex(m => m.id === messageId);
        if (index !== -1) {
            messages.value[index] = { ...messages.value[index], ...updates };
        }
    };

    const removeMessage = (messageId) => {
        const index = messages.value.findIndex(m => m.id === messageId);
        if (index !== -1) {
            messages.value[index].is_deleted = true;
        }
    };

    onMounted(() => {
        listenForMessages();
    });

    onUnmounted(() => {
        stopListening();
    });

    return {
        messages,
        isConnected,
        listenForMessages,
        stopListening,
        addMessage,
        updateMessage,
        removeMessage,
    };
}

