<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PostPurposeSelector from '@/Components/PostPurposeSelector.vue';
import BusinessTypeSelector from '@/Components/BusinessTypeSelector.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { PURPOSE_TYPES } from '@/Utils/constants';
import { ref, onMounted } from 'vue';

const props = defineProps({
    businessTypes: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    purpose_type: '',
    business_type: null,
    title: '',
    content: '',
    scheduled_at: null,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
});

const submit = () => {
    form.post(route('posts.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Post" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Buat Post Baru
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <PostPurposeSelector v-model="form.purpose_type" />
                                <InputError class="mt-2" :message="form.errors.purpose_type" />

                                <BusinessTypeSelector
                                    v-if="form.purpose_type && ['idea_business', 'validate_idea', 'find_tools'].includes(form.purpose_type)"
                                    v-model="form.business_type"
                                    :business-types="businessTypes"
                                />
                                <InputError class="mt-2" :message="form.errors.business_type" />

                                <div>
                                    <InputLabel for="title" value="Judul" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.title"
                                        required
                                        autofocus
                                        placeholder="Masukkan judul postingan (min. 10 karakter)"
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>

                                <div>
                                    <InputLabel for="content" value="Konten" />
                                    <Textarea
                                        id="content"
                                        class="mt-1 block w-full"
                                        v-model="form.content"
                                        required
                                        rows="10"
                                        placeholder="Tulis konten postingan kamu di sini (min. 50 karakter). Pastikan konten relevan dengan bisnis."
                                    />
                                    <InputError class="mt-2" :message="form.errors.content" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Konten harus fokus pada bisnis. Hindari konten personal atau drama.
                                    </p>
                                </div>

                                <div class="flex items-center justify-end gap-4">
                                    <div class="flex items-center gap-2 mr-auto">
                                        <label class="text-sm text-gray-700 dark:text-gray-300">Schedule</label>
                                        <input type="datetime-local" v-model="form.scheduled_at" class="px-2 py-1 border rounded" />
                                    </div>
                                    <PrimaryButton :disabled="form.processing">
                                        Publish
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
