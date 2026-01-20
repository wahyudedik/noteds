<template>
    <Head title="Messages" />
    
    <MessagingLayout>
        <div class="flex h-full">
            <!-- Conversation List -->
            <div class="w-1/3 border-r border-gray-200 flex flex-col">
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <h1 class="text-xl font-bold">Messages</h1>
                    <Link
                        :href="route('messaging.conversations.create')"
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        New
                    </Link>
                </div>
                <ConversationList :conversations="conversations" />
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center text-gray-500">
                    <p class="text-lg">Select a conversation to start messaging</p>
                </div>
            </div>
        </div>
    </MessagingLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';
import ConversationList from '@/Components/Messaging/ConversationList.vue';

defineProps({
    conversations: Object,
});
</script>

