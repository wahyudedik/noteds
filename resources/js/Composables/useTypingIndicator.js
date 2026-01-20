import { ref, onMounted, onUnmounted } from 'vue';
import Echo from '@/Utils/echo';

export function useTypingIndicator(conversationId) {
    const typingUsers = ref([]);
    let channel = null;
    let typingTimeout = null;

    const listenForTyping = () => {
        if (!conversationId || !window.Echo) return;

        channel = window.Echo.private(`conversation.${conversationId}`)
            .listen('.typing.started', (e) => {
                // Only add if not already in the list
                if (!typingUsers.value.find(u => u.user_id === e.user_id)) {
                    typingUsers.value.push(e.user);
                }
                // Auto-remove after 5 seconds if no stop event
                clearTimeout(typingTimeout);
                typingTimeout = setTimeout(() => {
                    typingUsers.value = typingUsers.value.filter(u => u.id !== e.user_id);
                }, 5000);
            })
            .listen('.typing.stopped', (e) => {
                typingUsers.value = typingUsers.value.filter(u => u.id !== e.user_id);
                clearTimeout(typingTimeout);
            });
    };

    const stopListening = () => {
        if (!conversationId || !window.Echo || !channel) return;
        
        window.Echo.leave(`conversation.${conversationId}`);
        channel = null;
        typingUsers.value = [];
        clearTimeout(typingTimeout);
    };

    onMounted(() => {
        listenForTyping();
    });

    onUnmounted(() => {
        stopListening();
    });

    return {
        typingUsers,
        listenForTyping,
        stopListening,
    };
}

