<template>
    <div class="flex-1 overflow-y-auto">
        <div v-if="conversations.data && conversations.data.length > 0">
            <ConversationItem
                v-for="conversation in conversations.data"
                :key="conversation.id"
                :conversation="conversation"
                :active="conversation.id === activeConversationId"
            />
        </div>
        <div v-else class="p-4 text-center text-gray-500">
            <p>No conversations yet</p>
        </div>
    </div>
</template>

<script setup>
import ConversationItem from './ConversationItem.vue';

defineProps({
    conversations: Object,
    activeConversationId: String,
});
</script>

