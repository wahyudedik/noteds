<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    documentation: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => !!props.documentation);

const form = useForm({
    title: props.documentation?.title || '',
    slug: props.documentation?.slug || '',
    content: props.documentation?.content || '',
    category: props.documentation?.category || '',
    excerpt: props.documentation?.excerpt || '',
    order: props.documentation?.order || 0,
    status: props.documentation?.status || 'draft',
});

const categories = [
    { value: 'getting-started', label: 'Getting Started' },
    { value: 'marketplace', label: 'Marketplace' },
    { value: 'clipper', label: 'Clipper' },
    { value: 'api', label: 'API' },
];

// Auto-generate slug from title
watch(() => form.title, (newTitle) => {
    if (!isEdit.value || !form.slug) {
        form.slug = newTitle.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
});

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.documentations.update', props.documentation.id));
    } else {
        form.post(route('admin.documentations.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="title" value="Title" />
            <TextInput
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div>
            <InputLabel for="slug" value="Slug" />
            <TextInput
                id="slug"
                v-model="form.slug"
                type="text"
                class="mt-1 block w-full"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                URL: /documentation/{{ form.slug || 'slug-will-be-generated' }}
            </p>
            <InputError class="mt-2" :message="form.errors.slug" />
        </div>

        <div>
            <InputLabel for="content" value="Content" />
            <Textarea
                id="content"
                v-model="form.content"
                class="mt-1 block w-full"
                rows="12"
                required
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Supports HTML or Markdown
            </p>
            <InputError class="mt-2" :message="form.errors.content" />
        </div>

        <div>
            <InputLabel for="category" value="Category" />
            <select
                id="category"
                v-model="form.category"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">Select Category</option>
                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                    {{ cat.label }}
                </option>
            </select>
            <InputError class="mt-2" :message="form.errors.category" />
        </div>

        <div>
            <InputLabel for="excerpt" value="Excerpt (Optional)" />
            <Textarea
                id="excerpt"
                v-model="form.excerpt"
                class="mt-1 block w-full"
                rows="3"
            />
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Brief summary. If empty, will be auto-generated from content.
            </p>
            <InputError class="mt-2" :message="form.errors.excerpt" />
        </div>

        <div>
            <InputLabel for="order" value="Order" />
            <TextInput
                id="order"
                v-model.number="form.order"
                type="number"
                min="0"
                class="mt-1 block w-full"
            />
            <InputError class="mt-2" :message="form.errors.order" />
        </div>

        <div>
            <InputLabel for="status" value="Status" />
            <div class="mt-2 space-x-4">
                <label class="inline-flex items-center">
                    <input
                        v-model="form.status"
                        type="radio"
                        value="draft"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Draft</span>
                </label>
                <label class="inline-flex items-center">
                    <input
                        v-model="form.status"
                        type="radio"
                        value="published"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Published</span>
                </label>
            </div>
            <InputError class="mt-2" :message="form.errors.status" />
        </div>

        <div v-if="isEdit" class="rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4">
            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                ⚠️ Changing the slug will affect the URL. Make sure to update any links pointing to this documentation.
            </p>
        </div>

        <div class="flex items-center justify-end space-x-4">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ isEdit ? 'Update' : 'Create' }} Documentation
            </PrimaryButton>
        </div>
    </form>
</template>

