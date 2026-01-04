<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Object,
});

const form = useForm({
    subject: '',
    message: '',
    category: 'general',
    priority: 'medium',
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
    form.attachments = selectedFiles.value;
};

const removeFile = (index) => {
    selectedFiles.value.splice(index, 1);
    form.attachments = selectedFiles.value;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    const formData = new FormData();
    formData.append('subject', form.subject);
    formData.append('message', form.message);
    formData.append('category', form.category);
    formData.append('priority', form.priority);
    
    selectedFiles.value.forEach((file, index) => {
        formData.append(`attachments[${index}]`, file);
    });

    form.transform(() => formData).post(route('support.tickets.store'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Create Support Ticket" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Create Support Ticket
            </h2>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-3xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form @submit.prevent="submit">
                        <div class="space-y-6">
                            <!-- Subject -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Subject <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    placeholder="Brief description of your issue"
                                />
                                <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.subject }}
                                </p>
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Category
                                </label>
                                <select
                                    v-model="form.category"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="(label, value) in categories" :key="value" :value="value">
                                        {{ label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Priority -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Priority
                                </label>
                                <select
                                    v-model="form.priority"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>

                            <!-- Message -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Message <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    v-model="form.message"
                                    required
                                    rows="8"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                                    placeholder="Please provide detailed information about your issue..."
                                ></textarea>
                                <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.message }}
                                </p>
                            </div>

                            <!-- Attachments -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Attachments (Optional, Max 5 files, 10MB each)
                                </label>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    multiple
                                    accept="image/*,.pdf,.txt,.doc,.docx"
                                    @change="handleFileSelect"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Supported formats: Images (JPEG, PNG, GIF, WebP), PDF, Text, Word documents
                                </p>
                                
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
                                <p v-if="form.errors.attachments" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.attachments }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end gap-4">
                                <a
                                    :href="route('support.tickets.index')"
                                    class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Ticket' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

