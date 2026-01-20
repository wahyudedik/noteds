<template>
    <div
        :class="[
            'flex',
            isOwnMessage ? 'justify-end' : 'justify-start'
        ]"
    >
        <div
            :class="[
                'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                isOwnMessage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-900'
            ]"
        >
            <div v-if="!isOwnMessage" class="text-xs font-semibold mb-1">
                {{ message.user.name }}
            </div>
            
            <div v-if="message.reply_to" class="mb-2 p-2 bg-black bg-opacity-20 rounded text-sm">
                <p class="font-semibold">{{ message.reply_to.user.name }}</p>
                <p class="truncate">{{ message.reply_to.content }}</p>
            </div>

            <div v-if="message.content" class="mb-2">
                {{ message.content }}
            </div>

            <MessageMedia v-if="message.media && message.media.length > 0" :media="message.media" />

            <div class="flex items-center justify-end space-x-2 mt-1 text-xs opacity-75">
                <span>{{ formatTime(message.created_at) }}</span>
                <span v-if="message.is_edited">(edited)</span>
                <ReadReceipt v-if="isOwnMessage" :message="message" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MessageMedia from './MessageMedia.vue';
import ReadReceipt from './ReadReceipt.vue';

const props = defineProps({
    message: Object,
    conversation: Object,
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user);

const isOwnMessage = computed(() => {
    return props.message.user_id === currentUser.value.id;
});

const formatTime = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
};
</script>

