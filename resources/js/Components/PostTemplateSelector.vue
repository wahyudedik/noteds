<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    purposeType: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['template-selected', 'create-template']);

const templates = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const showModal = ref(false);
const selectedTemplate = ref(null);
const showPreview = ref(false);

const loadTemplates = async () => {
    loading.value = true;
    try {
        const url = route('post-templates.index', {
            purpose_type: props.purposeType || 'all',
        });
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        templates.value = data.data || [];
    } catch (error) {
        console.error('Error loading templates:', error);
    } finally {
        loading.value = false;
    }
};

const openModal = () => {
    showModal.value = true;
    loadTemplates();
};

const closeModal = () => {
    showModal.value = false;
    searchQuery.value = '';
    selectedTemplate.value = null;
    showPreview.value = false;
};

const selectTemplate = (template) => {
    selectedTemplate.value = template;
    showPreview.value = true;
};

const applyTemplate = () => {
    if (selectedTemplate.value) {
        emit('template-selected', selectedTemplate.value);
        closeModal();
    }
};

const createTemplateFromCurrent = () => {
    emit('create-template');
    closeModal();
};

const filteredTemplates = computed(() => {
    if (!searchQuery.value) {
        return templates.value;
    }
    const query = searchQuery.value.toLowerCase();
    return templates.value.filter(template =>
        template.name.toLowerCase().includes(query) ||
        template.title_template?.toLowerCase().includes(query) ||
        template.content_template?.toLowerCase().includes(query)
    );
});

defineExpose({
    openModal,
});
</script>

<template>
    <div>
        <button
            @click="openModal"
            type="button"
            class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-200 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
            📋 Use Template
        </button>

        <!-- Template Selection Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Select Template</h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        ✕
                    </button>
                </div>

                <!-- Search -->
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search templates..."
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto">
                    <div v-if="loading" class="p-6 text-center text-gray-500 dark:text-gray-400">
                        Loading templates...
                    </div>
                    <div v-else-if="filteredTemplates.length === 0" class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No templates found.
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
                        <div
                            v-for="template in filteredTemplates"
                            :key="template.id"
                            @click="selectTemplate(template)"
                            :class="[
                                'p-4 border rounded-lg cursor-pointer transition',
                                selectedTemplate?.id === template.id
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                            ]"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ template.name }}</h4>
                                <span v-if="template.is_public" class="text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded">
                                    Public
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                {{ template.purpose_type?.replace('_', ' ') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 line-clamp-2">
                                {{ template.title_template }}
                            </p>
                            <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                                Used {{ template.usage_count }} times
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div v-if="showPreview && selectedTemplate" class="border-t border-gray-200 dark:border-gray-700 p-6 bg-gray-50 dark:bg-gray-900">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Preview</h4>
                    <div class="space-y-2">
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Title:</span>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ selectedTemplate.title_template }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Content:</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 line-clamp-3">{{ selectedTemplate.content_template }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <button
                        @click="createTemplateFromCurrent"
                        class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        Create template from current post
                    </button>
                    <div class="flex gap-2">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <button
                            @click="applyTemplate"
                            :disabled="!selectedTemplate"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Apply Template
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
