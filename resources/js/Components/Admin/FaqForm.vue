<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    faq: {
        type: Object,
        default: null,
    },
});

const isEdit = computed(() => !!props.faq);

const form = useForm({
    question: props.faq?.question || '',
    answer: props.faq?.answer || '',
    category: props.faq?.category || '',
    order: props.faq?.order || 0,
    status: props.faq?.status || 'draft',
});

const categories = [
    { value: 'general', label: 'General' },
    { value: 'marketplace', label: 'Marketplace' },
    { value: 'clipper', label: 'Clipper' },
    { value: 'account', label: 'Account' },
];

const submit = () => {
    if (isEdit.value) {
        form.put(route('admin.faqs.update', props.faq.id));
    } else {
        form.post(route('admin.faqs.store'));
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="question" value="Question" />
            <TextInput
                id="question"
                v-model="form.question"
                type="text"
                class="mt-1 block w-full"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.question" />
        </div>

        <div>
            <InputLabel for="answer" value="Answer" />
            <Textarea
                id="answer"
                v-model="form.answer"
                class="mt-1 block w-full"
                rows="6"
                required
            />
            <InputError class="mt-2" :message="form.errors.answer" />
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

        <div class="flex items-center justify-end space-x-4">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ isEdit ? 'Update' : 'Create' }} FAQ
            </PrimaryButton>
        </div>
    </form>
</template>

