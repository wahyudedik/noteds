<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    ticket: Object,
});

const responseForm = useForm({
    message: '',
    attachments: [],
});

const fileInput = ref(null);
const selectedFiles = ref([]);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files);
    if (files.length + selectedFiles.value.length > 5) {
        alert('Maximum 5 files allowed');
        return;
    }
    selectedFiles.value = [...selectedFiles.value, ...files];
    responseForm.attachments = selectedFiles.value;
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    responseForm.attachments = selectedFiles.value;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitResponse = () => {
    const formData = new FormData();
    formData.append('message', responseForm.message);
    
    selectedFiles.value.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    responseForm.transform(() => formData).post(route('support.tickets.response', props.ticket.id), {
        forceFormData: true,
        onSuccess: () => {
            responseForm.reset();
            selectedFiles.value = [];
        },
    });
};

const getStatusBadgeClass = (status) => {
    const classes = {
        open: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        in_progress: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        resolved: 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        closed: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
    };
    return classes[status] || classes.open;
};

const getPriorityBadgeClass = (priority) => {
    const classes = {
        low: 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200',
        medium: 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        high: 'bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200',
        urgent: 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
    };
    return classes[priority] || classes.medium;
};
</script>

<template>
    <Head :title="ticket ? `Ticket ${ticket.ticket_number}` : 'Support Ticket'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Ticket {{ ticket?.ticket_number || 'Loading...' }}
                </h2>
                <a
                    :href="route('support.tickets.index')"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                >
                    Back to Tickets
                </a>
            </div>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div v-if="!ticket" class="mx-auto max-w-4xl">
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">Loading ticket...</p>
                </div>
            </div>
            <div v-else class="mx-auto max-w-4xl space-y-6">
                <!-- Ticket Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                {{ ticket.subject }}
                            </h3>
                            <div class="flex items-center gap-3">
                                <span
                                    v-if="ticket.status"
                                    :class="['px-3 py-1 text-sm rounded-full', getStatusBadgeClass(ticket.status)]"
                                >
                                    {{ ticket.status.replace('_', ' ') }}
                                </span>
                                <span
                                    v-if="ticket.priority"
                                    :class="['px-3 py-1 text-sm rounded-full', getPriorityBadgeClass(ticket.priority)]"
                                >
                                    {{ ticket.priority }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ ticket.category || 'General' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ ticket.message }}</p>
                    </div>

                    <!-- Attachments -->
                    <div v-if="ticket.attachments && ticket.attachments.length > 0" class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attachments:</h4>
                        <div class="flex flex-wrap gap-2">
                            <a
                                v-for="(attachment, index) in ticket.attachments"
                                :key="index"
                                :href="`/storage/${attachment}`"
                                target="_blank"
                                class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-200 dark:hover:bg-gray-600"
                            >
                                {{ attachment.split('/').pop() }}
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Created: {{ ticket.created_at ? new Date(ticket.created_at).toLocaleString() : '' }}
                        <span v-if="ticket.assigned_admin && ticket.assigned_admin.name">
                            | Assigned to: {{ ticket.assigned_admin.name }}
                        </span>
                    </div>
                </div>

                <!-- Responses -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Responses ({{ ticket.responses?.length || 0 }})
                    </h3>

                    <div v-if="!ticket.responses || ticket.responses.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No responses yet.
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="response in ticket.responses"
                            :key="response.id"
                            :class="[
                                'p-4 rounded-lg border',
                                response.is_admin_response
                                    ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'
                                    : 'bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600'
                            ]"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ response.user?.name }}
                                    </span>
                                    <span
                                        v-if="response.is_admin_response"
                                        class="ml-2 px-2 py-0.5 text-xs bg-blue-600 text-white rounded"
                                    >
                                        Admin
                                    </span>
                                </div>
                                <span v-if="response.created_at" class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ new Date(response.created_at).toLocaleString() }}
                                </span>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ response.message }}</p>
                            
                            <!-- Response Attachments -->
                            <div v-if="response.attachments && response.attachments.length > 0" class="mt-3">
                                <div class="flex flex-wrap gap-2">
                                    <a
                                        v-for="(attachment, index) in response.attachments"
                                        :key="index"
                                        :href="`/storage/${attachment}`"
                                        target="_blank"
                                        class="px-2 py-1 text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
                                    >
                                        {{ attachment.split('/').pop() }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Response Form -->
                <div v-if="ticket.status !== 'closed'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Response</h3>
                    <form @submit.prevent="submitResponse">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="responseForm.message"
                                    required
                                    rows="6"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    placeholder="Type your response..."
                                ></textarea>
                                <p v-if="responseForm.errors.message" class="mt-1 text-sm text-red-600">
                                    {{ responseForm.errors.message }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Attachments (Optional, Max 5 files)
                                </label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    multiple
                                    accept="image/*,.pdf,.txt,.doc,.docx"
                                    @change="handleFileSelect"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                                
                                <div v-if="selectedFiles.length > 0" class="mt-3 space-y-2">
                                    <div
                                        v-for="(file, index) in selectedFiles"
                                        :key="index"
                                        class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded"
                                    >
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ file.name }}</span>
                                        <button
                                            type="button"
                                            @click="removeFile(index)"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="responseForm.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{ responseForm.processing ? 'Sending...' : 'Send Response' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

