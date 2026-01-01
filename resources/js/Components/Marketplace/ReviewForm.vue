<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    productId: {
        type: String,
        required: true,
    },
    existingReview: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['review-submitted', 'cancel']);

const hoveredRating = ref(0);
const selectedRating = ref(props.existingReview?.rating || 0);

const form = useForm({
    rating: props.existingReview?.rating || 0,
    comment: props.existingReview?.comment || '',
});

watch(() => props.existingReview, (newReview) => {
    if (newReview) {
        form.rating = newReview.rating;
        form.comment = newReview.comment || '';
        selectedRating.value = newReview.rating;
    } else {
        form.rating = 0;
        form.comment = '';
        selectedRating.value = 0;
    }
}, { immediate: true });

const setRating = (rating) => {
    selectedRating.value = rating;
    form.rating = rating;
};

const submit = () => {
    if (!form.rating || form.rating < 1) {
        form.setError('rating', 'Please select a rating');
        return;
    }

    if (props.existingReview) {
        // Update existing review
        form.put(route('marketplace.reviews.update', props.existingReview.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                selectedRating.value = 0;
                emit('review-submitted');
            },
        });
    } else {
        // Create new review
        form.post(route('marketplace.products.reviews.store', props.productId), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                selectedRating.value = 0;
                emit('review-submitted');
            },
        });
    }
};

const cancel = () => {
    form.reset();
    selectedRating.value = 0;
    emit('cancel');
};

const renderStars = () => {
    const stars = [];
    const displayRating = hoveredRating.value || selectedRating.value;
    for (let i = 1; i <= 5; i++) {
        stars.push(i <= displayRating);
    }
    return stars;
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            {{ existingReview ? 'Edit Your Review' : 'Write a Review' }}
        </h3>

        <form @submit.prevent="submit">
            <!-- Rating Stars -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rating <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center space-x-1">
                    <button
                        v-for="(filled, index) in renderStars()"
                        :key="index"
                        type="button"
                        @click="setRating(index + 1)"
                        @mouseenter="hoveredRating = index + 1"
                        @mouseleave="hoveredRating = 0"
                        class="focus:outline-none transition-transform hover:scale-110"
                    >
                        <svg
                            :class="filled ? 'text-yellow-400' : 'text-gray-300'"
                            class="w-8 h-8"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </button>
                    <span v-if="selectedRating > 0" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ selectedRating }} / 5
                    </span>
                </div>
                <InputError :message="form.errors.rating" />
            </div>

            <!-- Comment -->
            <div class="mb-4">
                <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Comment (optional)
                </label>
                <Textarea
                    id="comment"
                    v-model="form.comment"
                    rows="4"
                    class="mt-1 block w-full"
                    placeholder="Share your experience with this product..."
                />
                <InputError :message="form.errors.comment" />
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-3">
                <PrimaryButton :disabled="form.processing || !selectedRating">
                    {{ form.processing ? 'Submitting...' : (existingReview ? 'Update Review' : 'Submit Review') }}
                </PrimaryButton>
                <button
                    v-if="existingReview"
                    type="button"
                    @click="cancel"
                    class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                >
                    Cancel
                </button>
            </div>
        </form>
    </div>
</template>

