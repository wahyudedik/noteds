<template>
    <Head title="New Conversation" />
    
    <MessagingLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">New Conversation</h1>
            
            <form @submit.prevent="createConversation">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Conversation Type
                    </label>
                    <select v-model="form.type" class="w-full rounded-md border-gray-300">
                        <option value="direct">Direct Message</option>
                        <option value="group">Group Chat</option>
                    </select>
                </div>

                <div v-if="form.type === 'direct'" class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Select User
                    </label>
                    <input
                        type="text"
                        v-model="form.user_id"
                        placeholder="User ID"
                        class="w-full rounded-md border-gray-300"
                        required
                    />
                </div>

                <div v-if="form.type === 'group'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Group Name
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            class="w-full rounded-md border-gray-300"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <textarea
                            v-model="form.description"
                            class="w-full rounded-md border-gray-300"
                            rows="3"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <Link
                        :href="route('messaging.index')"
                        class="px-4 py-2 border border-gray-300 rounded-md"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md"
                        :disabled="form.processing"
                    >
                        Create
                    </button>
                </div>
            </form>
        </div>
    </MessagingLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MessagingLayout from '@/Layouts/MessagingLayout.vue';

const form = useForm({
    type: 'direct',
    user_id: null,
    name: null,
    description: null,
    participant_ids: [],
});

const createConversation = () => {
    form.post(route('messaging.conversations.store'), {
        onSuccess: () => {
            // Redirect will be handled by controller
        },
    });
};
</script>

