<template>
    <div class="border-t border-gray-200 p-4">
        <form @submit.prevent="sendMessage" class="flex items-center space-x-2">
            <input
                type="text"
                v-model="form.content"
                @input="handleTyping"
                @keydown.enter.exact.prevent="sendMessage"
                placeholder="Type a message..."
                class="flex-1 rounded-md border-gray-300"
            />
            <button
                type="button"
                @click="toggleFileUpload"
                class="p-2 text-gray-500 hover:text-gray-700"
            >
                📎
            </button>
            <input
                ref="fileInput"
                type="file"
                multiple
                @change="handleFileSelect"
                class="hidden"
                accept="image/*,application/pdf,.doc,.docx,audio/*"
            />
            <button
                type="submit"
                :disabled="form.processing || (!form.content && !form.attachments.length)"
                class="px-4 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50"
            >
                Send
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
});

const form = useForm({
    content: '',
    attachments: [],
    reply_to_id: null,
});

const fileInput = ref(null);
let typingTimeout = null;

const toggleFileUpload = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    form.attachments = Array.from(event.target.files);
};

const sendMessage = () => {
    const formData = new FormData();
    formData.append('content', form.content || '');
    formData.append('reply_to_id', form.reply_to_id || '');
    
    if (form.attachments && form.attachments.length > 0) {
        form.attachments.forEach((file) => {
            formData.append('attachments[]', file);
        });
    }

    form.transform(() => formData).post(route('messaging.messages.store', props.conversation.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('content', 'attachments');
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
};

const handleTyping = () => {
    // Send typing indicator
    router.post(route('messaging.typing.start', props.conversation.id), {}, {
        preserveState: true,
        preserveScroll: true,
    });

    // Clear existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }

    // Stop typing after 3 seconds of inactivity
    typingTimeout = setTimeout(() => {
        router.post(route('messaging.typing.stop', props.conversation.id), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    }, 3000);
};
</script>

