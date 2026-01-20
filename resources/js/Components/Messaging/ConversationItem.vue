<template>
    <Link
        :href="route('messaging.conversations.show', conversation.id)"
        :class="[
            'block p-4 border-b border-gray-200 hover:bg-gray-50 transition',
            active ? 'bg-blue-50 border-l-4 border-l-blue-600' : ''
        ]"
    >
        <div class="flex items-center space-x-3">
            <img
                :src="conversation.display_avatar || '/default-avatar.png'"
                :alt="conversation.display_name"
                class="w-12 h-12 rounded-full"
            />
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ conversation.display_name }}
                    </p>
                    <span v-if="conversation.unread_count > 0" class="ml-2 bg-blue-600 text-white text-xs rounded-full px-2 py-1">
                        {{ conversation.unread_count }}
                    </span>
                </div>
                <p v-if="lastMessage" class="text-sm text-gray-500 truncate">
                    {{ lastMessagePreview }}
                </p>
                <p v-if="lastMessage" class="text-xs text-gray-400 mt-1">
                    {{ formatTime(lastMessage.created_at) }}
                </p>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
    active: Boolean,
});

const lastMessage = computed(() => {
    return props.conversation.messages?.data?.[0] || props.conversation.last_message;
});

const lastMessagePreview = computed(() => {
    if (!lastMessage.value) return '';
    
    if (lastMessage.value.content) {
        return lastMessage.value.content.length > 50
            ? lastMessage.value.content.substring(0, 50) + '...'
            : lastMessage.value.content;
    }
    
    if (lastMessage.value.type === 'image') return '📷 Image';
    if (lastMessage.value.type === 'file') return '📎 File';
    if (lastMessage.value.type === 'voice') return '🎤 Voice message';
    
    return 'Message';
});

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diff = now - d;
    const minutes = Math.floor(diff / 60000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (minutes < 1440) return `${Math.floor(minutes / 60)}h ago`;
    return d.toLocaleDateString();
};
</script>

