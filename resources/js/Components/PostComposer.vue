<script setup>
import { ref } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import PostPurposeSelector from '@/Components/PostPurposeSelector.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const page = usePage();

const showComposer = ref(false);

const form = useForm({
    purpose_type: '',
    title: '',
    content: '',
});

const submit = () => {
    if (form.processing) return; // Prevent double submission
    
    form.post(route('posts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showComposer.value = false;
        },
        onError: (errors) => {
            // Handle 429 error - when there are no validation errors but request failed
            // This usually means rate limiting
            if (!errors || Object.keys(errors).length === 0) {
                // Check if form has a general error message
                const generalError = form.error('message') || form.error('error');
                if (generalError) {
                    alert(generalError);
                } else {
                    alert('Too many requests. Please wait a moment before creating another post.');
                }
            }
        },
    });
};

const toggleComposer = () => {
    showComposer.value = !showComposer.value;
    if (!showComposer.value) {
        form.reset();
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <div v-if="!showComposer" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
                {{ page.props.auth?.user?.name?.charAt(0).toUpperCase() || 'U' }}
            </div>
            <button
                @click="toggleComposer"
                class="flex-1 text-left px-4 py-2 bg-gray-50 dark:bg-gray-700 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            >
                Share your business idea or question...
            </button>
        </div>

        <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Create Post
                </h3>
                <button
                    @click="toggleComposer"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <PostPurposeSelector v-model="form.purpose_type" />
                <InputError :message="form.errors.purpose_type" />

                <div>
                    <TextInput
                        v-model="form.title"
                        type="text"
                        class="w-full"
                        placeholder="Post title (min. 10 characters)"
                        required
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div>
                    <Textarea
                        v-model="form.content"
                        class="w-full"
                        rows="6"
                        placeholder="Share your thoughts... (min. 50 characters)"
                        required
                    />
                    <InputError :message="form.errors.content" />
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Focus on business-related content. Personal drama is not allowed.
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="toggleComposer"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                    >
                        Cancel
                    </button>
                    <PrimaryButton :disabled="form.processing">
                        Publish
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>

